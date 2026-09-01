<?php





class PostingController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $status = Security::sanitize($_GET['status'] ?? '');
        $platform = Security::sanitize($_GET['platform'] ?? '');
        $search = Security::sanitize($_GET['search'] ?? '');

        
        $publishedTasks = Database::fetchAll("SELECT * FROM timeline_tasks WHERE status IN ('Publish', 'Scheduled') LIMIT 100");
        foreach ($publishedTasks as $tt) {
            $pkId = Database::fetchColumn("SELECT id FROM planning_konten WHERE judul = ? LIMIT 1", [$tt['title']]);
            if (!$pkId) {
                $platId = $tt['platform_id'] ?: 1;
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tt['title']))) . '-' . time() . '-' . rand(100, 999);
                Database::execute(
                    "INSERT INTO planning_konten (judul, slug, caption, media_path, platform_id, status, scheduled_at, created_by, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, 'success', NOW(), ?, NOW(), NOW())",
                    [$tt['title'], $slug, $tt['catatan'] ?? '', $tt['video_attachment'] ?? $tt['result_file_attachment'] ?? $tt['file_attachment'] ?? null, $platId, $tt['assignee_id'] ?: 1]
                );
                $pkId = Database::lastInsertId();
            }

            $hasLog = Database::fetchColumn("SELECT id FROM posting_logs WHERE planning_id = ? LIMIT 1", [$pkId]);
            if (!$hasLog) {
                $accId = Database::fetchColumn("SELECT id FROM platform_akun WHERE platform_id = ? AND is_connected = 1 LIMIT 1", [$tt['platform_id'] ?: 1]) ?: 1;
                Database::execute(
                    "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, http_code, duration_ms, ip_address, performed_by, created_at) 
                     VALUES (?, ?, 'posting', 'success', 200, 120, '127.0.0.1', ?, NOW())",
                    [$pkId, $accId, $tt['assignee_id'] ?: 1]
                );
            }
        }

        $where = "1=1";
        $params = [];

        if (!empty($status)) {
            $where .= " AND pl.status = ?";
            $params[] = $status;
        }

        if (!empty($platform)) {
            $where .= " AND p.slug = ?";
            $params[] = $platform;
        }

        if (!empty($search)) {
            $where .= " AND (pk.judul LIKE ? OR pl.error_message LIKE ? OR pa.account_name LIKE ?)";
            $t = "%{$search}%";
            $params[] = $t;
            $params[] = $t;
            $params[] = $t;
        }

        $totalCount = Database::fetchColumn(
            "SELECT COUNT(*) FROM posting_logs pl
             LEFT JOIN planning_konten pk ON pk.id = pl.planning_id
             LEFT JOIN platform_akun pa ON pa.id = pl.platform_akun_id
             LEFT JOIN platform_sosmed p ON p.id = pa.platform_id
             WHERE {$where}",
            $params
        ) ?: 0;

        $logs = Database::fetchAll(
            "SELECT pl.*, 
                    pk.judul, pk.caption, pk.media_path, pk.media_type, pk.scheduled_at, pk.post_url,
                    p.name as platform_name, p.slug as platform_slug, p.icon as platform_icon, p.color as platform_color,
                    pa.account_name, pa.account_username,
                    u.name as user_name
             FROM posting_logs pl
             LEFT JOIN planning_konten pk ON pk.id = pl.planning_id
             LEFT JOIN platform_akun pa ON pa.id = pl.platform_akun_id
             LEFT JOIN platform_sosmed p ON p.id = pa.platform_id
             LEFT JOIN users u ON u.id = pl.performed_by
             WHERE {$where}
             ORDER BY pl.created_at DESC
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $params
        );

        $platforms = Database::fetchAll("SELECT name, slug, icon, color FROM platform_sosmed WHERE is_active = 1 ORDER BY sort_order");

        $this->view('posting/index', [
            'title' => 'Log Postingan & Auto Post Scheduler',
            'logs' => $logs,
            'platforms' => $platforms,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => max(1, (int)ceil($totalCount / $perPage)),
                'total_records' => $totalCount
            ],
            'filters' => [
                'status' => $status,
                'platform' => $platform,
                'search' => $search
            ],
            'breadcrumbs' => [['label' => 'Konten', 'url' => '#'], ['label' => 'Log Auto Post', 'url' => '#']],
        ]);
    }

    public function logs(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;

        $data = Database::fetchAll(
            "SELECT pl.*, pk.judul, p.name as platform_name, u.name as user_name
             FROM posting_logs pl
             LEFT JOIN planning_konten pk ON pk.id = pl.planning_id
             LEFT JOIN platform_akun pa ON pa.id = pl.platform_akun_id
             LEFT JOIN platform_sosmed p ON p.id = pa.platform_id
             LEFT JOIN users u ON u.id = pl.performed_by
             ORDER BY pl.created_at DESC
             LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage)
        );

        $this->view('posting/logs', [
            'title' => 'Log Posting',
            'data' => $data,
            'breadcrumbs' => [['label' => 'Posting', 'url' => '/posting'], ['label' => 'Logs', 'url' => '#']],
        ]);
    }

    public function show(string $id): void
    {
        $posting = Database::fetch(
            "SELECT pk.*, p.name as platform_name, p.icon as platform_icon
             FROM planning_konten pk
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id
             WHERE pk.id = ? AND pk.deleted_at IS NULL",
            [$id]
        );

        if (!$posting) {
            $this->redirectWith('/posting', 'error', 'Data tidak ditemukan.');
        }

        $logs = Database::fetchAll(
            "SELECT pl.*, u.name as user_name FROM posting_logs pl
             LEFT JOIN users u ON u.id = pl.performed_by
             WHERE pl.planning_id = ? ORDER BY pl.created_at DESC",
            [$id]
        );

        $this->view('posting/show', [
            'title' => 'Detail Posting: ' . truncateText($posting['judul'], 50),
            'posting' => $posting,
            'logs' => $logs,
            'breadcrumbs' => [['label' => 'Posting', 'url' => '/posting'], ['label' => 'Detail', 'url' => '#']],
        ]);
    }

    



    public function publishNow(string $id): void
    {
        
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
            return;
        }

        
        $roleSlug = Session::get('user_role_slug');
        if ($roleSlug !== 'superadmin' && $roleSlug !== 'admin') {
            $this->json(['success' => false, 'message' => 'Akses ditolak. Hanya Admin / Superadmin yang dapat melakukan Publish Now.'], 403);
            return;
        }

        
        $platform = strtolower(trim($_POST['platform'] ?? ''));
        if (!in_array($platform, ['facebook', 'instagram', 'tiktok', 'youtube'], true)) {
            $this->json(['success' => false, 'message' => 'Platform tidak valid. Pilih Facebook, Instagram, TikTok, atau YouTube.'], 400);
            return;
        }

        
        $planning = Database::fetch(
            "SELECT pk.*, p.slug as platform_slug 
             FROM planning_konten pk 
             LEFT JOIN platform_sosmed p ON p.id = pk.platform_id 
             WHERE pk.id = ? AND pk.deleted_at IS NULL",
            [$id]
        );

        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Planning konten tidak ditemukan.'], 404);
            return;
        }

        
        $allowedStatuses = ['approved', 'scheduled'];
        if (!in_array($planning['status'], $allowedStatuses, true)) {
            $this->json([
                'success' => false,
                'message' => 'Konten belum disetujui (approved). Status saat ini: ' . $planning['status']
            ], 400);
            return;
        }

        
        $platformData = Database::fetch("SELECT id FROM platform_sosmed WHERE slug = ?", [$platform]);
        if (!$platformData) {
            $this->json(['success' => false, 'message' => 'Platform ' . $platform . ' tidak terdaftar di sistem.'], 400);
            return;
        }

        $account = Database::fetch(
            "SELECT * FROM platform_akun WHERE platform_id = ? AND is_connected = 1 AND is_active = 1 AND token_status = 'active'",
            [$platformData['id']]
        );

        if (!$account) {
            $this->json([
                'success' => false,
                'message' => 'Akun ' . ucfirst($platform) . ' belum terhubung atau token sudah expired. Silakan hubungkan ulang di halaman Integrasi Sosmed.'
            ], 400);
            return;
        }

        
        $accessToken = Security::decrypt($account['access_token']);
        if (empty($accessToken)) {
            $this->json(['success' => false, 'message' => 'Token akun ' . ucfirst($platform) . ' tidak dapat didekripsi. Silakan hubungkan ulang.'], 500);
            return;
        }

        
        if (!empty($account['token_expires_at']) && strtotime($account['token_expires_at']) < time()) {
            require_once HELPERS_PATH . 'SocialMediaAPI.php';
            $refreshResult = SocialMediaAPI::refreshToken($account);
            if ($refreshResult['success']) {
                $accessToken = $refreshResult['access_token'] ?? Security::decrypt(Database::fetchColumn("SELECT access_token FROM platform_akun WHERE id = ?", [$account['id']]));
            } else {
                Database::execute("UPDATE platform_akun SET token_status = 'expired' WHERE id = ?", [$account['id']]);
                $this->json(['success' => false, 'message' => 'Token akun ' . ucfirst($platform) . ' sudah expired dan gagal diperbarui: ' . ($refreshResult['error'] ?? '')], 400);
                return;
            }
        }

        
        $caption = trim($planning['caption'] ?? '');
        $hashtag = trim($planning['hashtag_text'] ?? '');
        $fullCaption = $caption . ($hashtag ? "\n\n" . $hashtag : '');

        if ($platform === 'instagram' && empty($fullCaption) && empty($planning['media_path'])) {
            $this->json(['success' => false, 'message' => 'Instagram membutuhkan minimal caption atau media.'], 400);
            return;
        }

        
        $existingPost = Database::fetch(
            "SELECT * FROM planning_konten_platform WHERE planning_konten_id = ? AND platform = ?",
            [$id, $platform]
        );

        if ($existingPost) {
            if ($existingPost['status'] === 'publishing') {
                $this->json(['success' => false, 'message' => 'Konten sedang dalam proses publishing ke ' . ucfirst($platform) . '. Mohon tunggu.'], 409);
                return;
            }
            if ($existingPost['status'] === 'success') {
                $this->json([
                    'success' => false,
                    'message' => 'Konten sudah berhasil dipublish ke ' . ucfirst($platform) . ' (Post ID: ' . $existingPost['platform_post_id'] . '). Tidak dapat mengirim ulang.'
                ], 409);
                return;
            }
        }

        
        if ($existingPost) {
            Database::execute(
                "UPDATE planning_konten_platform SET status = 'publishing', error_message = NULL, updated_at = NOW() WHERE id = ?",
                [$existingPost['id']]
            );
        } else {
            Database::execute(
                "INSERT INTO planning_konten_platform (planning_konten_id, platform, status, created_at, updated_at) VALUES (?, ?, 'publishing', NOW(), NOW())",
                [$id, $platform]
            );
        }

        
        $mediaUrl = null;
        $mediaType = $planning['media_type'] ?? 'text';

        if (!empty($planning['media_path'])) {
            $rawPath = $planning['media_path'];
            if (preg_match('/uploads\/(.+)$/i', $rawPath, $m)) {
                $relPath = $m[1];
            } else {
                $relPath = ltrim($rawPath, '/');
            }
            $localPath = UPLOADS_PATH . $relPath;
            if (!file_exists($localPath)) {
                $this->updatePlatformStatus($id, $platform, 'failed', null, 'File media tidak ditemukan: ' . $planning['media_path']);
                $this->json(['success' => false, 'message' => 'File media tidak ditemukan di server.'], 400);
                return;
            }

            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($localPath);
            $allowedImage = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $allowedVideo = ['video/mp4', 'video/quicktime', 'video/mpeg', 'video/avi'];

            if (in_array($mediaType, ['image', 'carousel']) && !in_array($mimeType, $allowedImage)) {
                $this->updatePlatformStatus($id, $platform, 'failed', null, 'Format media tidak didukung: ' . $mimeType);
                $this->json(['success' => false, 'message' => 'Format media gambar tidak didukung: ' . $mimeType], 400);
                return;
            }
            if (in_array($mediaType, ['video', 'reels', 'shorts']) && !in_array($mimeType, $allowedVideo)) {
                $this->updatePlatformStatus($id, $platform, 'failed', null, 'Format video tidak didukung: ' . $mimeType);
                $this->json(['success' => false, 'message' => 'Format video tidak didukung: ' . $mimeType], 400);
                return;
            }

            
            $mediaUrl = BASE_URL . '/uploads/' . ltrim($planning['media_path'], '/');
        }

        
        
        
        $startTime = microtime(true);
        $result = ['success' => false, 'error' => 'Platform belum didukung'];

        try {
            if ($platform === 'facebook') {
                require_once SERVICES_PATH . 'FacebookService.php';
                $service = new FacebookService();
                $result = $this->executeFacebookPublish($service, $account, $accessToken, $fullCaption, $mediaUrl, $mediaType);
            } elseif ($platform === 'instagram') {
                require_once SERVICES_PATH . 'InstagramService.php';
                $service = new InstagramService();
                $result = $this->executeInstagramPublish($service, $account, $accessToken, $fullCaption, $mediaUrl, $mediaType);
            } elseif ($platform === 'tiktok') {
                require_once SERVICES_PATH . 'TikTokService.php';
                $service = new TikTokService();
                $targetVideoPath = ($localPath && file_exists($localPath)) ? $localPath : $mediaUrl;
                $result = $service->publishVideo($account['account_id'], $accessToken, $targetVideoPath, $fullCaption);
            } elseif ($platform === 'youtube') {
                require_once SERVICES_PATH . 'YouTubeService.php';
                $service = new YouTubeService();
                $targetVideoPath = ($localPath && file_exists($localPath)) ? $localPath : $mediaUrl;
                $result = $service->publishVideo($account['account_id'], $accessToken, $targetVideoPath, $planning['judul'], $fullCaption);
            }
        } catch (\Throwable $e) {
            $result = ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }

        $durationMs = (int)((microtime(true) - $startTime) * 1000);

        
        
        
        if ($result['success']) {
            $postId = $result['post_id'] ?? null;

            $this->updatePlatformStatus($id, $platform, 'success', $postId, null);

            
            Database::execute(
                "UPDATE planning_konten SET status = 'success', posted_at = NOW(), post_id_platform = ?, post_url = ? WHERE id = ?",
                [$postId, $result['post_url'] ?? null, $id]
            );

            
            $this->insertPostLog($id, $account['id'], 'posting', 'success', null, 200, null, $durationMs);

            
            $this->logActivity('publish_' . $platform, "Publish Now berhasil ke {$platform}. Planning #{$id}. Post ID: {$postId}");

            $this->json([
                'success' => true,
                'message' => 'Konten berhasil dipublish ke ' . ucfirst($platform) . '!',
                'post_id' => $postId,
                'post_url' => $result['post_url'] ?? null
            ]);
        } else {
            $errorMsg = $result['error'] ?? 'Unknown error';
            $httpCode = $result['http_code'] ?? null;
            $retryCount = ($existingPost['retry_count'] ?? 0) + 1;

            $this->updatePlatformStatus($id, $platform, 'failed', null, $errorMsg, $retryCount);

            
            $this->insertPostLog($id, $account['id'], 'posting', 'failed', $errorMsg, $httpCode, null, $durationMs);

            
            $this->logActivity('publish_failed_' . $platform, "Publish Now gagal ke {$platform}. Planning #{$id}. Error: {$errorMsg}");

            $this->json([
                'success' => false,
                'message' => 'Gagal publish ke ' . ucfirst($platform) . ': ' . $errorMsg
            ], 500);
        }
    }

    


    private function executeFacebookPublish(FacebookService $service, array $account, string $token, string $caption, ?string $mediaUrl, string $mediaType): array
    {
        $pageId = $account['account_id'];

        if (!empty($mediaUrl) && in_array($mediaType, ['image', 'carousel'])) {
            return $service->publishImage($pageId, $token, $mediaUrl, $caption);
        } elseif (!empty($mediaUrl) && in_array($mediaType, ['video', 'reels', 'shorts'])) {
            return $service->publishVideo($pageId, $token, $mediaUrl, $caption, $caption);
        } else {
            return $service->publishText($pageId, $token, $caption);
        }
    }

    


    private function executeInstagramPublish(InstagramService $service, array $account, string $token, string $caption, ?string $mediaUrl, string $mediaType): array
    {
        $igAccountId = $account['account_id'];

        if (empty($mediaUrl)) {
            return ['success' => false, 'error' => 'Instagram membutuhkan media (gambar atau video) untuk posting.'];
        }

        if (in_array($mediaType, ['image', 'carousel'])) {
            return $service->publishImage($igAccountId, $token, $mediaUrl, $caption);
        } elseif (in_array($mediaType, ['video', 'reels', 'shorts'])) {
            return $service->publishVideo($igAccountId, $token, $mediaUrl, $caption);
        }

        return ['success' => false, 'error' => 'Tipe media tidak didukung oleh Instagram: ' . $mediaType];
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
        } else {
            Database::execute(
                "INSERT INTO planning_konten_platform (planning_konten_id, platform, status, platform_post_id, error_message, retry_count, published_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [$planningId, $platform, $status, $postId, $errorMsg, $retryCount, $status === 'success' ? date('Y-m-d H:i:s') : null]
            );
        }
    }

    


    private function insertPostLog(string $planningId, string $akunId, string $action, string $status, ?string $errorMsg, ?int $httpCode, ?string $requestData = null, ?int $durationMs = null): void
    {
        Database::execute(
            "INSERT INTO posting_logs (planning_id, platform_akun_id, action, status, error_message, http_code, ip_address, performed_by, duration_ms, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $planningId,
                $akunId,
                $action,
                $status,
                $errorMsg,
                $httpCode,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                Session::get('user_id'),
                $durationMs
            ]
        );
    }

    


    private function logActivity(string $action, string $description): void
    {
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address, user_agent) 
             VALUES (?, ?, ?, 'posting', ?, ?, ?)",
            [
                Session::get('user_id'),
                Session::get('user_role_id'),
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]
        );
    }
}

