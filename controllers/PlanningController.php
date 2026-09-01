<?php





class PlanningController extends Controller
{
    


    public function index(): void
    {
        if (Session::get('user_role_slug') === 'magang') {
            Session::setFlash('error', 'Akses halaman planning ditolak.');
            $this->redirect('/dashboard');
            return;
        }

        $viewMode = $_GET['view'] ?? 'timeline';
        if (!in_array($viewMode, ['timeline', 'table'])) {
            $viewMode = 'timeline';
        }

        $userId = Session::get('user_id');
        $userRole = Session::get('user_role_slug');

        
        
        
        $month = $_GET['bulan'] ?? date('m');
        $year = $_GET['tahun'] ?? date('Y');

        
        
        $minYear = null;
        $minMonth = null;
        if ($userRole !== 'magang') {
            $minKey = date('Ym', strtotime('-2 months', strtotime(date('Y-m-01'))));
            $minKeyNum = (int)substr($minKey, 0, 4) * 12 + (int)substr($minKey, 4, 2);
            $currentKey = (int)$year * 12 + (int)$month;
            if ($currentKey < $minKeyNum) {
                $year = (int)substr($minKey, 0, 4);
                $month = (int)substr($minKey, 4, 2);
            }
            $minYear = (int)substr($minKey, 0, 4);
            $minMonth = (int)substr($minKey, 4, 2);
        }
        $filterStatus = $_GET['timeline_status'] ?? '';
        $filterType = $_GET['jenis'] ?? '';
        $filterPic = $_GET['pic'] ?? '';

        $timelineWhere = "WHERE YEAR(task_date) = ? AND MONTH(task_date) = ?";
        $timelineParams = [$year, $month];

        if ($userRole === 'magang') {
            $timelineWhere .= " AND (assignee_id = ? OR assigned_to = ? OR LOWER(TRIM(pic_name)) = LOWER(TRIM(?)))";
            $timelineParams[] = $userId;
            $timelineParams[] = $userId;
            $timelineParams[] = Session::get('user_name');
        }

        if (!empty($filterStatus)) {
            $timelineWhere .= " AND status = ?";
            $timelineParams[] = $filterStatus;
        }
        if (!empty($filterType)) {
            $timelineWhere .= " AND content_type = ?";
            $timelineParams[] = $filterType;
        }
        if (!empty($filterPic)) {
            $timelineWhere .= " AND pic_name = ?";
            $timelineParams[] = $filterPic;
        }

        $timelineTasks = Database::fetchAll(
            "SELECT * FROM timeline_tasks {$timelineWhere} ORDER BY task_date ASC, sort_order ASC, id ASC",
            $timelineParams
        );

        $daysInMonth = (int)date('t', strtotime(sprintf('%04d-%02d-01', (int)$year, (int)$month)));
        $dayNamesIndo = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];

        $grouped = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', (int)$year, (int)$month, $d);
            $dayEnglish = date('l', strtotime($dateStr));
            $dayNameIndo = $dayNamesIndo[$dayEnglish] ?? $dayEnglish;

