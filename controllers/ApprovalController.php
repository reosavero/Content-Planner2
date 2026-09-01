<?php












class ApprovalController extends Controller
{
    


    public function taskIndex(): void
    {
        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');

        if ($roleSlug === 'magang') {
            $userName = Session::get('user_name');
            $currentMonthStart = date('Y-m-01 00:00:00');
            $submittedTasks = Database::fetchAll(
                "SELECT tt.*, creator.name AS assigned_by_name,
                        p.name AS platform_name, p.icon AS platform_icon
                 FROM timeline_tasks tt
                 LEFT JOIN users creator ON creator.id = tt.creator_id
                 LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
                 WHERE (tt.assignee_id = ? OR tt.assigned_to = ? OR LOWER(TRIM(tt.pic_name)) = LOWER(TRIM(?)))
                   AND (
                        tt.status IN ('Pending Approval', 'Need Revision')
                        OR (tt.status IN ('Approved', 'Selesai', 'Publish') AND COALESCE(tt.approved_at, tt.task_date) >= ?)
                   )
                 ORDER BY CASE WHEN tt.status IN ('Pending Approval', 'Need Revision') THEN 1 ELSE 2 END ASC,
                          tt.task_date ASC,
                          tt.updated_at DESC, tt.id ASC",
                [$userId, $userId, $userName, $currentMonthStart]
            );

            
            $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
            $groupedTasks = [];
            foreach ($submittedTasks as $task) {
                $date = $task['task_date'] ?? date('Y-m-d');
                if (!isset($groupedTasks[$date])) {
                    $engDay = date('l', strtotime($date));
                    $groupedTasks[$date] = [
                        'date' => $date,
                        'day_name' => $dayNames[$engDay] ?? $engDay,
                        'has_pending' => false,
                        'tasks' => [],
                    ];
                }
                if (in_array($task['status'], ['Pending Approval', 'Need Revision'])) {
                    $groupedTasks[$date]['has_pending'] = true;
                }
                $groupedTasks[$date]['tasks'][] = $task;
            }

            
            foreach ($groupedTasks as $date => &$group) {
                usort($group['tasks'], function($a, $b) {
                    $aPending = in_array($a['status'], ['Pending Approval', 'Need Revision']) ? 1 : 2;
                    $bPending = in_array($b['status'], ['Pending Approval', 'Need Revision']) ? 1 : 2;
                    if ($aPending !== $bPending) {
                        return $aPending <=> $bPending;
                    }
                    return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
                });
            }
            unset($group);

            
            uksort($groupedTasks, function($keyA, $keyB) use ($groupedTasks) {
                $hasPendingA = $groupedTasks[$keyA]['has_pending'] ? 1 : 2;
                $hasPendingB = $groupedTasks[$keyB]['has_pending'] ? 1 : 2;
                if ($hasPendingA !== $hasPendingB) {
                    return $hasPendingA <=> $hasPendingB;
                }
                return strcmp($keyA, $keyB);
            });

            if ($this->isAjax()) {
                ob_start();
                $this->viewPartial('approval/_cards_magang', ['groupedTasks' => $groupedTasks]);
                $html = ob_get_clean();
                $this->success(['html' => $html]);
            }

            $this->view('approval/magang_index', [
                'title' => 'Status Approval Task',
                'groupedTasks' => $groupedTasks,
            ]);
            return;
        }

        $this->requireTaskAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search = Security::sanitize($_GET['search'] ?? '');
        $status = $_GET['status'] ?? 'Pending Approval';
        $platformId = $_GET['platform_id'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $deadline = $_GET['deadline'] ?? '';
        $uploadDate = $_GET['upload_date'] ?? '';

        if ($roleSlug === 'superadmin') {
            $where = "1=1";
            $params = [];
        } else {
            $where = "(tt.creator_id = ? OR tt.creator_id IS NULL OR tt.creator_id = 0 OR tt.status = 'Pending Approval')";
            $params = [Session::get('user_id')];
        }

        if ($search !== '') {
            $where .= " AND tt.title LIKE ?";
            $params[] = "%{$search}%";
        }
        if ($status !== '' && $status !== 'all') {
            $where .= " AND tt.status = ?";
            $params[] = $status;
        }
        if ($platformId !== '') {
            $where .= " AND tt.platform_id = ?";
            $params[] = $platformId;
        }
        if ($priority !== '') {
            $where .= " AND tt.priority = ?";
            $params[] = $priority;
        }
        if ($deadline !== '') {
            $where .= " AND tt.deadline = ?";
            $params[] = $deadline;
        }
        if ($uploadDate !== '') {
            $where .= " AND DATE(tt.submitted_at) = ?";
            $params[] = $uploadDate;
        }

        $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM timeline_tasks tt WHERE {$where}", $params);
        $totalPages = max(1, (int)ceil($count / $perPage));
        $offset = ($page - 1) * $perPage;

        $data = Database::fetchAll(
            "SELECT tt.*, assignee.name AS assignee_name, creator.name AS creator_name,
                    p.name AS platform_name, p.icon AS platform_icon, p.color AS platform_color
             FROM timeline_tasks tt
             LEFT JOIN users assignee ON assignee.id = tt.assignee_id
             LEFT JOIN users creator ON creator.id = tt.creator_id
             LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
             WHERE {$where}
             ORDER BY tt.submitted_at DESC, tt.updated_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $platforms = Database::fetchAll("SELECT id, name FROM platform_sosmed WHERE is_active = 1 ORDER BY name");

        if ($this->isAjax()) {
            ob_start();
            $this->viewPartial('approval/_cards_admin', ['data' => $data]);
            $html = ob_get_clean();
            $this->success(['html' => $html]);
        }

        $this->view('approval/index', [
            'title' => 'Approval Task',
            'data' => $data,
            'filters' => compact('search', 'status', 'platformId', 'priority', 'deadline', 'uploadDate'),
            'platforms' => $platforms,
            'pagination' => ['current_page' => $page, 'total_pages' => $totalPages, 'total' => $count],
        ]);
    }

    public function taskShow(string $id): void
    {
        $this->requireTaskAdmin();
        $task = $this->findOwnedTask($id);
        if (!$task) {
            $this->json(['success' => false, 'message' => 'Task tidak ditemukan'], 404);
        }
        $history = Database::fetchAll(
            "SELECT al.*, u.name AS user_name FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.table_name = 'timeline_tasks' AND al.record_id = ?
             ORDER BY al.created_at ASC",
            [$id]
        );
        $this->json(['success' => true, 'data' => $task, 'history' => $history]);
    }

    public function taskApprove(string $id): void
    {
        $this->requireTaskAdmin();
        $task = $this->findOwnedTask($id, 'Pending Approval');
        if (!$task) {
            $this->json(['success' => false, 'message' => 'Task tidak ditemukan atau bukan milik Anda'], 404);
        }

        Database::execute(
            "UPDATE timeline_tasks SET status = 'Approved', approved_by = ?, approved_at = NOW(), revision_notes = NULL WHERE id = ?",
            [Session::get('user_id'), $id]
        );
        $this->logTaskAction($id, 'approve', 'Task disetujui');

        require_once HELPERS_PATH . 'Notification.php';
        if (!empty($task['assignee_id'])) {
            try {
                Notification::create(
                    (int)$task['assignee_id'],
                    'Task telah disetujui',
                    "Task \"{$task['title']}\" telah disetujui.",
                    'success', '/approval', 'bi-check-circle'
                );
            } catch (\Throwable $e) {}
        }
        $this->json(['success' => true, 'message' => 'Task disetujui']);
    }

    public function taskRevision(string $id): void
    {
        $this->requireTaskAdmin();
        $notes = trim(Security::sanitize($_POST['notes'] ?? ''));
        if ($notes === '') {
            $this->json(['success' => false, 'message' => 'Catatan revisi wajib diisi'], 422);
        }
        $task = $this->findOwnedTask($id, 'Pending Approval');
        if (!$task) {
            $this->json(['success' => false, 'message' => 'Task tidak ditemukan atau bukan milik Anda'], 404);
        }

        $oldNotes = trim($task['revision_notes'] ?? '');
        $revisionNotes = ($oldNotes !== '' ? $oldNotes . "\n\n" : '') . '[' . date('d/m/Y H:i') . '] ' . $notes;
        Database::execute(
            "UPDATE timeline_tasks SET status = 'Belum', revision_notes = ?, approved_by = NULL, approved_at = NULL WHERE id = ?",
            [$revisionNotes, $id]
        );
        $this->logTaskAction($id, 'request_revision', 'Revisi: ' . $notes);

        require_once HELPERS_PATH . 'Notification.php';
        if (!empty($task['assignee_id'])) {
            Notification::create(
                (int)$task['assignee_id'],
                'Task memerlukan revisi',
                "Task \"{$task['title']}\" memerlukan revisi. Catatan: {$notes}",
                'warning',
                '/planning?bulan=' . date('n', strtotime($task['task_date'])) . '&tahun=' . date('Y', strtotime($task['task_date'])),
                'bi-arrow-counterclockwise', 'task_revision', 'Task Revision'
            );
        }
        $this->json(['success' => true, 'message' => 'Revisi dikirim kepada user']);
    }

    public function archive(): void
    {
        $roleSlug = Session::get('user_role_slug');
        $userId = Session::get('user_id');
        $userName = Session::get('user_name');
        $currentMonthStart = date('Y-m-01 00:00:00');
        
        
        $archiveStart = date('Y-m-01 00:00:00', strtotime('-2 months', strtotime(date('Y-m-01'))));

        if ($roleSlug === 'magang') {
            $approvedTasks = Database::fetchAll(
                "SELECT tt.*, creator.name AS assigned_by_name,
                        approver.name AS approver_name,
                        p.name AS platform_name, p.icon AS platform_icon, p.color AS platform_color,
                        assignee.name AS assignee_name
                 FROM timeline_tasks tt
                 LEFT JOIN users creator ON creator.id = tt.creator_id
                 LEFT JOIN users approver ON approver.id = tt.approved_by
                 LEFT JOIN users assignee ON assignee.id = tt.assignee_id
                 LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
                 WHERE (tt.assignee_id = ? OR tt.assigned_to = ? OR LOWER(TRIM(tt.pic_name)) = LOWER(TRIM(?)))
                   AND tt.status IN ('Approved', 'Selesai', 'Publish')
                   AND tt.task_date < ?
                 ORDER BY tt.task_date DESC, tt.id DESC",
                [$userId, $userId, $userName, $currentMonthStart]
            );
            $viewName = 'approval/magang_archive';
        } else {
            
            $approvedTasks = Database::fetchAll(
                "SELECT tt.*, creator.name AS assigned_by_name,
                        approver.name AS approver_name,
                        p.name AS platform_name, p.icon AS platform_icon, p.color AS platform_color,
                        assignee.name AS assignee_name
                 FROM timeline_tasks tt
                 LEFT JOIN users creator ON creator.id = tt.creator_id
                 LEFT JOIN users approver ON approver.id = tt.approved_by
                 LEFT JOIN users assignee ON assignee.id = tt.assignee_id
                 LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
                 WHERE tt.task_date < ?
                 ORDER BY tt.task_date DESC, tt.id DESC",
                [$archiveStart]
            );
            $viewName = 'approval/admin_archive';
        }

        $monthNamesIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $dayNamesIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        $groupedMonths = [];
        foreach ($approvedTasks as $task) {
            $approvalDate = !empty($task['task_date']) ? $task['task_date'] : date('Y-m-d');
            $year = date('Y', strtotime($approvalDate));
            $monthNum = (int)date('m', strtotime($approvalDate));
            $monthKey = sprintf('%04d-%02d', $year, $monthNum);
            $monthName = ($monthNamesIndo[$monthNum] ?? 'Bulan') . ' ' . $year;

            if (!isset($groupedMonths[$monthKey])) {
                $groupedMonths[$monthKey] = [
                    'key' => $monthKey,
                    'name' => $monthName,
                    'year' => $year,
                    'month' => $monthNum,
                    'total_tasks' => 0,
                    'date_groups' => []
                ];
            }

            $taskDate = $task['task_date'] ?? '';
            $groupedMonths[$monthKey]['total_tasks']++;

            if (!isset($groupedMonths[$monthKey]['date_groups'][$taskDate])) {
                $engDay = date('l', strtotime($taskDate));
                $groupedMonths[$monthKey]['date_groups'][$taskDate] = [
                    'day_name' => $dayNamesIndo[$engDay] ?? $engDay,
                    'tasks' => []
                ];
            }
            $groupedMonths[$monthKey]['date_groups'][$taskDate]['tasks'][] = $task;
        }

        
        
        foreach ($groupedMonths as $key => $month) {
            ksort($groupedMonths[$key]['date_groups']);
        }

        krsort($groupedMonths);

        $this->view($viewName, [
            'title' => 'Archive Task Disetujui',
            'groupedMonths' => $groupedMonths,
            'totalTasks' => count($approvedTasks),
            'isAdminView' => ($roleSlug !== 'magang')
        ]);
    }

    public function archiveShow(string $id): void
    {
        $roleSlug = Session::get('user_role_slug');
        $userId = Session::get('user_id');
        $userName = Session::get('user_name');
        $currentMonthStart = date('Y-m-01 00:00:00');
        $archiveStart = date('Y-m-01 00:00:00', strtotime('-2 months', strtotime(date('Y-m-01'))));

        if ($roleSlug === 'magang') {
            $task = Database::fetch(
                "SELECT tt.*, creator.name AS creator_name, assignee.name AS assignee_name, approver.name AS approver_name,
                        p.name AS platform_name, p.icon AS platform_icon, p.color AS platform_color
                 FROM timeline_tasks tt
                 LEFT JOIN users creator ON creator.id = tt.creator_id
                 LEFT JOIN users assignee ON assignee.id = tt.assignee_id
                 LEFT JOIN users approver ON approver.id = tt.approved_by
                 LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
                 WHERE tt.id = ? AND (tt.assignee_id = ? OR tt.assigned_to = ? OR LOWER(TRIM(tt.pic_name)) = LOWER(TRIM(?))) AND tt.status IN ('Approved', 'Selesai', 'Publish')",
                [$id, $userId, $userId, $userName]
            );
        } else {
            $task = Database::fetch(
                "SELECT tt.*, creator.name AS creator_name, assignee.name AS assignee_name, approver.name AS approver_name,
                        p.name AS platform_name, p.icon AS platform_icon, p.color AS platform_color
                 FROM timeline_tasks tt
                 LEFT JOIN users creator ON creator.id = tt.creator_id
                 LEFT JOIN users assignee ON assignee.id = tt.assignee_id
                 LEFT JOIN users approver ON approver.id = tt.approved_by
                 LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
                 WHERE tt.id = ? AND tt.task_date < ?",
                [$id, $archiveStart]
            );
        }

        if (!$task) {
            $this->json(['success' => false, 'message' => 'Archive tidak ditemukan'], 404);
            return;
        }
        $history = Database::fetchAll(
            "SELECT al.*, u.name AS user_name FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id
             WHERE al.table_name = 'timeline_tasks' AND al.record_id = ? ORDER BY al.created_at ASC",
            [$id]
        );
        $this->json(['success' => true, 'data' => $task, 'history' => $history]);
    }

    



    public function archiveDeleteMonth(): void
    {
        if (Session::get('user_role_slug') !== 'superadmin') {
            $this->json(['success' => false, 'message' => 'Akses ditolak: hanya Superadmin yang dapat menghapus archive'], 403);
            return;
        }

        $monthKey = trim($_POST['month'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
            $this->json(['success' => false, 'message' => 'Bulan tidak valid'], 422);
            return;
        }

        $monthStart = $monthKey . '-01 00:00:00';
        $monthEnd = date('Y-m-01', strtotime($monthKey . '-01 +1 month')) . ' 00:00:00';

        $deleted = Database::execute(
            "DELETE FROM timeline_tasks
             WHERE task_date >= ? AND task_date < ?",
            [$monthStart, $monthEnd]
        );

        $this->json(['success' => true, 'message' => 'Semua task bulan ' . $monthKey . ' berhasil dihapus dari database']);
    }

    public function archiveDeleteTask(): void
    {
        if (Session::get('user_role_slug') !== 'superadmin') {
            $this->json(['success' => false, 'message' => 'Akses ditolak: hanya Superadmin yang dapat menghapus archive'], 403);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID task tidak valid'], 422);
            return;
        }

        $deleted = Database::execute(
            "DELETE FROM timeline_tasks WHERE id = ? AND task_date < ?",
            [$id, date('Y-m-01 00:00:00', strtotime('-2 months', strtotime(date('Y-m-01'))))]
        );

        if ($deleted) {
            $this->json(['success' => true, 'message' => 'Task berhasil dihapus dari database']);
        }
        $this->json(['success' => false, 'message' => 'Task tidak ditemukan atau sudah dihapus'], 404);
    }

    private function requireTaskAdmin(): void
    {
        if (!in_array(Session::get('user_role_slug'), ['admin', 'superadmin'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            }
            $this->redirectWith('/dashboard', 'error', 'Akses approval hanya untuk Admin.');
        }
    }

    private function findOwnedTask(string $id, ?string $status = null): ?array
    {
        $roleSlug = Session::get('user_role_slug');
        if ($roleSlug === 'superadmin') {
            $where = "tt.id = ?";
            $params = [$id];
        } else {
            $where = "tt.id = ? AND (tt.creator_id = ? OR tt.creator_id IS NULL OR tt.creator_id = 0 OR tt.status = 'Pending Approval')";
            $params = [$id, Session::get('user_id')];
        }
        if ($status !== null) {
            $where .= " AND tt.status = ?";
            $params[] = $status;
        }
        return Database::fetch(
            "SELECT tt.*, assignee.name AS assignee_name, creator.name AS creator_name,
                    p.name AS platform_name, p.icon AS platform_icon, p.color AS platform_color
             FROM timeline_tasks tt
             LEFT JOIN users assignee ON assignee.id = tt.assignee_id
             LEFT JOIN users creator ON creator.id = tt.creator_id
             LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
             WHERE {$where}",
            $params
        ) ?: null;
    }

    private function logTaskAction(string $id, string $action, string $description): void
    {
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, ?, 'approval', 'timeline_tasks', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $action, $id, $description, $_SERVER['REMOTE_ADDR']]
        );
    }

    


    public function index(): void
    {
        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');

        if ($roleSlug === 'magang') {
            $userName = Session::get('user_name');
            $currentMonthStart = date('Y-m-01 00:00:00');
            $submittedTasks = Database::fetchAll(
                "SELECT tt.*, creator.name AS assigned_by_name,
                        p.name AS platform_name, p.icon AS platform_icon
                 FROM timeline_tasks tt
                 LEFT JOIN users creator ON creator.id = tt.creator_id
                 LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
                 WHERE (tt.assignee_id = ? OR tt.assigned_to = ? OR LOWER(TRIM(tt.pic_name)) = LOWER(TRIM(?)))
                   AND (
                        tt.status IN ('Pending Approval', 'Need Revision')
                        OR (tt.status IN ('Approved', 'Selesai', 'Publish') AND COALESCE(tt.approved_at, tt.task_date) >= ?)
                   )
                 ORDER BY CASE WHEN tt.status IN ('Pending Approval', 'Need Revision') THEN 1 ELSE 2 END ASC,
                          tt.task_date ASC,
                          tt.updated_at DESC, tt.id ASC",
                [$userId, $userId, $userName, $currentMonthStart]
            );

            
            $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
            $groupedTasks = [];
            foreach ($submittedTasks as $task) {
                $date = $task['task_date'] ?? date('Y-m-d');
                if (!isset($groupedTasks[$date])) {
                    $engDay = date('l', strtotime($date));
                    $groupedTasks[$date] = [
                        'date' => $date,
                        'day_name' => $dayNames[$engDay] ?? $engDay,
                        'has_pending' => false,
                        'tasks' => [],
                    ];
                }
                if (in_array($task['status'], ['Pending Approval', 'Need Revision'])) {
                    $groupedTasks[$date]['has_pending'] = true;
                }
                $groupedTasks[$date]['tasks'][] = $task;
            }

            
            foreach ($groupedTasks as $date => &$group) {
                usort($group['tasks'], function($a, $b) {
                    $aPending = in_array($a['status'], ['Pending Approval', 'Need Revision']) ? 1 : 2;
                    $bPending = in_array($b['status'], ['Pending Approval', 'Need Revision']) ? 1 : 2;
                    if ($aPending !== $bPending) {
                        return $aPending <=> $bPending;
                    }
                    return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
                });
            }
            unset($group);

            
            uksort($groupedTasks, function($keyA, $keyB) use ($groupedTasks) {
                $hasPendingA = $groupedTasks[$keyA]['has_pending'] ? 1 : 2;
                $hasPendingB = $groupedTasks[$keyB]['has_pending'] ? 1 : 2;
                if ($hasPendingA !== $hasPendingB) {
                    return $hasPendingA <=> $hasPendingB;
                }
                return strcmp($keyA, $keyB);
            });

            $this->view('approval/magang_index', [
                'title' => 'Status Approval Task',
                'groupedTasks' => $groupedTasks,
            ]);
            return;
        }

        if (!in_array($roleSlug, ['admin', 'superadmin'])) {
            $this->redirectWith('/dashboard', 'error', 'Akses approval hanya untuk Admin.');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $status = $_GET['status'] ?? 'review';
        $search = Security::sanitize($_GET['search'] ?? '');
        $platformId = $_GET['platform_id'] ?? '';

        $where = "pk.deleted_at IS NULL";
        $params = [];

        
        if ($roleSlug === 'magang') {
            
            $where .= " AND pk.created_by = ?";
            $params[] = $userId;
        } elseif (in_array($roleSlug, ['admin', 'superadmin'])) {
            
            $where .= " AND (pk.status IN ('review', 'approved', 'scheduled'))";
        }

        
        if (!empty($search)) {
            $where .= " AND pk.judul LIKE ?";
            $params[] = "%{$search}%";
        }
        if (!empty($status) && $status !== 'all') {
            if ($status === 'recheck') {
                $where .= " AND pk.recheck_needed = 1";
            } else {
                $where .= " AND pk.status = ?";
                $params[] = $status;
            }
        }
        if (!empty($platformId)) {
            $where .= " AND pk.platform_id = ?";
            $params[] = $platformId;
        }

        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM planning_konten pk WHERE {$where}",
            $params
        );

        $totalPages = max(1, ceil($count / $perPage));
        $offset = ($page - 1) * $perPage;

        $data = Database::fetchAll(
            "SELECT pk.*, 
                    p.name as platform_name, p.icon as platform_icon, p.color as platform_color,
                    creator.name as creator_name, creator.avatar as creator_avatar,
                    editor.name as editor_name,
                    approver.name as approver_name,
                    rechecker.name as rechecker_name
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             LEFT JOIN users creator ON creator.id = pk.created_by
             LEFT JOIN users editor ON editor.id = pk.editor_id
             LEFT JOIN users approver ON approver.id = pk.approved_by
             LEFT JOIN users rechecker ON rechecker.id = pk.rechecked_by
             WHERE {$where}
             ORDER BY pk.recheck_needed DESC, pk.updated_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        
        $platforms = Database::fetchAll("SELECT id, name FROM platform_sosmed WHERE is_active = 1 ORDER BY name");

        
        $recheckCount = 0;
        if ($roleSlug === 'superadmin') {
            $recheckCount = Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE recheck_needed = 1 AND deleted_at IS NULL"
            ) ?: 0;
        }

        $this->view('approval/index', [
            'title' => 'Approval Konten',
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total' => $count,
            ],
            'filters' => ['search' => $search, 'status' => $status, 'platform_id' => $platformId],
            'platforms' => $platforms,
            'recheckCount' => $recheckCount,
            'breadcrumbs' => [['label' => 'Konten', 'url' => '#'], ['label' => 'Approval', 'url' => '#']],
            'pageActions' => [
                ['label' => 'Buat Planning', 'icon' => 'bi-plus', 'variant' => 'primary', 'onclick' => "window.location.href='" . BASE_URL . "/planning/create'"],
            ],
        ]);
    }

    


    public function show(string $id): void
    {
        if (!in_array(Session::get('user_role_slug'), ['admin', 'superadmin'])) {
            $this->redirectWith('/dashboard', 'error', 'Akses approval hanya untuk Admin.');
        }

        $planning = Database::fetch(
            "SELECT pk.*, 
                    p.name as platform_name, p.icon as platform_icon, p.color as platform_color,
                    creator.name as creator_name, creator.avatar as creator_avatar,
                    editor.name as editor_name,
                    approver.name as approver_name,
                    rechecker.name as rechecker_name,
                    pic.name as pic_name
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             LEFT JOIN users creator ON creator.id = pk.created_by
             LEFT JOIN users editor ON editor.id = pk.editor_id
             LEFT JOIN users approver ON approver.id = pk.approved_by
             LEFT JOIN users rechecker ON rechecker.id = pk.rechecked_by
             LEFT JOIN users pic ON pic.id = pk.pic_id
             WHERE pk.id = ? AND pk.deleted_at IS NULL",
            [$id]
        );

        if (!$planning) {
            $this->redirectWith('/approval', 'error', 'Konten tidak ditemukan');
        }

        
        $approvalHistory = Database::fetchAll(
            "SELECT al.*, u.name as user_name 
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.record_id = ? AND al.table_name = 'planning_konten' AND al.module = 'approval'
             ORDER BY al.created_at DESC
             LIMIT 20",
            [$id]
        );

        $this->view('approval/show', [
            'title' => 'Detail Approval: ' . $planning['judul'],
            'planning' => $planning,
            'approvalHistory' => $approvalHistory,
            'breadcrumbs' => [
                ['label' => 'Konten', 'url' => '#'],
                ['label' => 'Approval', 'url' => '/approval'],
                ['label' => 'Detail', 'url' => '#'],
            ],
        ]);
    }

    


    public function approve(string $id): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $planning = Database::fetch(
            "SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL AND status IN ('review', 'approved', 'revision')",
            [$id]
        );

        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Konten tidak ditemukan atau tidak dapat diapprove']);
        }

