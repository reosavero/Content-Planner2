<?php









class CalendarController extends Controller
{
    


    public function index(): void
    {
        
        $platforms = Database::fetchAll("SELECT id, name, slug, icon, color FROM platform_sosmed WHERE is_active = 1 ORDER BY sort_order");
        $statuses = ['draft' => 'Draft', 'review' => 'Review', 'approved' => 'Approved', 'scheduled' => 'Terjadwal', 'posting' => 'Posting', 'success' => 'Berhasil', 'failed' => 'Gagal', 'revision' => 'Revisi', 'cancelled' => 'Dibatalkan'];

        
        $programs = Database::fetchAll("SELECT id, name FROM program_tv WHERE is_active = 1 ORDER BY sort_order");
        $kategoris = Database::fetchAll("SELECT id, name, color FROM kategori_konten WHERE is_active = 1 ORDER BY sort_order");
        $jenisKonten = Database::fetchAll("SELECT id, name FROM jenis_konten WHERE is_active = 1 ORDER BY id");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name");

        
        
        $commemorations = [
            
            '01-01' => 'Tahun Baru Masehi',
            '01-10' => 'Hari Gerakan Satu Juta Pohon',
            '01-15' => 'Hari Darma Samudera',
            '01-25' => 'Hari Gizi dan Makanan',
            '01-26' => 'Hari Kepabeanan Internasional',
            
            '02-02' => 'Hari Lahan Basah Sedunia',
            '02-04' => 'Hari Kanker Sedunia',
            '02-09' => 'Hari Pers Nasional',
            '02-14' => 'Hari Kasih Sayang (Valentine)',
            '02-22' => 'Hari Kepanduan Sedunia',
            
            '03-01' => 'Hari Penegakan Kedaulatan Negara',
            '03-08' => 'Hari Perempuan Internasional',
            '03-09' => 'Hari Musik Nasional',
            '03-11' => 'Hari Supersemar',
            '03-21' => 'Hari Hutan Sedunia & Hari Down Syndrome',
            '03-22' => 'Hari Air Sedunia',
            '03-23' => 'Hari Meteorologi Sedunia',
            '03-24' => 'Hari Tuberkulosis Sedunia',
            '03-30' => 'Hari Film Nasional',
            
            '04-02' => 'Hari Peduli Autisme Sedunia',
            '04-06' => 'Hari Nelayan Nasional',
            '04-07' => 'Hari Kesehatan Sedunia',
            '04-09' => 'Hari Penerbangan Nasional',
            '04-21' => 'Hari Kartini',
            '04-22' => 'Hari Bumi',
            '04-23' => 'Hari Buku Sedunia',
            '04-24' => 'Hari Angkutan Nasional',
            '04-25' => 'Hari Otonomi Daerah',
            '04-26' => 'Hari Kekayaan Intelektual Sedunia',
            
            '05-01' => 'Hari Buruh Internasional',
            '05-02' => 'Hari Pendidikan Nasional',
            '05-03' => 'Hari Kebebasan Pers Sedunia',
            '05-05' => 'Hari Bidan Internasional',
            '05-08' => 'Hari Palang Merah Internasional',
            '05-17' => 'Hari Buku Nasional',
            '05-20' => 'Hari Kebangkitan Nasional',
            '05-21' => 'Hari Peringatan Reformasi',
            '05-22' => 'Hari Keanekaragaman Hayati Internasional',
            '05-29' => 'Hari Lanjut Usia Nasional',
            '05-31' => 'Hari Tanpa Tembakau Sedunia',
            
            '06-01' => 'Hari Lahir Pancasila',
            '06-03' => 'Hari Pasar Modal Indonesia',
            '06-05' => 'Hari Lingkungan Hidup Sedunia',
            '06-08' => 'Hari Laut Sedunia',
            '06-14' => 'Hari Donor Darah Sedunia',
            '06-15' => 'Hari Demam Berdarah Dengue ASEAN',
            '06-17' => 'Hari Penanggulangan Degradasi Lahan dan Kekeringan Dunia',
            '06-21' => 'Hari Musik Sedunia',
            '06-24' => 'Hari Bidan Nasional',
            '06-26' => 'Hari Anti Narkoba Sedunia',
            '06-29' => 'Hari Keluarga Nasional',
            
            '07-01' => 'Hari Bhayangkara',
            '07-05' => 'Hari Bank Indonesia',
            '07-11' => 'Hari Populasi Sedunia',
            '07-12' => 'Hari Koperasi Indonesia',
            '07-15' => 'Hari Keterampilan Pemuda Sedunia',
            '07-22' => 'Hari Bhakti Adhyaksa (Kejaksaan)',
            '07-23' => 'Hari Anak Nasional',
            '07-30' => 'Hari Persahabatan Sedunia',
            
            '08-08' => 'Hari Ulang Tahun ASEAN',
            '08-10' => 'Hari Veteran Nasional',
            '08-12' => 'Hari Remaja Internasional',
            '08-14' => 'Hari Pramuka',
            '08-17' => 'Hari Kemerdekaan Republik Indonesia',
            '08-18' => 'Hari Konstitusi Republik Indonesia',
            '08-19' => 'Hari Kemanusiaan Sedunia',
            '08-24' => 'Hari Ulang Tahun TVRI',
            
            '09-04' => 'Hari Pelanggan Nasional',
            '09-08' => 'Hari Aksara Internasional',
            '09-09' => 'Hari Olahraga Nasional',
            '09-11' => 'Hari Radio Republik Indonesia (RRI)',
            '09-15' => 'Hari Demokrasi Internasional',
            '09-16' => 'Hari Ozon Internasional',
            '09-17' => 'Hari Palang Merah Indonesia',
            '09-21' => 'Hari Perdamaian Internasional',
            '09-24' => 'Hari Tani',
            '09-27' => ['Hari Pos Telekomunikasi Telegraf', 'Hari Pariwisata Sedunia'],
            '09-28' => 'Hari Kereta Api',
            '09-30' => 'Hari Penerjemah Internasional',
            
            '10-01' => ['Hari Kesaktian Pancasila', 'Hari Lanjut Usia Internasional'],
            '10-02' => 'Hari Batik Nasional',
            '10-04' => 'Hari Hewan Sedunia',
            '10-05' => 'Hari Tentara Nasional Indonesia (TNI)',
            '10-09' => 'Hari Pos Sedunia',
            '10-10' => 'Hari Kesehatan Jiwa Sedunia',
            '10-13' => 'Hari Pengurangan Risiko Bencana Internasional',
            '10-15' => 'Hari Cuci Tangan Sedunia',
            '10-16' => 'Hari Pangan Sedunia',
            '10-17' => 'Hari Pengentasan Kemiskinan Internasional',
            '10-24' => 'Hari Perserikatan Bangsa-Bangsa (PBB)',
            '10-27' => 'Hari Listrik Nasional',
            '10-28' => 'Hari Sumpah Pemuda',
            '10-29' => 'Hari Stroke Sedunia',
            
            '11-10' => 'Hari Pahlawan',
            '11-12' => 'Hari Kesehatan Nasional',
            '11-14' => 'Hari Diabetes Sedunia',
            '11-16' => 'Hari Toleransi Internasional',
            '11-21' => ['Hari Pohon', 'Hari Ikan Nasional', 'Hari Televisi Sedunia'],
            '11-25' => ['Hari Guru Nasional', 'Hari Anti Kekerasan terhadap Perempuan'],
            '11-28' => 'Hari Menanam Pohon Indonesia',
            
            '12-01' => 'Hari AIDS Sedunia',
            '12-02' => 'Hari Internasional Penghapusan Perbudakan',
            '12-03' => 'Hari Disabilitas Internasional',
            '12-05' => ['Hari Sukarelawan Internasional', 'Hari Armada Republik Indonesia'],
            '12-07' => 'Hari Penerbangan Sipil Internasional',
            '12-09' => 'Hari Anti Korupsi Sedunia',
            '12-10' => 'Hari Hak Asasi Manusia',
            '12-12' => 'Hari Transmigrasi',
            '12-18' => 'Hari Migran Internasional',
            '12-19' => 'Hari Bela Negara',
            '12-20' => 'Hari Kesetiakawanan Sosial Nasional',
            '12-22' => 'Hari Ibu',
        ];

        
        
        $seedFlag = Database::fetch("SELECT value FROM settings WHERE `key` = 'calendar_builtin_notes_seeded'");
        if (!$seedFlag) {
            foreach ($commemorations as $key => $names) {
                $namesArr = is_array($names) ? $names : [$names];
                $seedDate = date('Y') . '-' . $key;
                foreach ($namesArr as $name) {
                    try {
                        Database::execute(
                            "INSERT INTO calendar_notes (note, note_date, color, created_by, created_at) VALUES (?, ?, '#dc3545', NULL, NOW())",
                            [$name, $seedDate]
                        );
                    } catch (Exception $e) {
                        
                    }
                }
            }
            try {
                Database::execute(
                    "INSERT INTO settings (`group`, `key`, `value`, `type`) VALUES ('calendar', 'calendar_builtin_notes_seeded', '1', 'text')"
                );
            } catch (Exception $e) {
                
            }
        }

        
        
        $notesByDate = [];
        $commemorationColors = [];
        $dbNotes = Database::fetchAll("SELECT id, note, note_date, color, created_by FROM calendar_notes ORDER BY (created_by IS NOT NULL) ASC, id ASC");
        foreach ($dbNotes as $n) {
            $key = date('m-d', strtotime($n['note_date']));
            $commemorationColors[$key] = $n['color'];
            if (!isset($notesByDate[$key])) {
                $notesByDate[$key] = [];
            }
            $notesByDate[$key][] = [
                'id' => (int)$n['id'],
                'text' => $n['note'],
                'color' => $n['color'],
                'builtin' => $n['created_by'] === null,
            ];
        }

        $canManageBuiltin = in_array(Session::get('user_role_slug'), ['superadmin', 'admin'], true);

        $this->view('calendar/index', [
            'title' => 'Kalender Konten',
            'platforms' => $platforms,
            'statuses' => $statuses,
            'commemorations' => $commemorations,
            'commemorationColors' => $commemorationColors,
            'canManageBuiltin' => $canManageBuiltin,
            'notesByDate' => $notesByDate,
            'programs' => $programs,
            'kategoris' => $kategoris,
            'jenisKonten' => $jenisKonten,
            'users' => $users,
            'breadcrumbs' => [['label' => 'Konten', 'url' => '#'], ['label' => 'Kalender', 'url' => '#']],
        ]);
    }

    


