<?php









class SchedulerController extends Controller
{
    


    public function index(): void
    {
        if (!in_array(Session::get('user_role_slug'), ['admin', 'superadmin'])) {
            $this->redirectWith('/dashboard', 'error', 'Akses scheduler hanya untuk Admin.');
            return;
        }

        $search = Security::sanitize($_GET['search'] ?? '');
        $where = "tt.status = 'Approved'";
        $params = [];

        if (!empty($search)) {
            $where .= " AND tt.title LIKE ?";
            $params[] = "%{$search}%";
        }

        $tasks = Database::fetchAll(
            "SELECT tt.*, assignee.name AS assignee_name, creator.name AS creator_name,
                    p.name AS platform_name, p.icon AS platform_icon, p.color AS platform_color
             FROM timeline_tasks tt
             LEFT JOIN users assignee ON assignee.id = tt.assignee_id
             LEFT JOIN users creator ON creator.id = tt.creator_id
             LEFT JOIN platform_sosmed p ON p.id = tt.platform_id
             WHERE {$where}
             ORDER BY tt.task_date ASC, tt.updated_at DESC",
            $params
        );

        $platforms = Database::fetchAll(
            "SELECT id, name, slug, icon, color FROM platform_sosmed WHERE is_active = 1 ORDER BY id"
        );

        $hashtags = Database::fetchAll(
            "SELECT id, name FROM hashtag WHERE is_active = 1 ORDER BY usage_count DESC, name ASC"
        );

        $allTags = Database::fetchAll(
            "SELECT id, name, color FROM tags WHERE is_active = 1 ORDER BY name ASC"
        );

        if ($this->isAjax()) {
            ob_start();
            $this->viewPartial('scheduler/_cards', ['tasks' => $tasks]);
            $html = ob_get_clean();
            $this->success(['html' => $html]);
        }

        $connectedPlatforms = Database::fetchAll(
            "SELECT p.id, p.slug, pa.is_connected, pa.token_status 
             FROM platform_akun pa 
             JOIN platform_sosmed p ON p.id = pa.platform_id 
             WHERE pa.is_connected = 1 AND pa.token_status = 'active'"
        );

        $this->view('scheduler/index', [
            'title' => 'Scheduler & Auto Posting',
            'tasks' => $tasks,
            'platforms' => $platforms,
            'connectedPlatforms' => $connectedPlatforms,
            'hashtags' => $hashtags,
            'allTags' => $allTags,
            'search' => $search,
            'breadcrumbs' => [['label' => 'Scheduler', 'url' => '#']]
        ]);
    }