            $grouped[$dateStr] = [
                'date' => $dateStr,
                'day_name' => $dayNameIndo,
                'tasks' => []
            ];
        }

        foreach ($timelineTasks as $task) {
            $date = $task['task_date'];
            if (!isset($grouped[$date])) {
                $grouped[$date] = [
                    'date' => $date,
                    'day_name' => !empty($task['day_name']) ? $task['day_name'] : ($dayNamesIndo[date('l', strtotime($date))] ?? date('l', strtotime($date))),
                    'tasks' => []
                ];
            } else if (!empty($task['day_name'])) {
                $grouped[$date]['day_name'] = $task['day_name'];
            }
            $grouped[$date]['tasks'][] = $task;
        }

        
        
        
        if ((int)$month === (int)date('m') && (int)$year === (int)date('Y')) {
            $todayDay = (int)date('d');
            uksort($grouped, function ($a, $b) use ($todayDay) {
                $dayA = (int)substr($a, 8, 2);
                $dayB = (int)substr($b, 8, 2);
                $rankA = ($dayA >= $todayDay) ? $dayA - $todayDay : $dayA - $todayDay + 100;
                $rankB = ($dayB >= $todayDay) ? $dayB - $todayDay : $dayB - $todayDay + 100;
                return $rankA <=> $rankB;
            });
        }

        $calendar = Database::fetchAll(
            "SELECT * FROM timeline_calendar WHERE YEAR(calendar_date) = ? AND MONTH(calendar_date) = ? ORDER BY calendar_date ASC",
            [$year, $month]
        );
        $calendarByDate = [];
        foreach ($calendar as $c) {
            $calendarByDate[$c['calendar_date']] = $c;
        }

        $contentTypes = Database::fetchAll(
            "SELECT DISTINCT content_type FROM timeline_tasks WHERE YEAR(task_date) = ? AND MONTH(task_date) = ? AND content_type IS NOT NULL AND content_type != '' ORDER BY content_type",
            [$year, $month]
        );
        $picList = Database::fetchAll(
            "SELECT DISTINCT pic_name FROM timeline_tasks WHERE YEAR(task_date) = ? AND MONTH(task_date) = ? AND pic_name IS NOT NULL AND pic_name != '' ORDER BY pic_name",
            [$year, $month]
        );

        $usersList = Database::fetchAll(
            "SELECT u.id, u.name, u.email, u.role_id
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.is_active = 1 AND u.deleted_at IS NULL AND r.slug = 'magang'
             ORDER BY u.name"
        );

        
        $platformsList = Database::fetchAll(
            "SELECT id, name, icon, color FROM platform_sosmed WHERE is_active = 1 ORDER BY name"
        );

        $stats = Database::fetch(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Selesai' OR status = 'Publish' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = 'Pending Approval' THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'Scheduled' THEN 1 ELSE 0 END) as schedule,
                SUM(CASE WHEN status = 'Belum' OR status = 'Assigned' OR status = 'In Progress' OR status = 'Proses' OR status = 'Need Revision' OR status = 'Belum Selesai' THEN 1 ELSE 0 END) as belum
             FROM timeline_tasks {$timelineWhere}",
            $timelineParams
        );

        $excelList = [
            'Berita JHI (01 JULI)', 'Berita JHI (02 JULI)', 'Berita JHI (06 JULI)', 'EDIT FLYER ASSALAM',
            'Berita JHI (07 JULI)', 'Berita JHI (08 JULI)', 'Berita JHI (09 JULI)', 'Berita JHI (10-13 JULI)',
            'Berita JHI (13 JULI)', 'Berita JHI (14 JULI)', 'Berita JHI (15 JULI)', 'Berita JHI (16 JULI)',
            'Berita JHI (20 JULI)', 'Berita JHI (21 JULI)', 'Berita JHI (22 JULI)', 'TAKE VIDEO Konten Receh',
            'EDIT REELS Konten Receh', 'Berita JHI (23 JULI)', 'Berita JHI (24-26 JULI)', 'Berita JHI (27 JULI)',
            'Berita JHI (28 JULI)', 'Berita JHI (29 JULI)', 'Berita JHI (30 JULI)',
        ];
        $dbTitles = Database::fetchAll("SELECT DISTINCT title FROM timeline_tasks WHERE title IS NOT NULL AND title != ''");
        foreach ($dbTitles as $rowTitle) {
            $tVal = trim($rowTitle['title']);
            if (!empty($tVal) && !in_array($tVal, $excelList)) {
                $excelList[] = $tVal;
            }
        }

        $excelCatatanList = [
            'UBAH UKURAN VIDEO JADI PORTRAIT, BUAT 4 VIDEO, TIAP VIDEO NAMAI SESUAI JUDUL BERITA',
            "- Spanyol vs Austria : 01.00 WIB\n- Portuga vs Kroasia : 05.0 WIB\n- Swiss vs Aljazair : 09.00 WIB",
            "-Australia vs Mesir : 00.00 WIB\n- Argentina vs Cape Verde : 04.00 WIB\n- Kolombia vs Ghana : 07.30 WIB",
            'CARI REFERENSI DI GOOGLE',
            'VIDEO ADA DI SERVER MEDIABARU 2 > FOLDER CTY! KUTUKAN 5 TIMNAS BELANDA',
            'UBAH UKURAN VIDEO JADI PORTRAIT, BUAT 4 VIDEO (PER TGL 1 VIDEO) TIAP VIDEO NAMAI SESUAI JUDUL BERITA',
            'DARI ARTIKEL YG ADA RANGKUM MATERI PENTING JADI BENTUK SKRIP VIDEO YG MUDAH DIPAHAMI',
            'VOKSPOP?',
            'EDIT SEKREATIF MUNGKIN',
            'DARI ARTIKEL YG ADA RANGKUM MATERI PENTING JADI BENTUK SLIDE FEED IG',
            'CARI KONTEN PROMO KEKINIAN YG LEBIH KREATIF YAA',
            "BUAT KONTEN FEED ADAPTASI DARI REFERENSI YG DIKIRIM, TAPI BUAT JUDUL + ISI BARU YG LEBIH MENARIK. BUAT 1-4 SLIDE FEED (KALO BISA ADA VIDEO 'CLEAN' NYA YA)",
            'https://www.instagram.com/reel/DaeyAyltRkz/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA==  (CONTOH VIDEO DARI TVRI JATIM)',
            'EDIT DURASI MAKS 2 MENIT, MASUKKAN',
            'BIKIN BEDA YA, ATM AJAA',
            'BUAT BEDA, BISA 2 ORANG YG JADI TALENT',
        ];

        
        
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search = Security::sanitize($_GET['search'] ?? '');
        $tableStatus = $_GET['status'] ?? '';
        $platformId = $_GET['platform_id'] ?? '';
        $programId = $_GET['program_id'] ?? '';
        $kategoriId = $_GET['kategori_id'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $sort = $_GET['sort'] ?? 'created_at';
        $direction = $_GET['direction'] ?? 'DESC';

        $tableWhere = "pk.deleted_at IS NULL";
        $tableParams = [];

        if ($userRole === 'magang') {
            $tableWhere .= " AND pk.created_by = ?";
            $tableParams[] = $userId;
        }

        if (!empty($search)) {
            $tableWhere .= " AND (pk.judul LIKE ? OR pk.caption LIKE ?)";
            $searchTerm = "%{$search}%";
            $tableParams[] = $searchTerm;
            $tableParams[] = $searchTerm;
        }
        if (!empty($tableStatus)) {
            $tableWhere .= " AND pk.status = ?";
            $tableParams[] = $tableStatus;
        }
        if (!empty($platformId)) {
            $tableWhere .= " AND pk.platform_id = ?";
            $tableParams[] = $platformId;
        }
        if (!empty($programId)) {
            $tableWhere .= " AND pk.program_id = ?";
            $tableParams[] = $programId;
        }
        if (!empty($kategoriId)) {
            $tableWhere .= " AND pk.kategori_id = ?";
            $tableParams[] = $kategoriId;
        }
        if (!empty($priority)) {
            $tableWhere .= " AND pk.priority = ?";
            $tableParams[] = $priority;
        }

        $allowedSort = ['created_at', 'tanggal_posting', 'judul', 'status', 'priority'];
        if (!in_array($sort, $allowedSort)) $sort = 'created_at';
        if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'DESC';

        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM planning_konten pk WHERE {$tableWhere}",
            $tableParams
        );

        $totalPages = max(1, ceil($count / $perPage));
        $offset = ($page - 1) * $perPage;

        $tableData = Database::fetchAll(
            "SELECT pk.*, 
                    p.name as platform_name, p.icon as platform_icon, p.color as platform_color,
                    pt.name as program_name,
                    kk.name as kategori_name, kk.color as kategori_color,
                    creator.name as creator_name,
                    editor.name as editor_name,
                    pic.name as pic_name
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             LEFT JOIN program_tv pt ON pt.id = pk.program_id
             LEFT JOIN kategori_konten kk ON kk.id = pk.kategori_id
             LEFT JOIN users creator ON creator.id = pk.created_by
             LEFT JOIN users editor ON editor.id = pk.editor_id
             LEFT JOIN users pic ON pic.id = pk.pic_id
             WHERE {$tableWhere}
             ORDER BY pk.{$sort} {$direction}
             LIMIT {$perPage} OFFSET {$offset}",
            $tableParams
        );

        $platforms = Database::fetchAll("SELECT id, name, icon, color FROM platform_sosmed WHERE is_active = 1 ORDER BY sort_order");
        $programs = Database::fetchAll("SELECT id, name FROM program_tv WHERE is_active = 1 ORDER BY sort_order");
        $kategoris = Database::fetchAll("SELECT id, name, color FROM kategori_konten WHERE is_active = 1 ORDER BY sort_order");

        $pageActions = [];

        
        $isMagang = ($userRole === 'magang');

        $this->view('planning/index', [
            'title' => $isMagang ? '' : 'Planning Konten',
            'topbarTitle' => $isMagang ? 'Planning & Tugas' : null,
            'viewMode' => $viewMode,
            'userRole' => $userRole,
            
            'grouped' => $grouped,
            'calendarByDate' => $calendarByDate,
            'contentTypes' => $contentTypes,
            'picList' => $picList,
            'usersList' => $usersList,
            'platformsList' => $platformsList,
            'excelKontenList' => $excelList,
            'excelCatatanList' => $excelCatatanList,
            'stats' => $stats,
            'month' => $month,
            'year' => $year,
            'minYear' => $minYear,
            'minMonth' => $minMonth,
            'timelineStatus' => $filterStatus,
            'filterType' => $filterType,
            'filterPic' => $filterPic,
            
            'data' => $tableData,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total' => $count,
                'per_page' => $perPage,
            ],
            'filters' => [
                'search' => $search,
                'status' => $tableStatus,
                'platform_id' => $platformId,
                'program_id' => $programId,
                'kategori_id' => $kategoriId,
                'priority' => $priority,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'platforms' => $platforms,
            'programs' => $programs,
            'kategoris' => $kategoris,
            'breadcrumbs' => $isMagang ? [] : [
                ['label' => 'Planning Konten', 'url' => '#']
            ],
            'pageActions' => $pageActions,
        ]);
    }

    


    public function create(): void
    {
        $platforms = Database::fetchAll("SELECT id, name, icon, color FROM platform_sosmed WHERE is_active = 1 ORDER BY sort_order");
        $programs = Database::fetchAll("SELECT id, name FROM program_tv WHERE is_active = 1 ORDER BY sort_order");
        $kategoris = Database::fetchAll("SELECT id, name, color FROM kategori_konten WHERE is_active = 1 ORDER BY sort_order");
        $jenisKonten = Database::fetchAll("SELECT id, name FROM jenis_konten WHERE is_active = 1 ORDER BY id");
        $tags = Database::fetchAll("SELECT id, name, color FROM tags WHERE is_active = 1 ORDER BY name");
        $hashtags = Database::fetchAll("SELECT id, name FROM hashtag WHERE is_active = 1 ORDER BY name");
        $templates = Database::fetchAll("SELECT id, name FROM template_caption WHERE is_active = 1 ORDER BY name");
        $lokasis = Database::fetchAll("SELECT id, name FROM lokasi_shooting WHERE is_active = 1 ORDER BY name");
        $talents = Database::fetchAll("SELECT id, name, photo FROM talent WHERE is_active = 1 ORDER BY name");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name");

        $this->view('planning/create', [
            'title' => 'Buat Planning Konten',
            'platforms' => $platforms,
            'programs' => $programs,
            'kategoris' => $kategoris,
            'jenisKonten' => $jenisKonten,
            'tags' => $tags,
            'hashtags' => $hashtags,
            'templates' => $templates,
            'lokasis' => $lokasis,
            'talents' => $talents,
            'users' => $users,
            'breadcrumbs' => [
                ['label' => 'Konten', 'url' => '/planning'],
                ['label' => 'Buat Planning', 'url' => '#'],
            ],
        ]);
    }

    


    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/planning');
        }

        
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid. Silakan coba lagi.');
        }

        // Get platform_ids from checkboxes
        $selectedPlatformIds = $_POST['platform_ids'] ?? [];
        if (empty($selectedPlatformIds) || !is_array($selectedPlatformIds)) {
            $this->redirectBackWith('error', 'Pilih minimal satu platform sosmed!');
        }
        $primaryPlatformId = $selectedPlatformIds[0]; // First selected as primary

        $data = $this->validate($_POST, [
            'judul' => 'required|min:3|max:255',
            'platform_id' => 'required|numeric', // kept for backward compat (hidden field synced via JS)
            'kategori_id' => 'required|numeric',
            'program_id' => 'numeric',
            'jenis_konten_id' => 'numeric',
            'tanggal_posting' => 'required|date',
            'jam_posting' => 'required',
            'caption' => 'max:5000',
            'hashtag_text' => 'max:1000',
            'media_type' => 'in:image,video,reels,shorts,story,carousel,text',
            'priority' => 'in:low,medium,high,urgent',
            'pic_id' => 'numeric',
            'lokasi_shooting_id' => 'numeric',
            'talent_id' => 'numeric',
            'deadline' => 'date',
            'catatan' => 'max:2000',
        ]);

        
        $data['slug'] = generateSlug($data['judul']) . '-' . time();
        $data['status'] = 'draft';
        $data['created_by'] = Session::get('user_id');
        $data['scheduled_at'] = $data['tanggal_posting'] . ' ' . $data['jam_posting'] . ':00';
        unset($data['tanggal_posting'], $data['jam_posting']);

        $programId = !empty($_POST['program_id']) ? $_POST['program_id'] : null;
        $jenisKontenId = !empty($_POST['jenis_konten_id']) ? $_POST['jenis_konten_id'] : null;
        $picId = !empty($_POST['pic_id']) ? $_POST['pic_id'] : null;
        $lokasiId = !empty($_POST['lokasi_shooting_id']) ? $_POST['lokasi_shooting_id'] : null;
        $talentId = !empty($_POST['talent_id']) ? $_POST['talent_id'] : null;
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

        try {
            $planningId = Database::insert(
                "INSERT INTO planning_konten 
                (judul, slug, program_id, kategori_id, jenis_konten_id, platform_id, caption, hashtag_text, 
                 media_type, tanggal_posting, jam_posting, scheduled_at, status, priority, pic_id, 
                 lokasi_shooting_id, talent_id, deadline, catatan, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $data['judul'], $data['slug'], $programId, $data['kategori_id'], 
                    $jenisKontenId, $primaryPlatformId, $data['caption'] ?? '', 
                    $data['hashtag_text'] ?? '', $data['media_type'] ?? 'image',
                    $data['tanggal_posting'] ?? $data['scheduled_at'], $data['jam_posting'] ?? '00:00',
                    $data['scheduled_at'], $data['status'], $data['priority'] ?? 'medium',
                    $picId, $lokasiId, $talentId,
                    $deadline, $data['catatan'] ?? '', Session::get('user_id')
                ]
            );

            // Save to planning_konten_platform (pivot table)
            foreach ($selectedPlatformIds as $pid) {
                $pData = Database::fetch("SELECT slug FROM platform_sosmed WHERE id = ?", [$pid]);
                if (!$pData) continue;
                
                $acc = Database::fetch(
                    "SELECT id FROM platform_akun WHERE platform_id = ? AND is_connected = 1 AND is_active = 1 AND token_status = 'active'",
                    [$pid]
                );
                $status = $acc ? 'pending' : 'failed';
                $errorMsg = $acc ? null : 'Akun ' . ucfirst($pData['slug']) . ' belum terhubung';
                
                Database::execute(
                    "INSERT INTO planning_konten_platform (planning_konten_id, platform, status, error_message, created_at, updated_at)
                     VALUES (?, ?, ?, ?, NOW(), NOW())",
                    [$planningId, $pData['slug'], $status, $errorMsg]
                );
            }

            
            if (!empty($_POST['tags'])) {
                foreach ((array)$_POST['tags'] as $tagId) {
                    Database::execute(
                        "INSERT INTO planning_tags (planning_id, tag_id) VALUES (?, ?)",
                        [$planningId, $tagId]
                    );
                }
            }

            
            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address) 
                 VALUES (?, ?, 'create', 'planning', 'planning_konten', ?, 'Membuat planning konten: {$data['judul']}', ?)",
                [Session::get('user_id'), Session::get('user_role_id'), $planningId, $_SERVER['REMOTE_ADDR']]
            );

            $this->redirectWith('/planning', 'success', 'Planning konten berhasil dibuat.');
        } catch (Exception $e) {
            $this->redirectBackWith('error', 'Gagal membuat planning: ' . $e->getMessage());
        }
    }

    


    public function show(string $id): void
    {
        $planning = Database::fetch(
            "SELECT pk.*, 
                    p.name as platform_name, p.icon as platform_icon, p.color as platform_color,
                    pt.name as program_name,
                    kk.name as kategori_name, kk.color as kategori_color,
                    jk.name as jenis_konten_name,
                    creator.name as creator_name, creator.avatar as creator_avatar,
                    editor.name as editor_name,
                    approver.name as approver_name,
                    pic.name as pic_name,
                    ls.name as lokasi_name,
                    t.name as talent_name, t.photo as talent_photo
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             LEFT JOIN program_tv pt ON pt.id = pk.program_id
             LEFT JOIN kategori_konten kk ON kk.id = pk.kategori_id
             LEFT JOIN jenis_konten jk ON jk.id = pk.jenis_konten_id
             LEFT JOIN users creator ON creator.id = pk.created_by
             LEFT JOIN users editor ON editor.id = pk.editor_id
             LEFT JOIN users approver ON approver.id = pk.approved_by
             LEFT JOIN users pic ON pic.id = pk.pic_id
             LEFT JOIN lokasi_shooting ls ON ls.id = pk.lokasi_shooting_id
             LEFT JOIN talent t ON t.id = pk.talent_id
             WHERE pk.id = ? AND pk.deleted_at IS NULL",
            [$id]
        );

        if (!$planning) {
            $this->redirectWith('/planning', 'error', 'Planning tidak ditemukan.');
        }

        $tags = Database::fetchAll(
            "SELECT t.* FROM tags t JOIN planning_tags pt ON pt.tag_id = t.id WHERE pt.planning_id = ?",
            [$id]
        );

        $media = Database::fetchAll(
            "SELECT * FROM planning_media WHERE planning_id = ? ORDER BY sort_order",
            [$id]
        );

        $logs = Database::fetchAll(
            "SELECT pl.*, u.name as user_name 
             FROM posting_logs pl 
             LEFT JOIN users u ON u.id = pl.performed_by 
             WHERE pl.planning_id = ? 
             ORDER BY pl.created_at DESC LIMIT 10",
            [$id]
        );

        $this->view('planning/show', [
            'title' => 'Detail Planning: ' . truncateText($planning['judul'], 50),
            'planning' => $planning,
            'tags' => $tags,
            'media' => $media,
            'logs' => $logs,
            'breadcrumbs' => [
                ['label' => 'Konten', 'url' => '/planning'],
                ['label' => truncateText($planning['judul'], 30), 'url' => '#'],
            ],
        ]);
    }

    


    public function edit(string $id): void
    {
        $planning = Database::fetch("SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$planning) {
            $this->redirectWith('/planning', 'error', 'Planning tidak ditemukan.');
        }

        
        $roleSlug = Session::get('user_role_slug');
        $userId = Session::get('user_id');
        if ($roleSlug === 'magang' && $planning['created_by'] != $userId) {
            $this->redirectWith('/planning', 'error', 'Anda tidak memiliki izin untuk mengedit planning ini.');
        }

        $platforms = Database::fetchAll("SELECT id, name, icon, color FROM platform_sosmed WHERE is_active = 1 ORDER BY sort_order");
        $programs = Database::fetchAll("SELECT id, name FROM program_tv WHERE is_active = 1 ORDER BY sort_order");
        $kategoris = Database::fetchAll("SELECT id, name, color FROM kategori_konten WHERE is_active = 1 ORDER BY sort_order");
        $jenisKonten = Database::fetchAll("SELECT id, name FROM jenis_konten WHERE is_active = 1 ORDER BY id");
        $tags = Database::fetchAll("SELECT id, name, color FROM tags WHERE is_active = 1 ORDER BY name");
        $hashtags = Database::fetchAll("SELECT id, name FROM hashtag WHERE is_active = 1 ORDER BY name");
        $templates = Database::fetchAll("SELECT id, name FROM template_caption WHERE is_active = 1 ORDER BY name");
        $lokasis = Database::fetchAll("SELECT id, name FROM lokasi_shooting WHERE is_active = 1 ORDER BY name");
        $talents = Database::fetchAll("SELECT id, name, photo FROM talent WHERE is_active = 1 ORDER BY name");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name");
        $selectedTags = Database::fetchAll("SELECT tag_id FROM planning_tags WHERE planning_id = ?", [$id]);
        $selectedTagIds = array_column($selectedTags, 'tag_id');

        $this->view('planning/edit', [
            'title' => 'Edit Planning: ' . truncateText($planning['judul'], 50),
            'planning' => $planning,
            'platforms' => $platforms,
            'programs' => $programs,
            'kategoris' => $kategoris,
            'jenisKonten' => $jenisKonten,
            'tags' => $tags,
            'hashtags' => $hashtags,
            'templates' => $templates,
            'lokasis' => $lokasis,
            'talents' => $talents,
            'users' => $users,
            'selectedTagIds' => $selectedTagIds,
            'breadcrumbs' => [
                ['label' => 'Konten', 'url' => '/planning'],
                ['label' => 'Edit', 'url' => '#'],
            ],
        ]);
    }

    


    public function update(string $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/planning');
        }

        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->redirectBackWith('error', 'Token CSRF tidak valid.');
        }

        $planning = Database::fetch("SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$planning) {
            $this->redirectWith('/planning', 'error', 'Planning tidak ditemukan.');
        }

        // Get platform_ids from checkboxes
        $selectedPlatformIds = $_POST['platform_ids'] ?? [];
        if (empty($selectedPlatformIds) || !is_array($selectedPlatformIds)) {
            $this->redirectBackWith('error', 'Pilih minimal satu platform sosmed!');
        }
        $primaryPlatformId = $selectedPlatformIds[0]; // First selected as primary

        $data = $this->validate($_POST, [
            'judul' => 'required|min:3|max:255',
            'platform_id' => 'required|numeric', // kept for backward compat
            'kategori_id' => 'required|numeric',
            'caption' => 'max:5000',
            'hashtag_text' => 'max:1000',
            'media_type' => 'in:image,video,reels,shorts,story,carousel,text',
            'priority' => 'in:low,medium,high,urgent',
            'catatan' => 'max:2000',
        ]);

        $programId = !empty($_POST['program_id']) ? $_POST['program_id'] : null;
        $jenisKontenId = !empty($_POST['jenis_konten_id']) ? $_POST['jenis_konten_id'] : null;
        $picId = !empty($_POST['pic_id']) ? $_POST['pic_id'] : null;
        $lokasiId = !empty($_POST['lokasi_shooting_id']) ? $_POST['lokasi_shooting_id'] : null;
        $talentId = !empty($_POST['talent_id']) ? $_POST['talent_id'] : null;
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $tanggalPosting = !empty($_POST['tanggal_posting']) ? $_POST['tanggal_posting'] : $planning['tanggal_posting'];
        $jamPosting = !empty($_POST['jam_posting']) ? $_POST['jam_posting'] : $planning['jam_posting'];

        $scheduledAt = $tanggalPosting . ' ' . $jamPosting . ':00';

        Database::execute(
            "UPDATE planning_konten SET 
                judul = ?, program_id = ?, kategori_id = ?, jenis_konten_id = ?, platform_id = ?,
                caption = ?, hashtag_text = ?, media_type = ?, tanggal_posting = ?, jam_posting = ?,
                scheduled_at = ?, priority = ?, pic_id = ?, lokasi_shooting_id = ?, talent_id = ?,
                deadline = ?, catatan = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['judul'], $programId, $data['kategori_id'], 
                $jenisKontenId, $primaryPlatformId,
                $data['caption'] ?? '', $data['hashtag_text'] ?? '', $data['media_type'] ?? 'image',
                $tanggalPosting,
                $jamPosting,
                $scheduledAt,
                $data['priority'] ?? 'medium', $picId,
                $lokasiId, $talentId,
                $deadline, $data['catatan'] ?? '', $id
            ]
        );

        // Update planning_konten_platform (pivot table)
        // First, get existing platforms
        $existingPlatforms = Database::fetchAll(
            "SELECT platform FROM planning_konten_platform WHERE planning_konten_id = ?",
            [$id]
        );
        $existingSlugs = array_column($existingPlatforms, 'platform');

        foreach ($selectedPlatformIds as $pid) {
            $pData = Database::fetch("SELECT slug FROM platform_sosmed WHERE id = ?", [$pid]);
            if (!$pData) continue;
            
            $platformSlug = $pData['slug'];
            $acc = Database::fetch(
                "SELECT id FROM platform_akun WHERE platform_id = ? AND is_connected = 1 AND is_active = 1 AND token_status = 'active'",
                [$pid]
            );
            $status = $acc ? 'pending' : 'failed';
            $errorMsg = $acc ? null : 'Akun ' . ucfirst($platformSlug) . ' belum terhubung';
            
            if (in_array($platformSlug, $existingSlugs)) {
                // Update existing - keep status if already success, otherwise reset to pending/failed
                $existing = Database::fetch(
                    "SELECT status FROM planning_konten_platform WHERE planning_konten_id = ? AND platform = ?",
                    [$id, $platformSlug]
                );
                if ($existing && $existing['status'] === 'success') {
                    // Don't overwrite success status
                    continue;
                }
                Database::execute(
                    "UPDATE planning_konten_platform SET status = ?, error_message = ?, updated_at = NOW()
                     WHERE planning_konten_id = ? AND platform = ?",
                    [$status, $errorMsg, $id, $platformSlug]
                );
            } else {
                // Insert new
                Database::execute(
                    "INSERT INTO planning_konten_platform (planning_konten_id, platform, status, error_message, created_at, updated_at)
                     VALUES (?, ?, ?, ?, NOW(), NOW())",
                    [$id, $platformSlug, $status, $errorMsg]
                );
            }
        }

        // Remove platforms that were unchecked (only if not already published)
        foreach ($existingSlugs as $slug) {
            if (!in_array($slug, array_column(
                Database::fetchAll("SELECT slug FROM platform_sosmed WHERE id IN (" . implode(',', array_fill(0, count($selectedPlatformIds), '?')) . ")", $selectedPlatformIds), 'slug'
            ))) {
                $existing = Database::fetch(
                    "SELECT status FROM planning_konten_platform WHERE planning_konten_id = ? AND platform = ?",
                    [$id, $slug]
                );
                if ($existing && $existing['status'] !== 'success') {
                    Database::execute(
                        "DELETE FROM planning_konten_platform WHERE planning_konten_id = ? AND platform = ?",
                        [$id, $slug]
                    );
                }
            }
        }

        
        Database::execute("DELETE FROM planning_tags WHERE planning_id = ?", [$id]);
        if (!empty($_POST['tags'])) {
            foreach ((array)$_POST['tags'] as $tagId) {
                Database::execute("INSERT INTO planning_tags (planning_id, tag_id) VALUES (?, ?)", [$id, $tagId]);
            }
        }

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address) 
             VALUES (?, ?, 'update', 'planning', 'planning_konten', ?, 'Mengupdate planning konten', ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $id, $_SERVER['REMOTE_ADDR']]
        );

        $this->redirectWith('/planning/' . $id, 'success', 'Planning berhasil diupdate.');
    }

    


    public function delete(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }

        $planning = Database::fetch("SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Planning tidak ditemukan']);
        }

        Database::execute("UPDATE planning_konten SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?", [$id]);

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address) 
             VALUES (?, ?, 'delete', 'planning', 'planning_konten', ?, 'Menghapus planning konten: {$planning['judul']}', ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $id, $_SERVER['REMOTE_ADDR']]
        );

        $this->json(['success' => true, 'message' => 'Planning berhasil dihapus']);
    }

    


    public function updateStatus(string $id): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $status = $_POST['status'] ?? '';
        $validStatuses = ['draft', 'review', 'approved', 'revision', 'scheduled', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Status tidak valid']);
        }

        $planning = Database::fetch("SELECT * FROM planning_konten WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Planning tidak ditemukan']);
        }

        Database::execute(
            "UPDATE planning_konten SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $id]
        );

        
        if ($status === 'approved') {
            Database::execute(
                "UPDATE planning_konten SET approved_by = ?, approved_at = NOW() WHERE id = ?",
                [Session::get('user_id'), $id]
            );
        }

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address) 
             VALUES (?, ?, 'status_update', 'planning', 'planning_konten', ?, 'Mengupdate status ke: {$status}', ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $id, $_SERVER['REMOTE_ADDR']]
        );

        $this->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'data' => ['status' => $status]
        ]);
    }

    


    public function bulkAction(): void
    {
        $action = $_POST['bulk_action'] ?? '';
        $ids = $_POST['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->json(['success' => false, 'message' => 'Pilih minimal satu item']);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'delete':
                Database::execute(
                    "UPDATE planning_konten SET deleted_at = NOW(), updated_at = NOW() WHERE id IN ({$placeholders})",
                    $ids
                );
                $message = count($ids) . ' planning berhasil dihapus';
                break;
            case 'submit-review':
                Database::execute(
                    "UPDATE planning_konten SET status = 'review', updated_at = NOW() WHERE id IN ({$placeholders}) AND status = 'draft'",
                    $ids
                );
                $message = count($ids) . ' planning dikirim ke review';
                break;
            default:
                $this->json(['success' => false, 'message' => 'Aksi tidak dikenali']);
        }

        $logAction = $action === 'delete' ? 'delete' : 'submit_review';
        $logDescription = $action === 'delete'
            ? count($ids) . ' planning dihapus (bulk action)'
            : count($ids) . ' planning dikirim ke review (bulk action)';

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, ?, 'planning', NULL, NULL, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $logAction, $logDescription, $_SERVER['REMOTE_ADDR']]
        );

        $this->json(['success' => true, 'message' => $message]);
    }
}