    public function getData(): void
    {
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $status = $_GET['status'] ?? '';
        $platformId = $_GET['platform_id'] ?? '';
        $picId = $_GET['pic_id'] ?? '';
        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');

        $where = "pk.deleted_at IS NULL AND pk.scheduled_at BETWEEN ? AND ?";
        $params = [$start . ' 00:00:00', $end . ' 23:59:59'];

        if ($roleSlug === 'magang') {
            $where .= " AND pk.created_by = ?";
            $params[] = $userId;
        }

        if (!empty($status)) {
            $where .= " AND pk.status = ?";
            $params[] = $status;
        }
        if (!empty($platformId)) {
            $where .= " AND pk.platform_id = ?";
            $params[] = $platformId;
        }
        if (!empty($picId)) {
            $where .= " AND pk.pic_id = ?";
            $params[] = $picId;
        }

        $events = Database::fetchAll(
            "SELECT pk.id, pk.judul as title, pk.scheduled_at as start, pk.status, pk.priority,
                    pk.jenis_konten, pk.media_type,
                    p.name as platform, p.slug as platform_slug, p.icon as platform_icon, p.color as platform_color,
                    kk.color as kategori_color, kk.name as kategori_name,
                    creator.name as creator_name, creator.id as creator_id,
                    pic.name as pic_name, pic.id as pic_id
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             LEFT JOIN kategori_konten kk ON kk.id = pk.kategori_id
             LEFT JOIN users creator ON creator.id = pk.created_by
             LEFT JOIN users pic ON pic.id = pk.pic_id
             WHERE {$where}
             ORDER BY pk.scheduled_at ASC, pk.priority DESC",
            $params
        );

        
        $statusColors = [
            'draft' => '#6c757d', 'review' => '#ffc107', 'approved' => '#0d6efd',
            'scheduled' => '#00b0ff', 'posting' => '#7c3aed', 'success' => '#00c853',
            'failed' => '#ff1744', 'revision' => '#ff9100', 'cancelled' => '#6c757d',
        ];

        $formatted = array_map(function($event) use ($statusColors) {
            $platformIcon = '';
            if (!empty($event['platform_slug'])) {
                $platformIcon = '<i class="bi ' . $event['platform_icon'] . '" style="color:' . $event['platform_color'] . '"></i>';
            }

            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start'],
                'end' => $event['start'],
                'allDay' => true,
                'backgroundColor' => $statusColors[$event['status']] ?? '#6c757d',
                'borderColor' => $statusColors[$event['status']] ?? '#6c757d',
                'textColor' => '#ffffff',
                'classNames' => ['fc-event-' . $event['status'], 'fc-event-priority-' . $event['priority']],
                'extendedProps' => [
                    'status' => $event['status'],
                    'priority' => $event['priority'],
                    'platform' => $event['platform'],
                    'platform_slug' => $event['platform_slug'],
                    'platform_icon' => $platformIcon,
                    'platform_color' => $event['platform_color'],
                    'kategori_color' => $event['kategori_color'],
                    'kategori_name' => $event['kategori_name'],
                    'creator_name' => $event['creator_name'],
                    'pic_name' => $event['pic_name'] ?? $event['creator_name'],
                    'media_type' => $event['media_type'],
                    'jenis_konten' => $event['jenis_konten'],
                ],
                'url' => BASE_URL . '/planning/' . $event['id'],
            ];
        }, $events);