    public function uploadTask(): void
    {
        try {
            if (!in_array(Session::get('user_role_slug'), ['admin', 'superadmin'])) {
                $this->json(['success' => false, 'message' => 'Akses ditolak'], 403);
                return;
            }

            $taskId = (int)($_POST['task_id'] ?? 0);
            $selectedPlatforms = $_POST['platforms'] ?? []; 
            $caption = $_POST['caption'] ?? '';
            $schedulerActive = isset($_POST['scheduler_active']) ? (int)$_POST['scheduler_active'] : 0;
            $igTargetTypes = $_POST['ig_target_types'] ?? ['feed'];
            if (!is_array($igTargetTypes) || empty($igTargetTypes)) {
                $igTargetTypes = ['feed'];
            }

            if (empty($selectedPlatforms) || !is_array($selectedPlatforms)) {
                $this->json(['success' => false, 'message' => 'Pilih minimal satu media sosial untuk diupload!'], 422);
                return;
            }

            $task = Database::fetch("SELECT * FROM timeline_tasks WHERE id = ?", [$taskId]);
            if (!$task) {
                $this->json(['success' => false, 'message' => 'Task tidak ditemukan'], 404);
                return;
            }

            $rawDate = trim($_POST['task_date'] ?? '');
            $taskDate = !empty($rawDate) ? $rawDate : ($task['task_date'] ?: date('Y-m-d'));

            $rawTime = trim($_POST['scheduled_time'] ?? '');
            $scheduledTime = !empty($rawTime) ? $rawTime : ($task['scheduled_time'] ?: '09:00');

            $newTitle = Security::sanitize($_POST['title'] ?? '');
            if (!empty($newTitle) && $newTitle !== $task['title']) {
                Database::execute("UPDATE timeline_tasks SET title = ? WHERE id = ?", [$newTitle, $taskId]);
                $task['title'] = $newTitle;
            }

            $placeholders = implode(',', array_fill(0, count($selectedPlatforms), '?'));
            $platformRows = Database::fetchAll("SELECT id, name, slug FROM platform_sosmed WHERE id IN ({$placeholders})", $selectedPlatforms);
            $platformNames = array_column($platformRows, 'name');
            $platformStr = implode(', ', $platformNames);
            $platformIdsStr = implode(',', $selectedPlatforms);

            $userId = Session::get('user_id') ?: 1;
            $roleId = Session::get('user_role_id') ?: 1;
            $uploadResults = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($platformRows as $p) {
                $acc = Database::fetch(
                    "SELECT * FROM platform_akun WHERE platform_id = ? AND is_connected = 1 AND is_active = 1 AND token_status = 'active' LIMIT 1",
                    [$p['id']]
                );

                if (!$acc) {
                    $uploadResults[] = "✕ {$p['name']}: Gagal (Akun belum terhubung di Integrasi Sosmed / Sesi Kedaluwarsa)";
                    $failCount++;
                    continue;
                }

                $mediaPath = $task['video_attachment'] ?: ($task['result_file_attachment'] ?: ($task['result_link'] ?: $task['file_attachment']));
                $actualType = $this->detectActualMediaType($mediaPath ?? '', $task['content_type'] ?? null);
                $scheduledAt = $taskDate . ' ' . (strlen($scheduledTime) === 5 ? "{$scheduledTime}:00" : $scheduledTime);

                $itemsToProcess = [];
                if ($schedulerActive === 1 && $p['slug'] === 'instagram') {
                    $wantFeed = in_array('feed', $igTargetTypes);
                    $wantStory = in_array('story', $igTargetTypes);
                    if (!$wantFeed && !$wantStory) $wantFeed = true;

                    if ($wantFeed) {
                        $itemsToProcess[] = ['title' => $task['title'], 'media_type' => $actualType, 'label' => 'Instagram Feed/Reels'];
                    }
                    if ($wantStory) {
                        $itemsToProcess[] = ['title' => $task['title'] . ' (Story)', 'media_type' => 'story', 'label' => 'Instagram Story'];
                    }
                } else {
                    $detectedType = ($p['slug'] === 'instagram' && in_array('story', $igTargetTypes) && !in_array('feed', $igTargetTypes)) ? 'story' : $actualType;
                    $itemsToProcess[] = ['title' => $task['title'], 'media_type' => $detectedType, 'label' => $p['name']];
                }

                foreach ($itemsToProcess as $item) {
                    $itemTitle = $item['title'];
                    $detectedType = $item['media_type'];

                    $pkId = Database::fetchColumn("SELECT id FROM planning_konten WHERE judul = ? AND platform_id = ? LIMIT 1", [$itemTitle, $p['id']]);

                    if (!$pkId) {
                        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $itemTitle))) . '-' . time() . '-' . rand(100, 999);
                        $pkId = Database::insert(
                            "INSERT INTO planning_konten (judul, slug, caption, media_path, media_type, platform_id, platform_akun_id, status, scheduled_at, tanggal_posting, jam_posting, created_by, created_at, updated_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                            [
                                $itemTitle,
                                $slug,
                                $caption,
                                $mediaPath,
                                $detectedType,
                                $p['id'],
                                $acc['id'],
                                $schedulerActive === 1 ? 'scheduled' : 'draft',
                                $scheduledAt,
                                $taskDate,
                                $scheduledTime,
                                $userId
                            ]
                        );
                    } else {
                        Database::execute(
                            "UPDATE planning_konten SET media_path = IF(COALESCE(?, '') != '', ?, media_path), media_type = ?, caption = ?, platform_id = ?, platform_akun_id = ?, scheduled_at = ?, tanggal_posting = ?, jam_posting = ?, updated_at = NOW() WHERE id = ?",
                            [$mediaPath, $mediaPath, $detectedType, $caption, $p['id'], $acc['id'], $scheduledAt, $taskDate, $scheduledTime, $pkId]
                        );
                    }

                    if ($schedulerActive === 1) {
                        Database::execute("UPDATE planning_konten SET status = 'scheduled' WHERE id = ?", [$pkId]);
                        try {
                            Database::execute("DELETE FROM scheduler_queue WHERE planning_id = ? AND platform_akun_id = ?", [$pkId, $acc['id']]);
                            Database::execute(
                                "INSERT INTO scheduler_queue (planning_id, platform_akun_id, status, scheduled_at, created_at) VALUES (?, ?, 'queued', ?, NOW())",
                                [$pkId, $acc['id'], $scheduledAt]
                            );
                        } catch (\Throwable $e) {}

                        $successCount++;
                        $uploadResults[] = "✓ {$item['label']}: Dijadwalkan ({$taskDate} {$scheduledTime})";
                        continue;
                    }

