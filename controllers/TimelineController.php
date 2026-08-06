<?php







class TimelineController extends Controller
{
    public function __construct()
    {
        $roleSlug = Session::get('user_role_slug');
        if (!in_array($roleSlug, ['admin', 'superadmin', 'magang'])) {
            Session::setFlash('error', 'Akses ditolak.');
            $this->redirect('/dashboard');
        }
    }

    



    



    private function getDriveService(): GoogleDriveService
    {
        require_once SERVICES_PATH . 'GoogleDriveService.php';
        return new GoogleDriveService(GOOGLE_DRIVE_OAUTH_TOKEN, GOOGLE_DRIVE_FOLDER_ID);
    }

    


    private function deleteTaskFiles(array $task): void
    {
        require_once SERVICES_PATH . 'GoogleDriveService.php';

        $columns = ['file_attachment', 'result_file_attachment', 'screenshot_attachment', 'video_attachment'];
        $driveIds = [];

        foreach ($columns as $col) {
            $value = $task[$col] ?? null;
            if (empty($value)) continue;

            if (GoogleDriveService::isDriveLink($value)) {
                $fileId = GoogleDriveService::extractFileId($value);
                if ($fileId) {
                    $driveIds[] = $fileId;
                }
            } else {
                $filePath = str_replace(BASE_URL, BASE_PATH, $value);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        if ($driveIds) {
            try {
                $service = $this->getDriveService();
                foreach ($driveIds as $fileId) {
                    $service->deleteFile($fileId);
                }
            } catch (\Throwable $e) {
                error_log('GoogleDrive hapus gagal: ' . $e->getMessage());
            }
        }
    }

    


    private function deleteAttachmentFile(string $fileUrl): void
    {
        require_once SERVICES_PATH . 'GoogleDriveService.php';
        if (GoogleDriveService::isDriveLink($fileUrl)) {
            $fileId = GoogleDriveService::extractFileId($fileUrl);
            if ($fileId) {
                try {
                    $service = $this->getDriveService();
                    $service->deleteFile($fileId);
                } catch (\Throwable $e) {
                    error_log('GoogleDrive hapus file gagal: ' . $e->getMessage());
                }
            }
        } else {
            $filePath = str_replace(BASE_URL, BASE_PATH, $fileUrl);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    public function index(): void
    {
        $qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '?view=timeline';
        $this->redirect('/planning' . $qs);
    }

    


    public function store(): void
    {
        $this->requireAdmin();
        $data = $this->getTaskData();
        if (!$data) return;

        if (empty($data['assignee_id'])) {
            $this->redirectWith('/timeline?bulan=' . date('m', strtotime($data['task_date'])) . '&tahun=' . date('Y', strtotime($data['task_date'])), 'error', 'User/Magang penerima task wajib dipilih');
        }

        
        if (!empty($data['deadline']) && $data['deadline'] < date('Y-m-d')) {
            $this->redirectWith('/timeline?bulan=' . date('m', strtotime($data['task_date'])) . '&tahun=' . date('Y', strtotime($data['task_date'])), 'error', 'Tanggal deadline tidak boleh sebelum hari ini');
        }

        $data['creator_id'] = Session::get('user_id');
        $data['status'] = 'Belum';

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $id = Database::insert(
            "INSERT INTO timeline_tasks ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
        
        $this->syncCalendar($data['task_date']);

        Session::setFlash('success', 'Task berhasil ditambahkan');
        $this->redirect('/timeline?bulan=' . date('m', strtotime($data['task_date'])) . '&tahun=' . date('Y', strtotime($data['task_date'])));
    }

    


    public function update(string $id): void
    {
        $this->requireAdmin();
        $task = Database::fetch("SELECT * FROM timeline_tasks WHERE id = ?", [$id]);
        if (!$task) {
            $this->redirectWith('/timeline', 'error', 'Task tidak ditemukan');
        }

        $data = $this->getTaskData();
        if (!$data) return;

        
        if (!empty($data['deadline']) && $data['deadline'] < date('Y-m-d')) {
            $this->redirectWith('/timeline?bulan=' . date('m', strtotime($data['task_date'])) . '&tahun=' . date('Y', strtotime($data['task_date'])), 'error', 'Tanggal deadline tidak boleh sebelum hari ini');
        }

        
        if (!isset($_POST['status'])) {
            $data['status'] = $task['status'];
        }

        
        if (empty($data['file_attachment']) && empty($_POST['existing_file'])) {
            $data['file_attachment'] = $task['file_attachment'];
        }

        
        if (isset($_FILES['file_attachment']) && $_FILES['file_attachment']['error'] === UPLOAD_ERR_OK && !empty($task['file_attachment'])) {
            $this->deleteAttachmentFile($task['file_attachment']);
        }

        $oldDate = $task['task_date'];
        $oldSort = (int)$task['sort_order'];
        $newDate = $data['task_date'];
        $newSort = (int)$data['sort_order'];
        $idInt = (int)$id;

        
        if ($oldDate === $newDate) {
            
            if ($newSort > $oldSort) {
                
                Database::execute(
                    "UPDATE timeline_tasks SET sort_order = sort_order - 1 WHERE task_date = ? AND sort_order > ? AND sort_order <= ? AND id != ?",
                    [$newDate, $oldSort, $newSort, $idInt]
                );
            } elseif ($newSort < $oldSort) {
                
                Database::execute(
                    "UPDATE timeline_tasks SET sort_order = sort_order + 1 WHERE task_date = ? AND sort_order >= ? AND sort_order < ? AND id != ?",
                    [$newDate, $newSort, $oldSort, $idInt]
                );
            }
        } else {
            
            
            Database::execute(
                "UPDATE timeline_tasks SET sort_order = sort_order - 1 WHERE task_date = ? AND sort_order > ?",
                [$oldDate, $oldSort]
            );
            
            Database::execute(
                "UPDATE timeline_tasks SET sort_order = sort_order + 1 WHERE task_date = ? AND sort_order >= ?",
                [$newDate, $newSort]
            );
            $this->syncCalendar($oldDate);
        }

        $setClauses = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $id;
        
        Database::execute(
            "UPDATE timeline_tasks SET {$setClauses} WHERE id = ?",
            $params
        );
        
        $this->syncCalendar($data['task_date']);

        Session::setFlash('success', 'Task berhasil diperbarui');
        $this->redirect('/timeline?bulan=' . date('m', strtotime($data['task_date'])) . '&tahun=' . date('Y', strtotime($data['task_date'])));
    }

    


    public function delete(string $id): void
    {
        $this->requireAdmin();
        $task = Database::fetch("SELECT * FROM timeline_tasks WHERE id = ?", [$id]);
        if (!$task) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Task tidak ditemukan'], 404);
            }
            $this->redirectWith('/timeline', 'error', 'Task tidak ditemukan');
        }

        $this->deleteTaskFiles($task);

        Database::execute("DELETE FROM timeline_tasks WHERE id = ?", [$id]);
        $this->syncCalendar($task['task_date']);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Task berhasil dihapus']);
        }

        Session::setFlash('success', 'Task berhasil dihapus');
        $this->redirect('/timeline?bulan=' . date('m', strtotime($task['task_date'])) . '&tahun=' . date('Y', strtotime($task['task_date'])));
    }

    


    public function get(string $id): void
    {
        $task = Database::fetch(
            "SELECT tt.*, creator.name AS creator_name, assignee.name AS assignee_name,
                    p.name AS platform_name, p.icon AS platform_icon, p.color AS platform_color
             FROM timeline_tasks tt
             LEFT JOIN users creator ON creator.id = tt.creator_id
             LEFT JOIN users assignee ON assignee.id = tt.assignee_id
             LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
             WHERE tt.id = ?",
            [$id]
        );
        $userId = (int)Session::get('user_id');
        $userName = Session::get('user_name');
        if (!$task || (Session::get('user_role_slug') === 'magang' && (int)$task['assignee_id'] !== $userId && (int)$task['assigned_to'] !== $userId && strcasecmp(trim($task['pic_name'] ?? ''), trim($userName)) !== 0)) {
            $this->json(['success' => false, 'message' => 'Task tidak ditemukan'], 404);
            return;
        }
        $this->json(['success' => true, 'data' => $task]);
    }

    


    public function getByRange(): void
    {
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');

        $taskWhere = "task_date BETWEEN ? AND ?";
        $taskParams = [$start, $end];
        if (Session::get('user_role_slug') === 'magang') {
            $taskWhere .= " AND (assignee_id = ? OR assigned_to = ? OR LOWER(TRIM(pic_name)) = LOWER(TRIM(?)))";
            $taskParams[] = Session::get('user_id');
            $taskParams[] = Session::get('user_id');
            $taskParams[] = Session::get('user_name');
        }

        $tasks = Database::fetchAll(
            "SELECT * FROM timeline_tasks WHERE {$taskWhere} ORDER BY task_date, sort_order",
            $taskParams
        );

        $calendar = Database::fetchAll(
            "SELECT * FROM timeline_calendar WHERE calendar_date BETWEEN ? AND ? ORDER BY calendar_date",
            [$start, $end]
        );

        $this->json([
            'success' => true,
            'tasks' => $tasks,
            'calendar' => $calendar
        ]);
    }

    


    public function getPlanning(string $id): void
    {
        $planning = Database::fetch(
            "SELECT pk.*, 
                    p.name as platform_name, p.icon as platform_icon, p.color as platform_color,
                    pt.name as program_name,
                    kk.name as kategori_name, kk.color as kategori_color,
                    jk.name as jenis_konten_name,
                    creator.name as creator_name,
                    editor.name as editor_name,
                    pic.name as pic_name
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             LEFT JOIN program_tv pt ON pt.id = pk.program_id
             LEFT JOIN kategori_konten kk ON kk.id = pk.kategori_id
             LEFT JOIN jenis_konten jk ON jk.id = pk.jenis_konten_id
             LEFT JOIN users creator ON creator.id = pk.created_by
             LEFT JOIN users editor ON editor.id = pk.editor_id
             LEFT JOIN users pic ON pic.id = pk.pic_id
             WHERE pk.id = ? AND pk.deleted_at IS NULL",
            [$id]
        );
        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Planning tidak ditemukan']);
            return;
        }
        $this->json(['success' => true, 'data' => $planning]);
    }

    


    public function updateStatus(): void
    {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';

        if (Session::get('user_role_slug') === 'magang') {
            $assigned = Database::fetchColumn(
                "SELECT COUNT(*) FROM timeline_tasks WHERE id = ? AND (assignee_id = ? OR assigned_to = ? OR LOWER(TRIM(pic_name)) = LOWER(TRIM(?))) AND status IN ('Belum', 'Assigned', 'In Progress', 'Need Revision')",
                [$id, Session::get('user_id'), Session::get('user_id'), Session::get('user_name')]
            );
            if (!$assigned) {
                $this->json(['success' => false, 'message' => 'Akses task ditolak'], 403);
                return;
            }
        }

        $validStatuses = Session::get('user_role_slug') === 'magang'
            ? ['In Progress']
            : ['Belum', 'Assigned', 'In Progress', 'Selesai', 'Proses', 'Publish', 'Belum Selesai', 'Pending Approval', 'Approved', 'Need Revision', 'Scheduled'];
        if (!in_array($status, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Status tidak valid']);
            return;
        }

        Database::execute("UPDATE timeline_tasks SET status = ? WHERE id = ?", [$status, $id]);
        $this->json(['success' => true]);
    }

    


    public function submitApproval(): void
    {
        if (Session::get('user_role_slug') !== 'magang') {
            $this->json(['success' => false, 'message' => 'Hanya User/Magang yang dapat mengirim hasil'], 403);
        }

        
        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength > 0 && empty($_POST) && empty($_FILES)) {
            $this->json([
                'success' => false,
                'message' => 'File terlalu besar. Ukuran maksimal yang diizinkan ' . ini_get('post_max_size') . '. Perkecil file atau gunakan Link Hasil Google Drive.'
            ], 413);
            return;
        }

        $taskId = (int)($_POST['task_id'] ?? 0);
        $userId = Session::get('user_id');
        $userName = Session::get('user_name');
        $task = Database::fetch(
            "SELECT tt.*, assignee.name AS assignee_name
             FROM timeline_tasks tt
             LEFT JOIN users assignee ON assignee.id = tt.assignee_id
             WHERE tt.id = ? 
               AND (
                    tt.assignee_id = ? 
                    OR tt.assigned_to = ? 
                    OR (tt.pic_name IS NOT NULL AND LOWER(TRIM(tt.pic_name)) = LOWER(TRIM(?)))
                    OR tt.assignee_id IS NULL 
                    OR tt.assignee_id = 0
                   ) 
               AND tt.status IN ('Assigned', 'In Progress', 'Need Revision', 'Pending Approval', 'Belum', 'Proses')",
            [$taskId, $userId, $userId, $userName]
        );
        if (!$task) {
            $this->json(['success' => false, 'message' => 'Task tidak ditemukan atau tidak dapat dikirim'], 404);
            return;
        }

        $this->ensureResultFileColumnExists();

        $descriptionUpdate = trim(Security::sanitize($_POST['description_update'] ?? ''));
        $userNotes = trim(Security::sanitize($_POST['user_notes'] ?? ''));
        $resultLink = trim($_POST['result_link'] ?? '');
        if ($resultLink !== '' && !filter_var($resultLink, FILTER_VALIDATE_URL)) {
            $this->json(['success' => false, 'message' => 'Link hasil tidak valid'], 422);
            return;
        }

        $existingResultFile = $task['result_file_attachment'] ?? null;
        try {
            $resultFileAttachment = $this->saveResultUpload('file_attachment', $taskId, 'result_file', $existingResultFile);
            $screenshotAttachment = $this->saveResultUpload('screenshot_attachment', $taskId, 'screenshot', $task['screenshot_attachment']);
            $videoAttachment = $this->saveResultUpload('video_attachment', $taskId, 'video', $task['video_attachment']);
        } catch (\Throwable $e) {
            error_log('GoogleDrive upload gagal (submitApproval): ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Upload file hasil ke Google Drive gagal: ' . $e->getMessage()], 500);
            return;
        }

        if ($resultLink === '' && !$resultFileAttachment && !$screenshotAttachment && !$videoAttachment) {
            $this->json(['success' => false, 'message' => 'Wajib mengisi Link Hasil atau mengunggah File Hasil terlebih dahulu'], 422);
            return;
        }

        
        if (isset($_FILES['file_attachment']) && $_FILES['file_attachment']['error'] === UPLOAD_ERR_OK && !empty($task['result_file_attachment'])) {
            $this->deleteAttachmentFile($task['result_file_attachment']);
        }
        if (isset($_FILES['screenshot_attachment']) && $_FILES['screenshot_attachment']['error'] === UPLOAD_ERR_OK && !empty($task['screenshot_attachment'])) {
            $this->deleteAttachmentFile($task['screenshot_attachment']);
        }
        if (isset($_FILES['video_attachment']) && $_FILES['video_attachment']['error'] === UPLOAD_ERR_OK && !empty($task['video_attachment'])) {
            $this->deleteAttachmentFile($task['video_attachment']);
        }

        Database::execute(
            "UPDATE timeline_tasks SET status = 'Pending Approval', description_update = ?, user_notes = ?, result_link = ?,
                    result_file_attachment = ?, screenshot_attachment = ?, video_attachment = ?, submitted_at = NOW(),
                    approved_at = NULL, approved_by = NULL, updated_at = NOW()
             WHERE id = ?",
            [$descriptionUpdate, $userNotes, $resultLink, $resultFileAttachment, $screenshotAttachment, $videoAttachment, $taskId]
        );

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'submit_approval', 'approval', 'timeline_tasks', ?, 'Task dikirim untuk approval', ?)",
            [$userId, Session::get('user_role_id'), $taskId, $_SERVER['REMOTE_ADDR']]
        );

        try {
            if (!empty($task['creator_id'])) {
                require_once HELPERS_PATH . 'Notification.php';
                Notification::create(
                    (int)$task['creator_id'],
                    'Task menunggu approval',
                    ($task['assignee_name'] ?: $userName) . " telah mengirim hasil task \"{$task['title']}\" untuk direview.",
                    'info', BASE_URL . '/approval', 'bi-check2-square'
                );
            }
        } catch (\Throwable $e) {
            
        }

        $this->json(['success' => true, 'message' => 'Hasil berhasil dikirim untuk approval']);
    }

    


    public function uploadFile(): void
    {
        $taskId = $_POST['task_id'] ?? 0;
        $task = Database::fetch("SELECT * FROM timeline_tasks WHERE id = ?", [$taskId]);
        
        if (!$task) {
            Session::setFlash('error', 'Task tidak ditemukan');
            $this->redirect('/timeline');
            return;
        }

        
        $userRole = Session::get('user_role_slug');
        $userId = (int)Session::get('user_id');
        $userName = Session::get('user_name');
        
        if ($userRole === 'magang' && (int)$task['assignee_id'] !== $userId && (int)$task['assigned_to'] !== $userId && strcasecmp(trim($task['pic_name'] ?? ''), trim($userName)) !== 0) {
            Session::setFlash('error', 'Anda tidak berhak mengupload file ke task ini');
            $this->redirect('/timeline');
            return;
        }

        if (!isset($_FILES['file_attachment']) || $_FILES['file_attachment']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'File gagal diupload');
            $this->redirect('/timeline');
            return;
        }

        try {
            $service = $this->getDriveService();
            $result = $service->uploadFromUploadedFile($_FILES['file_attachment']);
            $fileUrl = $result['viewLink'];

            
            Database::execute(
                "UPDATE timeline_tasks SET file_attachment = ?, status = 'In Progress', updated_at = NOW() WHERE id = ?",
                [$fileUrl, $taskId]
            );

            $this->syncCalendar($task['task_date']);

            Session::setFlash('success', 'File berhasil diupload ke Google Drive. Klik Kirim untuk Approval setelah hasil lengkap.');
        } catch (\Throwable $e) {
            error_log('GoogleDrive upload gagal (uploadFile): ' . $e->getMessage());
            Session::setFlash('error', 'Upload ke Google Drive gagal: ' . $e->getMessage());
        }

        $this->redirect('/timeline');
    }

    

    public function saveCalendar(): void
    {
        $this->requireAdmin();
        $date = $_POST['calendar_date'] ?? '';
        $feedCount = (int)($_POST['feed_count'] ?? 0);
        $reelsCount = (int)($_POST['reels_count'] ?? 0);
        $storyCount = (int)($_POST['story_count'] ?? 0);
        $contentNames = $_POST['content_names'] ?? '';
        $status = $_POST['status'] ?? '';
        $postTime = $_POST['post_time'] ?? '08.00 - 18.00';

        $dayName = $this->hariIndonesia(date('N', strtotime($date)));

        $existing = Database::fetch("SELECT id FROM timeline_calendar WHERE calendar_date = ?", [$date]);

        if ($existing) {
            Database::execute(
                "UPDATE timeline_calendar SET day_name = ?, feed_count = ?, reels_count = ?, story_count = ?, content_names = ?, status = ?, post_time = ? WHERE id = ?",
                [$dayName, $feedCount, $reelsCount, $storyCount, $contentNames, $status, $postTime, $existing['id']]
            );
        } else {
            Database::execute(
                "INSERT INTO timeline_calendar (calendar_date, day_name, feed_count, reels_count, story_count, content_names, status, post_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$date, $dayName, $feedCount, $reelsCount, $storyCount, $contentNames, $status, $postTime]
            );
        }

        Session::setFlash('success', 'Kalender berhasil diperbarui');
        $this->redirect('/timeline?bulan=' . date('m', strtotime($date)) . '&tahun=' . date('Y', strtotime($date)));
    }

    public function getCalendar(): void
    {
        $date = $_GET['date'] ?? '';
        $entry = Database::fetch("SELECT * FROM timeline_calendar WHERE calendar_date = ?", [$date]);
        $this->json(['success' => true, 'data' => $entry ?: null]);
    }

    public function generateCalendar(): void
    {
        $this->requireAdmin();
        $month = $_POST['bulan'] ?? date('m');
        $year = $_POST['tahun'] ?? date('Y');

        $start = new DateTime("{$year}-{$month}-01");
        $end = new DateTime("{$year}-{$month}-" . $start->format('t'));

        $count = 0;
        $period = new DatePeriod($start, new DateInterval('P1D'), $end->modify('+1 day'));
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $existing = Database::fetch("SELECT id FROM timeline_calendar WHERE calendar_date = ?", [$dateStr]);
            if (!$existing) {
                Database::execute(
                    "INSERT INTO timeline_calendar (calendar_date, day_name, feed_count, reels_count, story_count, status, post_time) VALUES (?, ?, 0, 0, 0, '', ?)",
                    [$dateStr, $this->hariIndonesia((int)$date->format('N')), '08.00 - 18.00']
                );
                $count++;
            }
        }

        Session::setFlash('success', "Kalender {$month}/{$year} berhasil dibuat ($count hari baru)");
        $this->redirect('/timeline?bulan=' . $month . '&tahun=' . $year);
    }

    

    private function ensureResultFileColumnExists(): void
    {
        try {
            $col = Database::fetch("SHOW COLUMNS FROM timeline_tasks LIKE 'result_file_attachment'");
            if (!$col) {
                Database::execute("ALTER TABLE timeline_tasks ADD COLUMN result_file_attachment VARCHAR(255) NULL AFTER file_attachment");
            }
        } catch (\Throwable $e) {}
    }

    private function saveResultUpload(string $inputName, int $taskId, string $prefix, ?string $existing = null): ?string
    {
        if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return $existing;
        }

        
        $service = $this->getDriveService();
        $result = $service->uploadFromUploadedFile($_FILES[$inputName]);
        return $result['viewLink'];
    }

    private function requireAdmin(): void
    {
        $roleSlug = Session::get('user_role_slug');
        if (!in_array($roleSlug, ['admin', 'superadmin'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            }
            Session::setFlash('error', 'Akses ditolak. Hanya Admin yang dapat melakukan ini.');
            $this->redirect('/timeline');
        }
    }

    private function getTaskData(): ?array
    {
        $taskDate = $_POST['task_date'] ?? '';
        if (empty($taskDate)) {
            $taskDate = date('Y-m-d');
        }

        $dayName = $this->hariIndonesia(date('N', strtotime($taskDate)));

        
        $fileAttachment = null;
        if (isset($_FILES['file_attachment']) && $_FILES['file_attachment']['error'] === UPLOAD_ERR_OK) {
            try {
                $service = $this->getDriveService();
                $result = $service->uploadFromUploadedFile($_FILES['file_attachment']);
                $fileAttachment = $result['viewLink'];
            } catch (\Throwable $e) {
                error_log('GoogleDrive upload gagal (getTaskData): ' . $e->getMessage());
                $this->redirectWith('/timeline?bulan=' . date('m') . '&tahun=' . date('Y'), 'error', 'Upload file lampiran ke Google Drive gagal: ' . $e->getMessage());
                return null;
            }
        }

        if (!$fileAttachment && !empty($_POST['existing_file'])) {
            $fileAttachment = $_POST['existing_file'];
        }

        $userRole = Session::get('user_role_slug');
        $userName = Session::get('user_name');
        $picName = trim($_POST['pic_name'] ?? '');
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : ($userRole === 'magang' ? (int)Session::get('user_id') : null);

        if ($assignedTo) {
            $uObj = Database::fetch("SELECT name FROM users WHERE id = ?", [$assignedTo]);
            if ($uObj) {
                $picName = $uObj['name'];
            }
        } elseif (!empty($picName)) {
            $uObj = Database::fetch("SELECT id FROM users WHERE name = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1", [$picName]);
            if ($uObj) {
                $assignedTo = (int)$uObj['id'];
            }
        }

        if ($userRole === 'magang' && empty($picName)) {
            $picName = $userName;
        }

        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        if ($sortOrder <= 0 && !empty($taskDate)) {
            $maxSort = (int)Database::fetchColumn("SELECT COALESCE(MAX(sort_order), 0) FROM timeline_tasks WHERE task_date = ?", [$taskDate]);
            $sortOrder = $maxSort + 1;
        }

        return [
            'task_date' => $taskDate,
            'day_name' => $dayName,
            'content_type' => $_POST['content_type'] ?? '',
            'platform_id' => !empty($_POST['platform_id']) ? (int)$_POST['platform_id'] : null,
            'priority' => $_POST['priority'] ?? 'medium',
            'title' => $_POST['title'] ?? '',
            'status' => $_POST['status'] ?? 'Belum',
            'pic_name' => $picName,
            'assigned_to' => $assignedTo,
            'assignee_id' => $assignedTo,
            'creator_id' => Session::get('user_id'),
            'material_link' => $_POST['material_link'] ?? '',
            'edit_link' => $_POST['edit_link'] ?? '',
            'catatan' => $_POST['catatan'] ?? '',
            'referensi' => $_POST['referensi'] ?? '',
            'deadline' => $_POST['deadline'] ?: null,
            'deadline_active' => isset($_POST['deadline_active']) ? (int)$_POST['deadline_active'] : 0,
            'file_attachment' => $fileAttachment,
            'sort_order' => $sortOrder,
            'created_by' => Session::get('user_id')
        ];
    }

    private function syncCalendar(string $date): void
    {
        $tasks = Database::fetchAll(
            "SELECT content_type, COUNT(*) as cnt FROM timeline_tasks WHERE task_date = ? GROUP BY content_type",
            [$date]
        );

        $feedCount = 0;
        $reelsCount = 0;
        $storyCount = 0;

        foreach ($tasks as $t) {
            $type = strtolower($t['content_type']);
            if (strpos($type, 'feed') !== false) $feedCount += $t['cnt'];
            elseif (strpos($type, 'reels') !== false) $reelsCount += $t['cnt'];
            elseif (strpos($type, 'story') !== false) $storyCount += $t['cnt'];
        }

        $dayName = $this->hariIndonesia(date('N', strtotime($date)));

        $existing = Database::fetch("SELECT id FROM timeline_calendar WHERE calendar_date = ?", [$date]);
        if ($existing) {
            Database::execute(
                "UPDATE timeline_calendar SET feed_count = ?, reels_count = ?, story_count = ?, day_name = ? WHERE id = ?",
                [$feedCount, $reelsCount, $storyCount, $dayName, $existing['id']]
            );
        } else {
            Database::execute(
                "INSERT INTO timeline_calendar (calendar_date, day_name, feed_count, reels_count, story_count, post_time) VALUES (?, ?, ?, ?, ?, ?)",
                [$date, $dayName, $feedCount, $reelsCount, $storyCount, '08.00 - 18.00']
            );
        }
    }

    private function hariIndonesia(int $dayNum): string
    {
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        return $days[$dayNum] ?? '';
    }

    private function bulanIndonesia(int $monthNum): string
    {
        $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                   7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        return $months[$monthNum] ?? '';
    }
}