        $this->json($formatted);
    }

    


    public function dragDrop(): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $id = $_POST['id'] ?? 0;
        $newStart = $_POST['start'] ?? '';
        $newPlatformId = $_POST['platform_id'] ?? '';

        if (empty($id) || empty($newStart)) {
            $this->json(['success' => false, 'message' => 'Data tidak lengkap']);
        }

        $planning = Database::fetch(
            "SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL AND status IN ('draft','review','approved','scheduled')",
            [$id]
        );

        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Planning tidak ditemukan atau tidak dapat dipindahkan']);
        }

        $newDate = date('Y-m-d', strtotime($newStart));
        $oldTime = $planning['jam_posting'] ?? '08:00';
        $newScheduledAt = $newDate . ' ' . $oldTime . ':00';

        $updates = ["tanggal_posting = ?, scheduled_at = ?, updated_at = NOW()"];
        $updateParams = [$newDate, $newScheduledAt];

        if (!empty($newPlatformId)) {
            $updates[] = "platform_id = ?";
            $updateParams[] = $newPlatformId;
        }

        $updateParams[] = $id;
        Database::execute(
            "UPDATE planning_konten SET " . implode(', ', $updates) . " WHERE id = ?",
            $updateParams
        );

        
        $schedulerItem = Database::fetch(
            "SELECT id FROM scheduler_queue WHERE planning_id = ? AND status IN ('queued','retry')",
            [$id]
        );
        if ($schedulerItem) {
            Database::execute(
                "UPDATE scheduler_queue SET scheduled_at = ?, updated_at = NOW() WHERE id = ?",
                [$newScheduledAt, $schedulerItem['id']]
            );
        }

        
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address) 
             VALUES (?, ?, 'reschedule', 'calendar', 'planning_konten', ?, 'Reschedule dari kalender: {$newDate}', ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $id, $_SERVER['REMOTE_ADDR']]
        );

        $this->json([
            'success' => true,
            'message' => 'Jadwal berhasil diubah',
            'data' => ['new_date' => $newDate, 'new_scheduled_at' => $newScheduledAt]
        ]);
    }

    


    public function timeline(): void
    {
        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');

        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+4 weeks'));

        $where = "pk.deleted_at IS NULL AND pk.tanggal_posting BETWEEN ? AND ?";
        $params = [$startDate, $endDate];

        if ($roleSlug === 'magang') {
            $where .= " AND pk.created_by = ?";
            $params[] = $userId;
        }

        
        $weeklyContent = Database::fetchAll(
            "SELECT 
                YEARWEEK(pk.tanggal_posting, 1) as year_week,
                MIN(pk.tanggal_posting) as week_start,
                MAX(pk.tanggal_posting) as week_end,
                COUNT(*) as total_content,
                SUM(CASE WHEN pk.status = 'success' THEN 1 ELSE 0 END) as done,
                SUM(CASE WHEN pk.status NOT IN ('success','cancelled') THEN 1 ELSE 0 END) as pending,
                GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as platforms
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE {$where}
             GROUP BY YEARWEEK(pk.tanggal_posting, 1)
             ORDER BY year_week ASC",
            $params
        );

        
        $pics = Database::fetchAll(
            "SELECT DISTINCT u.id, u.name, u.avatar 
             FROM users u 
             JOIN planning_konten pk ON pk.pic_id = u.id OR pk.created_by = u.id
             WHERE pk.deleted_at IS NULL AND u.is_active = 1
             ORDER BY u.name"
        );

        $this->view('calendar/timeline', [
            'title' => 'Timeline Konten',
            'weeklyContent' => $weeklyContent,
            'pics' => $pics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'breadcrumbs' => [['label' => 'Konten', 'url' => '#'], ['label' => 'Timeline', 'url' => '#']],
        ]);
    }

    


    public function timelineData(): void
    {
        $start = $_GET['start'] ?? date('Y-m-d');
        $end = $_GET['end'] ?? date('Y-m-d', strtotime('+4 weeks'));
        $picId = $_GET['pic_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $userId = Session::get('user_id');
        $roleSlug = Session::get('user_role_slug');

        $where = "pk.deleted_at IS NULL AND pk.tanggal_posting BETWEEN ? AND ?";
        $params = [$start, $end];

        if ($roleSlug === 'magang') {
            $where .= " AND pk.created_by = ?";
            $params[] = $userId;
        }
        if (!empty($picId)) {
            $where .= " AND (pk.pic_id = ? OR pk.created_by = ?)";
            $params[] = $picId;
            $params[] = $picId;
        }
        if (!empty($status)) {
            $where .= " AND pk.status = ?";
            $params[] = $status;
        }

        $data = Database::fetchAll(
            "SELECT pk.id, pk.judul, pk.tanggal_posting, pk.scheduled_at, pk.status, pk.priority,
                    pk.jenis_konten, pk.media_type,
                    p.name as platform, p.slug as platform_slug, p.icon as platform_icon, p.color as platform_color,
                    kk.name as kategori, kk.color as kategori_color,
                    creator.name as creator_name,
                    pic.name as pic_name
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             LEFT JOIN kategori_konten kk ON kk.id = pk.kategori_id
             LEFT JOIN users creator ON creator.id = pk.created_by
             LEFT JOIN users pic ON pic.id = pk.pic_id
             WHERE {$where}
             ORDER BY pk.tanggal_posting ASC, pk.priority DESC,
                      CASE WHEN pk.status = 'success' THEN 1 WHEN pk.status = 'failed' THEN 2 ELSE 0 END ASC",
            $params
        );

        $this->json(['success' => true, 'data' => $data]);
    }

    


    public function storeNote(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/calendar');
        }

        
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        $data = $this->validate($_POST, [
            'note' => 'required|min:3|max:255',
            'note_date' => 'required|date',
        ]);

        $color = !empty($_POST['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color']) ? $_POST['color'] : '#dc3545';

        try {
            $noteId = Database::insert(
                "INSERT INTO calendar_notes (note, note_date, color, created_by, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$data['note'], $data['note_date'], $color, Session::get('user_id')]
            );

            
            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'create', 'calendar', 'calendar_notes', ?, ?, ?)",
                [Session::get('user_id'), Session::get('user_role_id'), $noteId, 'Menambahkan noted kalender: ' . $data['note'], $_SERVER['REMOTE_ADDR']]
            );

            $this->redirectBackWith('success', 'Noted kalender berhasil ditambahkan.');
        } catch (Exception $e) {
            $this->redirectBackWith('error', 'Gagal menambahkan noted: ' . $e->getMessage());
        }
    }

    


    public function updateNote(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/calendar');
        }

        
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirectBackWith('error', 'Data noted tidak valid.');
        }
        if (!$this->canManageNote($id)) {
            $this->redirectBackWith('error', 'Anda tidak memiliki izin untuk mengubah noted ini.');
        }

        $data = $this->validate($_POST, [
            'note' => 'required|min:3|max:255',
            'note_date' => 'required|date',
        ]);

        $color = !empty($_POST['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color']) ? $_POST['color'] : '#dc3545';

        try {
            Database::execute(
                "UPDATE calendar_notes SET note = ?, note_date = ?, color = ? WHERE id = ?",
                [$data['note'], $data['note_date'], $color, $id]
            );

            
            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'update', 'calendar', 'calendar_notes', ?, ?, ?)",
                [Session::get('user_id'), Session::get('user_role_id'), $id, 'Mengubah noted kalender: ' . $data['note'], $_SERVER['REMOTE_ADDR']]
            );

            $this->redirectBackWith('success', 'Noted kalender berhasil diubah.');
        } catch (Exception $e) {
            $this->redirectBackWith('error', 'Gagal mengubah noted: ' . $e->getMessage());
        }
    }

    


    public function deleteNote(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/calendar');
        }

        
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirectBackWith('error', 'Data noted tidak valid.');
        }
        if (!$this->canManageNote($id)) {
            $this->redirectBackWith('error', 'Anda tidak memiliki izin untuk menghapus noted ini.');
        }

        try {
            $note = Database::fetchAll("SELECT note FROM calendar_notes WHERE id = ?", [$id]);
            $noteText = $note[0]['note'] ?? '';

            Database::execute("DELETE FROM calendar_notes WHERE id = ?", [$id]);

            
            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'delete', 'calendar', 'calendar_notes', ?, ?, ?)",
                [Session::get('user_id'), Session::get('user_role_id'), $id, 'Menghapus noted kalender: ' . $noteText, $_SERVER['REMOTE_ADDR']]
            );

            $this->redirectBackWith('success', 'Noted kalender berhasil dihapus.');
        } catch (Exception $e) {
            $this->redirectBackWith('error', 'Gagal menghapus noted: ' . $e->getMessage());
        }
    }

    


    private function canManageNote(int $id): bool
    {
        $roleSlug = Session::get('user_role_slug');
        if (in_array($roleSlug, ['superadmin', 'admin'], true)) {
            return true;
        }
        $rows = Database::fetchAll(
            "SELECT id FROM calendar_notes WHERE id = ? AND created_by = ?",
            [$id, Session::get('user_id')]
        );
        return !empty($rows);
    }
}
