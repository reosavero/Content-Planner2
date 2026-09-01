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
        $where = "tt.status IN ('Approved', 'Scheduled')";
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

        $this->view('scheduler/index', [
            'title' => 'Scheduler & Auto Posting',
            'tasks' => $tasks,
            'platforms' => $platforms,
            'hashtags' => $hashtags,
            'allTags' => $allTags,
            'search' => $search,
            'breadcrumbs' => [['label' => 'Scheduler', 'url' => '#']]
        ]);
    }

    public function uploadTask(): void
    {
        if (!in_array(Session::get('user_role_slug'), ['admin', 'superadmin'])) {
            $this->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            return;
        }

        $taskId = (int)($_POST['task_id'] ?? 0);
        $selectedPlatforms = $_POST['platforms'] ?? []; 
        $taskDate = $_POST['task_date'] ?? date('Y-m-d');
        $scheduledTime = $_POST['scheduled_time'] ?? '09:00';
        $caption = $_POST['caption'] ?? '';
        $schedulerActive = isset($_POST['scheduler_active']) ? (int)$_POST['scheduler_active'] : 0;

        if (empty($selectedPlatforms) || !is_array($selectedPlatforms)) {
            $this->json(['success' => false, 'message' => 'Pilih minimal satu media sosial untuk diupload!'], 422);
            return;
        }

        $task = Database::fetch("SELECT * FROM timeline_tasks WHERE id = ?", [$taskId]);
        if (!$task) {
            $this->json(['success' => false, 'message' => 'Task tidak ditemukan'], 404);
            return;
        }

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

        $userId = Session::get('user_id');
        $uploadResults = [];

        foreach ($platformRows as $p) {
            
            $pkId = Database::fetchColumn("SELECT id FROM planning_konten WHERE judul = ? LIMIT 1", [$task['title']]);
            $mediaPath = $task['video_attachment'] ?: ($task['result_file_attachment'] ?: $task['file_attachment']);
            
            if (!$pkId) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $task['title']))) . '-' . time() . '-' . rand(100, 999);
                Database::execute(
                    "INSERT INTO planning_konten (judul, slug, caption, media_path, media_type, platform_id, status, scheduled_at, created_by, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, 'video', ?, 'posting', NOW(), ?, NOW(), NOW())",
                    [
                        $task['title'],
                        $slug,
                        $caption ?: ($task['catatan'] ?? ''),
                        $mediaPath,
                        $p['id'],
                        $userId ?: 1
                    ]
                );
                $pkId = Database::lastInsertId();
            } else {
                
                Database::execute(
                    "UPDATE planning_konten SET media_path = IFNULL(media_path, ?), caption = IFNULL(caption, ?) WHERE id = ?",
                    [$mediaPath, $caption, $pkId]
                );
            }

            
            $startTime = microtime(true);
            $res = $this->triggerLivePublish((int)$pkId, $p['slug']);
            $durationMs = (int)((microtime(true) - $startTime) * 1000);

            $accId = Database::fetchColumn("SELECT id FROM platform_akun WHERE platform_id = ? AND is_connected = 1 LIMIT 1", [$p['id']]) ?: 1;
            
            if ($res['success']) {
                $postId = $res['post_id'] ?? null;
                $postUrl = $res['post_url'] ?? null;

                Database::execute(
                    "UPDATE planning_konten SET status = 'success', posted_at = NOW(), post_id_platform = ?, post_url = ? WHERE id = ?",
                    [$postId, $postUrl, $pkId]
                );

                Database::execute(
                    "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, http_code, duration_ms, ip_address, performed_by, created_at) 
                     VALUES (?, ?, 'posting', 'success', 200, ?, ?, ?, NOW())",
                    [$pkId, $accId, $durationMs, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', $userId ?: 1]
                );

                $uploadResults[] = "✓ {$p['name']}: Berhasil (Post ID: {$postId})";
            } else {
                $errorMsg = $res['error'] ?? 'Gagal publikasi ke API platform';
                $httpCode = $res['http_code'] ?? 500;

                Database::execute(
                    "UPDATE planning_konten SET status = 'failed' WHERE id = ?",
                    [$pkId]
                );

                Database::execute(
                    "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, error_message, http_code, duration_ms, ip_address, performed_by, created_at) 
                     VALUES (?, ?, 'posting', 'failed', ?, ?, ?, ?, ?, NOW())",
                    [$pkId, $accId, $errorMsg, $httpCode, $durationMs, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', $userId ?: 1]
                );

                $uploadResults[] = "✕ {$p['name']}: Gagal ({$errorMsg})";
            }

            Database::execute(
                "INSERT INTO activity_logs (user_id, role_id, action, module, table_name, record_id, description, ip_address)
                 VALUES (?, ?, 'upload_social', 'scheduler', 'timeline_tasks', ?, ?, ?)",
                [$userId, Session::get('user_role_id'), $taskId, "Proses upload \"{$task['title']}\" ke {$p['name']}", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
            );
        }

        
        Database::execute(
            "UPDATE timeline_tasks SET status = 'Publish', target_platforms = ?, task_date = ?, scheduled_time = ?, updated_at = NOW() WHERE id = ?",
            [$platformIdsStr, $taskDate, $scheduledTime, $taskId]
        );

        $this->json([
            'success' => true,
            'message' => "Postingan \"{$task['title']}\" otomatis terupload ke " . $platformStr . "!"
        ]);
    }

    public function updateSchedule(): void
    {
        if (!in_array(Session::get('user_role_slug'), ['admin', 'superadmin'])) {
            $this->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            return;
        }

        $taskId = (int)($_POST['task_id'] ?? 0);
        $selectedPlatforms = $_POST['platforms'] ?? [];
        $taskDate = $_POST['task_date'] ?? date('Y-m-d');
        $scheduledTime = $_POST['scheduled_time'] ?? '09:00';
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

    


    private function triggerLivePublish(int $planningId, string $platformSlug): array
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

        $fullCaption = trim(($planning['judul'] ?? '') . "\n\n" . ($planning['caption'] ?? ''));

        try {
            if ($platformSlug === 'youtube') {
                require_once SERVICES_PATH . 'YouTubeService.php';
                $service = new YouTubeService();
                $targetPath = ($localPath && file_exists($localPath)) ? $localPath : $mediaUrl;
                return $service->publishVideo($account['account_id'], $accessToken, $targetPath, $planning['judul'], $fullCaption);
            } elseif ($platformSlug === 'facebook') {
                require_once SERVICES_PATH . 'FacebookService.php';
                $service = new FacebookService();
                if (!empty($mediaUrl) && (str_contains($mediaUrl, '.mp4') || str_contains($mediaUrl, '.mov'))) {
                    return $service->publishVideo($account['account_id'], $accessToken, $mediaUrl, $fullCaption, $fullCaption);
                } elseif (!empty($mediaUrl)) {
                    return $service->publishImage($account['account_id'], $accessToken, $mediaUrl, $fullCaption);
                }
                return $service->publishText($account['account_id'], $accessToken, $fullCaption);
            } elseif ($platformSlug === 'instagram') {
                require_once SERVICES_PATH . 'InstagramService.php';
                $service = new InstagramService();
                if (empty($mediaUrl)) {
                    return ['success' => false, 'error' => 'Instagram membutuhkan media (gambar atau video) untuk posting.'];
                }
                $mediaType = $planning['media_type'] ?? 'image';
                if (in_array($mediaType, ['image', 'carousel'])) {
                    return $service->publishImage($account['account_id'], $accessToken, $mediaUrl, $fullCaption);
                } elseif (in_array($mediaType, ['video', 'reels', 'shorts', 'story'])) {
                    return $service->publishVideo($account['account_id'], $accessToken, $mediaUrl, $fullCaption);
                }
                return ['success' => false, 'error' => 'Tipe media tidak didukung oleh Instagram: ' . $mediaType];
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
}