        $roleSlug = Session::get('user_role_slug');
        $userId = Session::get('user_id');
        $recheckNeeded = 0;
        $approvedByRole = $roleSlug;

        
        if ($roleSlug === 'superadmin') {
            
            $recheckNeeded = 0;
        } elseif ($roleSlug === 'admin') {
            
            $recheckNeeded = 0;
        } else {
            $this->json(['success' => false, 'message' => 'Role Anda tidak memiliki akses approval']);
        }

        $notes = Security::sanitize($_POST['notes'] ?? '');

        Database::beginTransaction();

        try {
            
            Database::execute(
                "UPDATE planning_konten SET 
                    status = 'approved',
                    is_approved = 1,
                    approved_by = ?,
                    approved_by_role = ?,
                    approved_at = NOW(),
                    recheck_needed = ?,
                    editor_id = COALESCE(editor_id, ?),
                    revision_note = ?,
                    updated_at = NOW()
                 WHERE id = ?",
                [$userId, $approvedByRole, $recheckNeeded, $userId, $notes ?: $planning['revision_note'], $id]
            );

            
            if ($recheckNeeded) {
                Database::execute(
                    "INSERT INTO recheck_logs (planning_id, approved_by, approved_by_role, status, notes)
                     VALUES (?, ?, ?, 'pending_recheck', ?)",
                    [$id, $userId, $roleSlug, $notes]
                );
            }

            $description = $recheckNeeded 
                ? "Approve oleh {$roleSlug} (menunggu re-check Super Admin)"
                : "Approve oleh {$roleSlug}";
            
            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'approve', 'approval', 'planning_konten', ?, ?, ?)",
                [$userId, Session::get('user_role_id'), $id, $description, $_SERVER['REMOTE_ADDR']]
            );

            Database::commit();

            
            $this->sendApprovalNotification($planning, $userId, $recheckNeeded);

            $msg = $recheckNeeded 
                ? 'Konten berhasil diapprove. Menunggu re-check Super Admin.'
                : 'Konten berhasil diapprove.';

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => $msg]);
            }
            $this->redirectWith('/approval', 'success', $msg);

        } catch (Exception $e) {
            Database::rollback();
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    


    public function recheck(string $id): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $planning = Database::fetch(
            "SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL AND recheck_needed = 1",
            [$id]
        );

        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Konten tidak ditemukan atau tidak perlu re-check']);
        }

        if (Session::get('user_role_slug') !== 'superadmin') {
            $this->json(['success' => false, 'message' => 'Hanya Super Admin yang dapat melakukan re-check']);
        }

        $action = $_POST['action'] ?? 'approve'; 
        $notes = Security::sanitize($_POST['notes'] ?? '');

        Database::beginTransaction();

        try {
            if ($action === 'approve') {
                
                Database::execute(
                    "UPDATE planning_konten SET 
                        recheck_needed = 0,
                        rechecked_by = ?,
                        rechecked_at = NOW(),
                        status = 'approved',
                        updated_at = NOW()
                     WHERE id = ?",
                    [Session::get('user_id'), $id]
                );

                
                Database::execute(
                    "UPDATE recheck_logs SET rechecked_by = ?, rechecked_at = NOW(), status = 'approved', notes = CONCAT(COALESCE(notes,''), ' | Re-check: ', ?) WHERE planning_id = ? AND status = 'pending_recheck'",
                    [Session::get('user_id'), $notes, $id]
                );

                $msg = 'Re-check selesai. Konten telah diapprove.';

            } else {
                
                Database::execute(
                    "UPDATE planning_konten SET 
                        recheck_needed = 0,
                        rechecked_by = ?,
                        rechecked_at = NOW(),
                        status = 'revision',
                        revision_note = ?,
                        updated_at = NOW()
                     WHERE id = ?",
                    [Session::get('user_id'), $notes ?: 'Re-check: Ditolak oleh Super Admin', $id]
                );

                
                Database::execute(
                    "UPDATE recheck_logs SET rechecked_by = ?, rechecked_at = NOW(), status = 'rejected', notes = CONCAT(COALESCE(notes,''), ' | Re-check rejected: ', ?) WHERE planning_id = ? AND status = 'pending_recheck'",
                    [Session::get('user_id'), $notes, $id]
                );

                $msg = 'Re-check: Konten dikembalikan ke revisi.';
            }

            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'recheck', 'approval', 'planning_konten', ?, ?, ?)",
                [Session::get('user_id'), Session::get('user_role_id'), $id, "Re-check oleh Super Admin: {$action} - {$notes}", $_SERVER['REMOTE_ADDR']]
            );

            Database::commit();

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => $msg]);
            }
            $this->redirectWith('/approval', 'success', $msg);

        } catch (Exception $e) {
            Database::rollback();
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    


    public function reject(string $id): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $planning = Database::fetch(
            "SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL AND status IN ('review', 'revision')",
            [$id]
        );

        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Konten tidak ditemukan']);
        }

        $reason = Security::sanitize($_POST['reason'] ?? '');
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Alasan penolakan harus diisi']);
        }

        Database::execute(
            "UPDATE planning_konten SET status = 'cancelled', revision_note = ?, updated_at = NOW() WHERE id = ?",
            [$reason, $id]
        );

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'reject', 'approval', 'planning_konten', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $id, 'Ditolak: ' . $reason, $_SERVER['REMOTE_ADDR']]
        );

        
        require_once HELPERS_PATH . 'Notification.php';
        Notification::create(
            $planning['created_by'],
            '❌ Konten Ditolak',
            "Konten \"{$planning['judul']}\" ditolak. Alasan: {$reason}",
            'error',
            BASE_URL . '/planning/' . $id,
            'bi-x-circle',
            'approval',
            'Approval'
        );

        $this->json(['success' => true, 'message' => 'Konten ditolak']);
    }

    


    public function requestRevision(string $id): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $planning = Database::fetch(
            "SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL AND status IN ('review', 'approved')",
            [$id]
        );

        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Konten tidak ditemukan']);
        }

        $notes = Security::sanitize($_POST['notes'] ?? '');
        if (empty($notes)) {
            $this->json(['success' => false, 'message' => 'Catatan revisi harus diisi']);
        }

        $revisionCount = ($planning['revision_count'] ?? 0) + 1;

        Database::execute(
            "UPDATE planning_konten SET 
                status = 'revision', 
                revision_note = ?,
                revision_count = ?,
                updated_at = NOW()
             WHERE id = ?",
            [$notes, $revisionCount, $id]
        );

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'request_revision', 'approval', 'planning_konten', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $id, 'Minta revisi: ' . $notes, $_SERVER['REMOTE_ADDR']]
        );

        
        require_once HELPERS_PATH . 'Notification.php';
        Notification::create(
            $planning['created_by'],
            '🔄 Revisi Diminta',
            "Konten \"{$planning['judul']}\" perlu direvisi. Catatan: {$notes}",
            'warning',
            BASE_URL . '/planning/' . $id . '/edit',
            'bi-arrow-counterclockwise',
            'approval',
            'Approval'
        );

        $this->json(['success' => true, 'message' => 'Revisi diminta']);
    }

    


    private function addToSchedulerQueue(array $planning): void
    {
        
        $existing = Database::fetch(
            "SELECT id FROM scheduler_queue WHERE planning_id = ?",
            [$planning['id']]
        );

        if ($existing) {
            Database::execute(
                "UPDATE scheduler_queue SET status = 'queued', scheduled_at = ?, updated_at = NOW() WHERE id = ?",
                [$planning['scheduled_at'] ?? date('Y-m-d H:i:s'), $existing['id']]
            );
            return;
        }

        
        if (empty($planning['platform_akun_id'])) {
            
            if (!empty($planning['platform_id'])) {
                $defaultAccount = Database::fetch(
                    "SELECT id FROM platform_akun WHERE platform_id = ? AND is_active = 1 LIMIT 1",
                    [$planning['platform_id']]
                );
                if ($defaultAccount) {
                    $planning['platform_akun_id'] = $defaultAccount['id'];
                }
            }
        }

        if (empty($planning['platform_akun_id'])) {
            return; 
        }

        Database::execute(
            "INSERT INTO scheduler_queue (planning_id, platform_akun_id, status, scheduled_at, priority, created_at)
             VALUES (?, ?, 'queued', ?, ?, NOW())",
            [
                $planning['id'],
                $planning['platform_akun_id'],
                $planning['scheduled_at'] ?? date('Y-m-d H:i:s', strtotime('+1 hour')),
                $planning['priority'] === 'urgent' ? 999 : ($planning['priority'] === 'high' ? 100 : 0)
            ]
        );
    }

    


    private function sendApprovalNotification(array $planning, int $approverId, bool $recheckNeeded): void
    {
        require_once HELPERS_PATH . 'Notification.php';

        $title = $recheckNeeded 
            ? '✅ Konten Diapprove (Menunggu Re-Check)'
            : '✅ Konten Diapprove';
        
        $message = $recheckNeeded
            ? "Konten \"{$planning['judul']}\" telah diapprove oleh Admin KMB dan menunggu re-check Super Admin."
            : "Konten \"{$planning['judul']}\" telah diapprove.";

        
        Notification::create(
            $planning['created_by'],
            $title,
            $message,
            'success',
            BASE_URL . '/planning/' . $planning['id'],
            'bi-check-circle',
            'approval',
            'Approval'
        );

        
        if ($recheckNeeded) {
            $superadmins = Database::fetchAll(
                "SELECT id FROM users WHERE role_id IN (SELECT id FROM roles WHERE slug = 'superadmin') AND is_active = 1"
            );
            $superadminIds = array_column($superadmins, 'id');
            
            Notification::createBulk(
                $superadminIds,
                '🔄 Re-Check Diperlukan',
                "Konten \"{$planning['judul']}\" telah diapprove oleh Admin KMB dan menunggu re-check Anda.",
                'warning',
                BASE_URL . '/approval',
                'bi-shield-exclamation',
                'recheck',
                'Re-Check'
            );
        }
    }
}