                    $startTime = microtime(true);
                    $res = $this->triggerLivePublish((int)$pkId, $p['slug'], $igTargetTypes);
                    $durationMs = (int)((microtime(true) - $startTime) * 1000);
                    $accId = $acc['id'];
                    
                    if (!empty($res['success'])) {
                        $postId = $res['post_id'] ?? null;
                        $postUrl = $res['post_url'] ?? null;
                        $successCount++;

                        Database::execute(
                            "UPDATE planning_konten SET status = 'success', posted_at = NOW(), post_id_platform = ?, post_url = ? WHERE id = ?",
                            [$postId, $postUrl, $pkId]
                        );

                        Database::execute(
                            "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, http_code, duration_ms, ip_address, performed_by, created_at) 
                             VALUES (?, ?, 'posting', 'success', 200, ?, ?, ?, NOW())",
                            [$pkId, $accId, $durationMs, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', $userId]
                        );

                        $uploadResults[] = "✓ {$p['name']}: Berhasil (Post ID: {$postId})";
                    } else {
                        $failCount++;
                        $errorMsg = $res['error'] ?? 'Gagal publikasi ke API platform';
                        $httpCode = $res['http_code'] ?? 500;

                        Database::execute(
                            "UPDATE planning_konten SET status = 'failed' WHERE id = ?",
                            [$pkId]
                        );

                        Database::execute(
                            "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, error_message, http_code, duration_ms, ip_address, performed_by, created_at) 
                             VALUES (?, ?, 'posting', 'failed', ?, ?, ?, ?, ?, NOW())",
                            [$pkId, $accId, $errorMsg, $httpCode, $durationMs, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', $userId]
                        );

                        $uploadResults[] = "✕ {$p['name']}: Gagal ({$errorMsg})";
                    }
                }

                Database::execute(
                    "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                     VALUES (?, ?, 'upload_social', 'scheduler', 'timeline_tasks', ?, ?, ?)",
                    [$userId, $roleId, $taskId, "Proses upload \"{$task['title']}\" ke {$p['name']}", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
                );
            }

            $summaryStr = implode(' | ', $uploadResults);

            if ($schedulerActive === 1) {
                if ($successCount > 0) {
                    Database::execute(
                        "UPDATE timeline_tasks SET status = 'Scheduled', target_platforms = ?, task_date = ?, scheduled_time = ?, scheduler_active = 1, updated_at = NOW() WHERE id = ?",
                        [$platformIdsStr, $taskDate, $scheduledTime, $taskId]
                    );

                    $this->json([
                        'success' => true,
                        'message' => ($failCount > 0 ? "Jadwal Parsial: " : "Berhasil Dijadwalkan: ") . $summaryStr
                    ]);
                } else {
                    $this->json([
                        'success' => false,
                        'message' => "Gagal Menjadwalkan: " . $summaryStr
                    ], 400);
                }
                return;
            }

            if ($successCount > 0) {
                Database::execute(
                    "UPDATE timeline_tasks SET status = 'Selesai', target_platforms = ?, task_date = ?, scheduled_time = ?, updated_at = NOW() WHERE id = ?",
                    [$platformIdsStr, $taskDate, $scheduledTime, $taskId]
                );

                $this->json([
                    'success' => true,
                    'message' => ($failCount > 0 ? "Upload Parsial: " : "Berhasil Upload: ") . $summaryStr
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => "Gagal Upload: " . $summaryStr
                ], 400);
            }
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal memproses upload: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSchedule(): void
    {
        if (!in_array(Session::get('user_role_slug'), ['admin', 'superadmin'])) {
            $this->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            return;
        }

        $task = Database::fetch("SELECT * FROM timeline_tasks WHERE id = ?", [$taskId]);
        if (!$task) {
            $this->json(['success' => false, 'message' => 'Task tidak ditemukan'], 404);
            return;
        }

        $rawDate = trim($_POST['task_date'] ?? '');
        $taskDate = !empty($rawDate) ? $rawDate : ($task['task_date'] ?: date('Y-m-d'));

        $rawTime = trim($_POST['scheduled_time'] ?? '');
        $scheduledTime = !empty($rawTime) ? $rawTime : ($task['scheduled_time'] ?: '09:00');

        $caption = $_POST['caption'] ?? '';
        $schedulerActive = isset($_POST['scheduler_active']) ? (int)$_POST['scheduler_active'] : 0;

        $newTitle = Security::sanitize($_POST['title'] ?? '');
        if (!empty($newTitle)) {
            Database::execute("UPDATE timeline_tasks SET title = ? WHERE id = ?", [$newTitle, $taskId]);
        }

        $platformIdsStr = is_array($selectedPlatforms) ? implode(',', $selectedPlatforms) : '';

        Database::execute(
            "UPDATE timeline_tasks SET target_platforms = ?, task_date = ?, scheduled_time = ?, status = 'Scheduled', updated_at = NOW() WHERE id = ?",
            [$platformIdsStr, $taskDate, $scheduledTime, $taskId]
        );

        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'update_schedule', 'scheduler', 'timeline_tasks', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), $taskId, 'Jadwal & media task #' . $taskId . ' diperbarui', $_SERVER['REMOTE_ADDR']]
        );

        $this->json(['success' => true, 'message' => 'Jadwal & media posting berhasil disimpan. Status task: Schedule.']);
    }

    


    public function autoPostStatus(): void
    {
        $recentLogs = Database::fetchAll(
            "SELECT apl.*, pk.judul, p.name as platform_name
             FROM auto_post_logs apl
             JOIN planning_konten pk ON pk.id = apl.planning_id
             JOIN platform_sosmed p ON p.slug = apl.platform
             ORDER BY apl.created_at DESC
             LIMIT 30"
        );

        $this->json([
            'success' => true,
            'data' => $recentLogs,
        ]);
    }

    


    public function processQueue(): void
    {
        
        $enabled = Database::fetchColumn(
            "SELECT value FROM settings WHERE `key` = 'auto_post_enabled'"
        );
        if ($enabled !== '1') {
            $this->json(['success' => false, 'message' => 'Auto posting disabled']);
            return;
        }

        $batchSize = (int) Database::fetchColumn(
            "SELECT value FROM settings WHERE `key` = 'scheduler_batch_size'"
        ) ?: 5;

        $interval = (int) Database::fetchColumn(
            "SELECT value FROM settings WHERE `key` = 'auto_post_interval_seconds'"
        ) ?: 30;

        
        // Ambil dari planning_konten_platform yang pending + scheduled_at <= NOW
        $queue = Database::fetchAll(
            "SELECT pkp.*, pk.judul, pk.caption, pk.hashtag_text, pk.media_path, pk.media_type,
                    pa.access_token, pa.account_id, pa.account_username, pa.account_name,
                    p.slug as platform_slug, p.name as platform_name
             FROM planning_konten_platform pkp
             JOIN planning_konten pk ON pk.id = pkp.planning_konten_id
             JOIN platform_sosmed p ON p.slug = pkp.platform
             JOIN platform_akun pa ON pa.platform_id = p.id
                 AND pa.is_connected = 1 AND pa.is_active = 1 AND pa.token_status = 'active'
             WHERE pkp.status = 'pending'
             AND pk.status IN ('approved', 'scheduled')
             AND pk.scheduled_at <= NOW()
             ORDER BY pk.scheduled_at ASC
             LIMIT {$batchSize}"
        );

        if (empty($queue)) {
            $this->json(['success' => true, 'message' => 'Tidak ada antrian', 'processed' => 0]);
            return;
        }

        require_once HELPERS_PATH . 'SocialMediaAPI.php';

        $processed = 0;
        $success = 0;
        $failed = 0;

        foreach ($queue as $item) {
            $processed++;
            $pkId = $item['planning_konten_id'];
            $platform = $item['platform_slug'];
            
            // Update status ke publishing
            $this->updatePlatformStatus($pkId, $platform, 'publishing', null, null);
            Database::execute(
                "UPDATE planning_konten SET status = 'posting' WHERE id = ?",
                [$pkId]
            );

            $planningData = [
                'judul' => $item['judul'],
                'caption' => $item['caption'],
                'media_path' => $item['media_path'],
                'media_type' => $item['media_type'],
                'hashtag_text' => $item['hashtag_text'] ?? '',
            ];

            $accountData = [
                'platform_slug' => $platform,
                'access_token' => $item['access_token'],
                'account_id' => $item['account_id'],
                'account_username' => $item['account_username'],
                'account_name' => $item['account_name'],
            ];

            $startTime = microtime(true);
            $result = SocialMediaAPI::post($planningData, $accountData);
            $duration = (int)((microtime(true) - $startTime) * 1000);

            $accId = Database::fetchColumn(
                "SELECT id FROM platform_akun WHERE platform_id = (SELECT id FROM platform_sosmed WHERE slug = ?) AND is_connected = 1 AND is_active = 1 AND token_status = 'active'",
                [$platform]
            );

            // Log ke posting_logs
            Database::execute(
                "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, error_message, http_code, ip_address, performed_by, duration_ms, created_at)
                 VALUES (?, ?, 'posting', ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $pkId,
                    $accId,
                    $result['success'] ? 'success' : 'failed',
                    $result['error'] ?? null,
                    $result['success'] ? 200 : ($result['http_code'] ?? 400),
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    Session::get('user_id') ?: 0,
                    $duration
                ]
            );

            if ($result['success']) {
                $postId = $result['post_id'] ?? null;
                $postUrl = $result['post_url'] ?? null;
                
                $this->updatePlatformStatus($pkId, $platform, 'success', $postId, null);
                
                Database::execute(
                    "UPDATE planning_konten SET 
                        status = 'success', 
                        posted_at = NOW(),
                        post_id_platform = ?,
                        post_url = ?,
                        updated_at = NOW()
                     WHERE id = ?",
                    [$postId, $postUrl, $pkId]
                );

                Database::execute(
                    "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                     VALUES (?, ?, 'auto_post', 'scheduler', 'planning_konten', ?, ?, ?)",
                    [
                        Session::get('user_id') ?: 0,
                        Session::get('user_role_id') ?: 0,
                        $pkId,
                        "Auto posting sukses ke {$item['platform_name']}: {$item['judul']}",
                        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                    ]
                );

                $success++;

            } else {
                $retryCount = ($item['retry_count'] ?? 0) + 1;
                $maxRetry = (int) Database::fetchColumn(
                    "SELECT value FROM settings WHERE `key` = 'auto_post_max_retry'"
                ) ?: 3;

                if ($retryCount >= $maxRetry) {
                    $this->updatePlatformStatus($pkId, $platform, 'failed', null, $result['error'], $retryCount);
                    Database::execute(
                        "UPDATE planning_konten SET status = 'failed', error_message = ?, retry_count = ?, updated_at = NOW() WHERE id = ?",
                        [$result['error'], $retryCount, $pkId]
                    );
                } else {
                    $retryDelay = (int) Database::fetchColumn(
                        "SELECT value FROM settings WHERE `key` = 'scheduler_retry_delay'"
                    ) ?: 5;
                    
                    $nextRun = date('Y-m-d H:i:s', strtotime("+{$retryDelay} minutes"));
                    Database::execute(
                        "UPDATE planning_konten_platform SET status = 'pending', retry_count = ?, error_message = ?, updated_at = NOW() WHERE planning_konten_id = ? AND platform = ?",
                        [$retryCount, $result['error'], $pkId, $platform]
                    );
                    Database::execute(
                        "UPDATE planning_konten SET error_message = ?, retry_count = ?, updated_at = NOW() WHERE id = ?",
                        [$result['error'], $retryCount, $pkId]
                    );
                }

                $failed++;
            }

            $this->sendPostNotification($item, $result);

            if (count($queue) > 1 && $processed < count($queue)) {
                sleep($interval);
            }
        }

        $this->json([
            'success' => true,
            'message' => "Diproses: {$processed}, Sukses: {$success}, Gagal: {$failed}",
            'processed' => $processed,
            'success_count' => $success,
            'failed_count' => $failed
        ]);
    }

    private function updatePlatformStatus(string $planningId, string $platform, string $status, ?string $postId, ?string $errorMsg, int $retryCount = 0): void
    {
        $existing = Database::fetch(
            "SELECT id FROM planning_konten_platform WHERE planning_konten_id = ? AND platform = ?",
            [$planningId, $platform]
        );

        if ($existing) {
            $sql = "UPDATE planning_konten_platform SET status = ?, platform_post_id = ?, error_message = ?, retry_count = ?, updated_at = NOW()";
            $params = [$status, $postId, $errorMsg, $retryCount];

            if ($status === 'success') {
                $sql .= ", published_at = NOW()";
            }
            $sql .= " WHERE id = ?";
            $params[] = $existing['id'];

            Database::execute($sql, $params);
        }
    }


    public function retryAutoPost(string $id): void
    {
        Database::execute(
            "UPDATE scheduler_queue SET status = 'queued', retry_count = 0, error_message = NULL, scheduled_at = NOW() WHERE id = ? AND status IN ('failed', 'retry')",
            [$id]
        );
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'retry', 'scheduler', 'scheduler_queue', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), (int)$id, 'Retry posting antrian #' . $id, $_SERVER['REMOTE_ADDR']]
        );
        $this->json(['success' => true, 'message' => 'Posting akan dicoba ulang']);
    }

    


    public function cancel(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }
        Database::execute("UPDATE scheduler_queue SET status = 'cancelled' WHERE id = ?", [$id]);
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'cancel', 'scheduler', 'scheduler_queue', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), (int)$id, 'Jadwal antrian #' . $id . ' dibatalkan', $_SERVER['REMOTE_ADDR']]
        );
        $this->json(['success' => true, 'message' => 'Jadwal dibatalkan.']);
    }

    


    public function runNow(): void
    {
        $id = $_POST['id'] ?? 0;
        Database::execute(
            "UPDATE scheduler_queue SET scheduled_at = NOW(), priority = 999 WHERE id = ?",
            [$id]
        );
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
             VALUES (?, ?, 'run_now', 'scheduler', 'scheduler_queue', ?, ?, ?)",
            [Session::get('user_id'), Session::get('user_role_id'), (int)$id, 'Posting antrian #' . $id . ' dijalankan sekarang', $_SERVER['REMOTE_ADDR']]
        );
        $this->redirectWith('/scheduler', 'success', 'Posting akan segera diproses.');
    }

    /**
     * Membatalkan upload dan menghapus task dari scheduler.
     */
    public function cancelTask(): void
    {
        try {
            if (!in_array(Session::get('user_role_slug'), ['admin', 'superadmin'])) {
                $this->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
                return;
            }

            $taskId = (int)($_POST['task_id'] ?? 0);
            if (!$taskId) {
                $this->json(['success' => false, 'message' => 'Task ID tidak valid.'], 400);
                return;
            }

            $task = Database::fetch("SELECT * FROM timeline_tasks WHERE id = ?", [$taskId]);
            if (!$task) {
                $this->json(['success' => false, 'message' => 'Task tidak ditemukan.'], 404);
                return;
            }

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
                    $driveService = new GoogleDriveService(CONFIG_PATH . 'google-drive-oauth.json', defined('GOOGLE_DRIVE_FOLDER_ID') ? GOOGLE_DRIVE_FOLDER_ID : '');
                    foreach ($driveIds as $fileId) {
                        $driveService->deleteFile($fileId);
                    }
                } catch (\Throwable $e) {
                    error_log('GoogleDrive hapus pada cancelTask scheduler gagal: ' . $e->getMessage());
                }
            }

            try {
                $pkRows = Database::fetchAll("SELECT id FROM planning_konten WHERE judul = ? OR judul LIKE ?", [$task['title'], $task['title'] . ' (%']);
                foreach ($pkRows as $pkItem) {
                    $pkId = $pkItem['id'];
                    Database::execute("DELETE FROM planning_konten_platform WHERE planning_konten_id = ?", [$pkId]);
                    Database::execute("DELETE FROM scheduler_queue WHERE planning_id = ?", [$pkId]);
                    Database::execute("DELETE FROM planning_konten WHERE id = ?", [$pkId]);
                }
            } catch (\Throwable $e) {}

            try {
                Database::execute(
                    "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                     VALUES (?, ?, 'cancel_delete', 'scheduler', 'timeline_tasks', ?, ?, ?)",
                    [Session::get('user_id') ?: 1, Session::get('user_role_id') ?: 1, $taskId, 'Upload dibatalkan & task "' . trim($task['title']) . '" dihapus', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
                );
            } catch (\Throwable $e) {}

            Database::execute("UPDATE timeline_tasks SET status = 'Approved', scheduler_active = 0, updated_at = NOW() WHERE id = ?", [$taskId]);

            $this->json(['success' => true, 'message' => 'Jadwal upload berhasil dibatalkan. Task tetap tersimpan di halaman Planning.']);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Gagal membatalkan task: ' . $e->getMessage()], 500);
        }
    }

    


    private function sendPostNotification(array $item, array $result): void
    {
        require_once HELPERS_PATH . 'Notification.php';
        
        $title = $result['success'] 
            ? "✅ Posting sukses ke {$item['platform_name']}"
            : "❌ Gagal posting ke {$item['platform_name']}";
        
        $message = $result['success']
            ? "Konten \"{$item['judul']}\" berhasil diposting ke {$item['platform_name']}"
            : "Konten \"{$item['judul']}\" gagal diposting ke {$item['platform_name']}: {$result['error']}";

        
        $creator = Database::fetchColumn(
            "SELECT created_by FROM planning_konten WHERE id = ?",
            [$item['planning_id']]
        );
        if ($creator) {
            Notification::create(
                $creator,
                $title,
                $message,
                $result['success'] ? 'success' : 'error',
                BASE_URL . '/planning/' . $item['planning_id'],
                $result['success'] ? 'bi-check-circle' : 'bi-x-circle',
                'auto_post',
                'Auto Posting'
            );
        }

        
        $admins = Database::fetchAll(
            "SELECT id FROM users WHERE role_id IN (SELECT id FROM roles WHERE slug IN ('superadmin','admin')) AND is_active = 1"
        );
        $adminIds = array_column($admins, 'id');
        
        Notification::createBulk(
            $adminIds,
            $title,
            $message,
            $result['success'] ? 'success' : 'error',
            BASE_URL . '/posting',
            $result['success'] ? 'bi-check-circle' : 'bi-x-circle',
            'auto_post',
            'Auto Posting'
        );
    }

    


    private function triggerLivePublish(int $planningId, string $platformSlug, array $igTargetTypes = ['feed']): array
    {
        $planning = Database::fetch("SELECT * FROM planning_konten WHERE id = ?", [$planningId]);
        if (!$planning) return ['success' => false, 'error' => 'Planning tidak ditemukan'];

        $platform = Database::fetch("SELECT id FROM platform_sosmed WHERE slug = ?", [$platformSlug]);
        if (!$platform) return ['success' => false, 'error' => 'Platform tidak valid'];

        $account = Database::fetch(
            "SELECT * FROM platform_akun WHERE platform_id = ? AND is_connected = 1 AND is_active = 1 LIMIT 1",
            [$platform['id']]
        );

        if (!$account) return ['success' => false, 'error' => 'Akun ' . ucfirst($platformSlug) . ' belum terhubung di Integrasi Sosmed'];

        $accessToken = Security::decrypt($account['access_token']);

        
        $mediaUrl = null;
        $localPath = null;
        if (!empty($planning['media_path'])) {
            $rawPath = $planning['media_path'];
            if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
                
                if (str_contains($rawPath, 'drive.google.com')) {
                    require_once SERVICES_PATH . 'GoogleDriveService.php';
                    $fileId = GoogleDriveService::extractFileId($rawPath);
                    $mediaUrl = $fileId ? ('https://drive.google.com/uc?export=download&id=' . $fileId) : $rawPath;
                } else {
                    $mediaUrl = $rawPath;
                }
                $localPath = null;
            } elseif (preg_match('/uploads\/(.+)$/i', $rawPath, $m)) {
                $relPath = $m[1];
                $localPath = UPLOADS_PATH . $relPath;
                $mediaUrl = BASE_URL . '/uploads/' . $relPath;
            } else {
                $relPath = ltrim($rawPath, '/');
                $localPath = UPLOADS_PATH . $relPath;
                $mediaUrl = BASE_URL . '/uploads/' . $relPath;
            }
        }

        $fullCaption = trim($planning['caption'] ?? '');

        $rawMediaPath = $planning['media_path'] ?? '';
        $mediaType = $this->detectActualMediaType($rawMediaPath, $planning['media_type'] ?? null);

        try {
            if ($platformSlug === 'youtube') {
                require_once SERVICES_PATH . 'YouTubeService.php';
                $service = new YouTubeService();
                $targetPath = ($localPath && file_exists($localPath)) ? $localPath : $mediaUrl;
                return $service->publishVideo($account['account_id'], $accessToken, $targetPath, $planning['judul'], $fullCaption);
            } elseif ($platformSlug === 'facebook') {
                require_once SERVICES_PATH . 'FacebookService.php';
                $service = new FacebookService();
                if ($mediaType === 'video') {
                    return $service->publishVideo($account['account_id'], $accessToken, $mediaUrl, $fullCaption, $fullCaption, $localPath);
                } elseif (!empty($mediaUrl)) {
                    return $service->publishImage($account['account_id'], $accessToken, $mediaUrl, $fullCaption, $localPath);
                }
                return $service->publishText($account['account_id'], $accessToken, $fullCaption);
            } elseif ($platformSlug === 'instagram') {
                require_once SERVICES_PATH . 'InstagramService.php';
                $service = new InstagramService();
                if (empty($mediaUrl)) {
                    return ['success' => false, 'error' => 'Instagram membutuhkan media (gambar atau video) untuk posting.'];
                }

                $wantFeed = in_array('feed', $igTargetTypes);
                $wantStory = in_array('story', $igTargetTypes);
                if (!$wantFeed && !$wantStory) {
                    $wantFeed = true;
                }

                if ($wantStory && !$wantFeed) {
                    return $service->publishStory($account['account_id'], $accessToken, $mediaUrl);
                }

                $errors = [];
                $lastSuccessRes = null;

                if ($wantFeed) {
                    if (in_array($mediaType, ['video', 'reels', 'shorts']) || preg_match('/\.(mp4|mov|webm|avi|mkv)(\?|$)/i', $mediaUrl)) {
                        $resFeed = $service->publishVideo($account['account_id'], $accessToken, $mediaUrl, $fullCaption);
                    } else {
                        $resFeed = $service->publishImage($account['account_id'], $accessToken, $mediaUrl, $fullCaption);
                    }
                    if ($resFeed['success']) {
                        $lastSuccessRes = $resFeed;
                    } else {
                        $errors[] = 'Feed/Reels: ' . ($resFeed['error'] ?? 'Gagal');
                    }
                }

                if ($wantStory) {
                    $resStory = $service->publishStory($account['account_id'], $accessToken, $mediaUrl);
                    if ($resStory['success']) {
                        if (!$lastSuccessRes) $lastSuccessRes = $resStory;
                    } else {
                        $errors[] = 'Story: ' . ($resStory['error'] ?? 'Gagal');
                    }
                }

                if ($lastSuccessRes) {
                    return $lastSuccessRes;
                }

                return ['success' => false, 'error' => implode('; ', $errors)];
            } elseif ($platformSlug === 'tiktok') {
                require_once SERVICES_PATH . 'TikTokService.php';
                $service = new TikTokService();
                $targetPath = ($localPath && file_exists($localPath)) ? $localPath : $mediaUrl;
                return $service->publishVideo($account['account_id'], $accessToken, $targetPath, $fullCaption);
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }

        return ['success' => false, 'error' => 'Platform tidak didukung'];
    }

    private function detectActualMediaType(string $mediaPath, ?string $fallbackLabel = null): string
    {
        if (empty($mediaPath)) {
            return 'image';
        }

        // 1. Extension check
        if (preg_match('/\.(mp4|mov|webm|avi|mkv|flv|3gp|m4v|ogv|wmv)(\?|$)/i', $mediaPath)) {
            return 'video';
        }
        if (preg_match('/\.(jpg|jpeg|png|webp|gif|bmp)(\?|$)/i', $mediaPath)) {
            return 'image';
        }

        // 2. Local file MIME check
        if (!str_starts_with($mediaPath, 'http://') && !str_starts_with($mediaPath, 'https://')) {
            $localFile = UPLOADS_PATH . ltrim(preg_replace('#^/?uploads/#i', '', $mediaPath), '/');
            if (file_exists($localFile)) {
                $mime = @mime_content_type($localFile) ?: '';
                if (str_starts_with($mime, 'video/')) return 'video';
                if (str_starts_with($mime, 'image/')) return 'image';
            }
        }

        // 3. Remote URL MIME check (Google Drive / HTTP link)
        if (str_starts_with($mediaPath, 'http://') || str_starts_with($mediaPath, 'https://')) {
            $checkUrl = $mediaPath;
            if (str_contains($mediaPath, 'drive.google.com')) {
                require_once SERVICES_PATH . 'GoogleDriveService.php';
                $fileId = GoogleDriveService::extractFileId($mediaPath);
                if ($fileId) {
                    $checkUrl = 'https://lh3.googleusercontent.com/d/' . $fileId;
                }
            }

            $ch = curl_init($checkUrl);
            curl_setopt_array($ch, [
                CURLOPT_NOBODY => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            curl_exec($ch);
            $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if (!empty($mime)) {
                if (str_starts_with($mime, 'video/')) return 'video';
                if (str_starts_with($mime, 'image/')) return 'image';
            }
        }

        // 4. Fallback to label only if file check could not determine type
        if (!empty($fallbackLabel) && preg_match('/(video|reel|reels|short|shorts|vt)/i', $fallbackLabel)) {
            return 'video';
        }

        return 'image';
    }
}
