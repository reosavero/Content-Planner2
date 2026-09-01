<?php






class SocialMediaController extends Controller
{
    


    private function checkAdminAccess(): void
    {
        $roleSlug = Session::get('user_role_slug');
        if ($roleSlug !== 'superadmin' && $roleSlug !== 'admin') {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Magang / Kontributor tidak diperbolehkan mengelola akun sosial media.'
                ], 403);
            }
            Session::setFlash('error', 'Akses ditolak. Magang / Kontributor tidak memiliki akses ke pengaturan sosial media.');
            $this->redirect('/dashboard');
            exit;
        }
    }

    



    public function index(): void
    {
        $this->checkAdminAccess();

        
        $targetSlugs = ['facebook', 'instagram', 'tiktok', 'youtube'];

        
        $platforms = Database::fetchAll(
            "SELECT * FROM platform_sosmed WHERE slug IN ('" . implode("','", $targetSlugs) . "') ORDER BY sort_order ASC"
        );

        
        $connectedAccountsRaw = Database::fetchAll(
            "SELECT pa.*, p.slug as platform_slug, u.name as connected_by_name
             FROM platform_akun pa
             JOIN platform_sosmed p ON p.id = pa.platform_id
             LEFT JOIN users u ON u.id = pa.user_id
             WHERE pa.is_connected = 1 AND pa.is_active = 1"
        );

        $connectedAccounts = [];
        foreach ($connectedAccountsRaw as $acc) {
            $connectedAccounts[$acc['platform_slug']] = $acc;
        }

        $this->view('social-media/index', [
            'title' => 'Integrasi & Koneksi Sosial Media',
            'platforms' => $platforms,
            'connectedAccounts' => $connectedAccounts,
            'breadcrumbs' => [
                ['label' => 'Akun', 'url' => '#'],
                ['label' => 'Social Media', 'url' => '/social-media']
            ]
        ]);
    }

    



    public function connect(string $platform): void
    {
        $this->checkAdminAccess();

        $platform = strtolower(trim($platform));
        if (!in_array($platform, ['facebook', 'instagram', 'tiktok', 'youtube'], true)) {
            Session::setFlash('error', 'Platform tidak valid atau belum didukung.');
            $this->redirect('/social-media');
            return;
        }

        
        if (in_array($platform, ['facebook', 'instagram']) && (empty(META_APP_ID) || empty(META_APP_SECRET))) {
            Session::setFlash('error', 'Credential Meta API (META_APP_ID / META_APP_SECRET) belum diisi pada config/social.php. Silakan isi App ID & App Secret Meta Anda dari developers.facebook.com.');
            $this->redirect('/social-media');
            return;
        }

        
        if ($platform === 'tiktok' && (empty(TT_CLIENT_KEY) || empty(TT_CLIENT_SECRET))) {
            Session::setFlash('error', 'Credential TikTok API (TT_CLIENT_KEY / TT_CLIENT_SECRET) belum diisi pada config/social.php. Silakan isi Client Key & Client Secret dari developers.tiktok.com.');
            $this->redirect('/social-media');
            return;
        }

        
        if ($platform === 'youtube' && (empty(YT_CLIENT_ID) || empty(YT_CLIENT_SECRET))) {
            Session::setFlash('error', 'Credential YouTube API (YT_CLIENT_ID / YT_CLIENT_SECRET) belum diisi pada config/social.php. Silakan isi Client ID & Client Secret dari Google Cloud Console.');
            $this->redirect('/social-media');
            return;
        }

        
        $state = Security::generateToken(32);
        Session::set('oauth_state', $state);
        Session::set('oauth_platform', $platform);

        $authUrl = null;
        if ($platform === 'facebook') {
            require_once SERVICES_PATH . 'FacebookService.php';
            $service = new FacebookService();
            $authUrl = $service->getAuthorizationUrl($state);
        } elseif ($platform === 'instagram') {
            require_once SERVICES_PATH . 'InstagramService.php';
            $service = new InstagramService();
            $authUrl = $service->getAuthorizationUrl($state);
        } elseif ($platform === 'tiktok') {
            require_once SERVICES_PATH . 'TikTokService.php';
            $service = new TikTokService();
            $authUrl = $service->getAuthorizationUrl($state);
        } elseif ($platform === 'youtube') {
            require_once SERVICES_PATH . 'YouTubeService.php';
            $service = new YouTubeService();
            $authUrl = $service->getAuthorizationUrl($state);
        }

        if ($authUrl && str_starts_with($authUrl, 'http')) {
            $this->redirect($authUrl);
            return;
        }

        Session::setFlash('warning', 'Integrasi platform ' . ucfirst($platform) . ' belum diimplementasikan.');
        $this->redirect('/social-media');
    }

    



    public function callback(string $platform): void
    {
        if (Session::isLoggedIn()) {
            $this->checkAdminAccess();
        }

        $platform = strtolower(trim($platform));
        $error = $_GET['error'] ?? $_GET['error_reason'] ?? '';
        $errorDesc = $_GET['error_description'] ?? '';
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        
        $savedState = Session::get('oauth_state');
        Session::remove('oauth_state');
        Session::remove('oauth_platform');

        if (!empty($savedState) && !empty($state) && !hash_equals($savedState, $state)) {
            Session::setFlash('error', 'Validasi OAuth State gagal. Request tidak valid atau kadaluarsa untuk keamanan.');
            $this->redirect('/social-media');
            return;
        }

        if (!empty($error)) {
            Session::setFlash('error', 'Otentikasi dibatalkan atau gagal: ' . Security::escape($errorDesc ?: $error));
            $this->redirect('/social-media');
            return;
        }

        if (empty($code)) {
            Session::setFlash('error', 'Authorization Code tidak ditemukan dari provider OAuth.');
            $this->redirect('/social-media');
            return;
        }

        try {
            $result = ['success' => false, 'error' => 'Platform tidak dikenali'];

            if ($platform === 'facebook') {
                require_once SERVICES_PATH . 'FacebookService.php';
                $service = new FacebookService();
                $result = $service->handleCallback($code);
            } elseif ($platform === 'instagram') {
                require_once SERVICES_PATH . 'InstagramService.php';
                $service = new InstagramService();
                $result = $service->handleCallback($code);
            } elseif ($platform === 'tiktok') {
                require_once SERVICES_PATH . 'TikTokService.php';
                $service = new TikTokService();
                $result = $service->handleCallback($code);
            } elseif ($platform === 'youtube') {
                require_once SERVICES_PATH . 'YouTubeService.php';
                $service = new YouTubeService();
                $result = $service->handleCallback($code);
            }

            if ($result['success'] && !empty($result['account'])) {
                $acc = $result['account'];
                $this->saveConnectedAccount($platform, $acc);

                
                $this->logActivity("connect_{$platform}", "User menghubungkan akun {$platform}: {$acc['account_name']}");

                Session::setFlash('success', "Akun " . ucfirst($platform) . " ({$acc['account_name']}) berhasil terhubung ke sistem TVRI Jawa Timur!");
            } else {
                Session::setFlash('error', $result['error'] ?? "Gagal menghubungkan akun {$platform}.");
            }
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        $this->redirect('/social-media');
    }

    



    public function disconnect(string $platform): void
    {
        $this->checkAdminAccess();

        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
            return;
        }

        $platform = strtolower(trim($platform));

        $platformData = Database::fetch("SELECT id FROM platform_sosmed WHERE slug = ?", [$platform]);
        if (!$platformData) {
            $this->json(['success' => false, 'message' => 'Platform tidak ditemukan'], 404);
            return;
        }

        
        Database::execute(
            "UPDATE platform_akun SET is_connected = 0, token_status = 'revoked', 
             access_token = NULL, refresh_token = NULL, updated_at = NOW() 
             WHERE platform_id = ?",
            [$platformData['id']]
        );

        
        $this->logActivity("disconnect_{$platform}", "User memutuskan koneksi akun {$platform}");

        $this->json([
            'success' => true,
            'message' => "Koneksi " . ucfirst($platform) . " berhasil diputuskan."
        ]);
    }

    



    public function reconnect(string $platform): void
    {
        $this->checkAdminAccess();
        $this->logActivity("reconnect_{$platform}", "User melakukan re-koneksi akun {$platform}");
        $this->connect($platform);
    }

    public function publishMulti(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
            return;
        }

        $roleSlug = Session::get('user_role_slug');
        if ($roleSlug !== 'superadmin' && $roleSlug !== 'admin') {
            $this->json(['success' => false, 'message' => 'Hanya Admin / Superadmin yang dapat Publish Multi-Platform.'], 403);
            return;
        }

        $planning = Database::fetch(
            "SELECT pk.* FROM planning_konten pk WHERE pk.id = ? AND pk.deleted_at IS NULL",
            [$id]
        );

        if (!$planning) {
            $this->json(['success' => false, 'message' => 'Konten tidak ditemukan.'], 404);
            return;
        }

        if (!in_array($planning['status'], ['approved', 'scheduled'], true)) {
            $this->json(['success' => false, 'message' => 'Konten harus status Approved atau Scheduled. Saat ini: ' . $planning['status']], 400);
            return;
        }

        // Ambil platform target dari planning_konten_platform
        $targetPlatforms = Database::fetchAll(
            "SELECT pkp.* FROM planning_konten_platform pkp WHERE pkp.planning_konten_id = ? AND pkp.status IN ('pending', 'failed')",
            [$id]
        );

        if (empty($targetPlatforms)) {
            $this->json(['success' => false, 'message' => 'Tidak ada platform target yang valid untuk dipublish.'], 400);
            return;
        }

        $results = [];
        $overallSuccess = true;

        foreach ($targetPlatforms as $tp) {
            $platform = $tp['platform'];
            
            // Get platform info
            $platformData = Database::fetch("SELECT id, name FROM platform_sosmed WHERE slug = ?", [$platform]);
            if (!$platformData) {
                $results[$platform] = ['success' => false, 'error' => 'Platform tidak terdaftar'];
                $overallSuccess = false;
                continue;
            }

            // Get connected account
            $account = Database::fetch(
                "SELECT * FROM platform_akun WHERE platform_id = ? AND is_connected = 1 AND is_active = 1 AND token_status = 'active'",
                [$platformData['id']]
            );

            if (!$account) {
                $results[$platform] = ['success' => false, 'error' => 'Akun tidak terhubung'];
                $overallSuccess = false;
                $this->updatePlatformStatus($id, $platform, 'failed', null, 'Akun tidak terhubung');
                continue;
            }

            $accessToken = Security::decrypt($account['access_token']);
            if (empty($accessToken)) {
                $results[$platform] = ['success' => false, 'error' => 'Token tidak valid'];
                $overallSuccess = false;
                $this->updatePlatformStatus($id, $platform, 'failed', null, 'Token tidak valid');
                continue;
            }

            // Check token expiry & refresh
            if (!empty($account['token_expires_at']) && strtotime($account['token_expires_at']) < time()) {
                require_once HELPERS_PATH . 'SocialMediaAPI.php';
                $refreshResult = SocialMediaAPI::refreshToken($account);
                if ($refreshResult['success']) {
                    $accessToken = $refreshResult['access_token'] ?? Security::decrypt(Database::fetchColumn("SELECT access_token FROM platform_akun WHERE id = ?", [$account['id']]));
                } else {
                    Database::execute("UPDATE platform_akun SET token_status = 'expired' WHERE id = ?", [$account['id']]);
                    $results[$platform] = ['success' => false, 'error' => 'Token expired & gagal refresh'];
                    $overallSuccess = false;
                    $this->updatePlatformStatus($id, $platform, 'failed', null, 'Token expired');
                    continue;
                }
            }

            // Prepare content
            $caption = trim($planning['caption'] ?? '');
            $hashtag = trim($planning['hashtag_text'] ?? '');
            $fullCaption = $caption . ($hashtag ? "\n\n" . $hashtag : '');
            $mediaType = $planning['media_type'] ?? 'text';
            
            // Build media URL
            $mediaUrl = null;
            $localPath = null;
            if (!empty($planning['media_path'])) {
                $rawPath = $planning['media_path'];
                if (preg_match('/uploads\/(.+)$/i', $rawPath, $m)) {
                    $relPath = $m[1];
                } else {
                    $relPath = ltrim($rawPath, '/');
                }
                $localPath = UPLOADS_PATH . $relPath;
                if (file_exists($localPath)) {
                    $mediaUrl = BASE_URL . '/uploads/' . ltrim($planning['media_path'], '/');
                }
            }

            // Execute publish per platform
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
                    $targetVideo = ($localPath && file_exists($localPath)) ? $localPath : $mediaUrl;
                    $result = $service->publishVideo($account['account_id'], $accessToken, $targetVideo, $fullCaption);
                } elseif ($platform === 'youtube') {
                    require_once SERVICES_PATH . 'YouTubeService.php';
                    $service = new YouTubeService();
                    $targetVideo = ($localPath && file_exists($localPath)) ? $localPath : $mediaUrl;
                    $result = $service->publishVideo($account['account_id'], $accessToken, $targetVideo, $planning['judul'], $fullCaption);
                }
            } catch (\Throwable $e) {
                $result = ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
            }

            $durationMs = (int)((microtime(true) - $startTime) * 1000);
            $retryCount = ($tp['retry_count'] ?? 0) + 1;

            if ($result['success']) {
                $postId = $result['post_id'] ?? null;
                $postUrl = $result['post_url'] ?? null;
                
                $this->updatePlatformStatus($id, $platform, 'success', $postId, null);
                
                Database::execute(
                    "UPDATE planning_konten SET status = 'success', posted_at = NOW(), post_id_platform = ?, post_url = ? WHERE id = ?",
                    [$postId, $postUrl, $id]
                );
                
                $this->insertPostLog($id, $account['id'], 'posting', 'success', null, 200, null, $durationMs);
                $results[$platform] = ['success' => true, 'post_id' => $postId, 'post_url' => $postUrl];
            } else {
                $errorMsg = $result['error'] ?? 'Unknown error';
                $httpCode = $result['http_code'] ?? null;
                
                $this->updatePlatformStatus($id, $platform, 'failed', null, $errorMsg, $retryCount);
                $this->insertPostLog($id, $account['id'], 'posting', 'failed', $errorMsg, $httpCode, null, $durationMs);
                $results[$platform] = ['success' => false, 'error' => $errorMsg];
                $overallSuccess = false;
            }
        }

        $this->logActivity('publish_multi', "Publish Multi-Platform Planning #{$id}. Results: " . json_encode($results));

        $this->json([
            'success' => $overallSuccess,
            'message' => $overallSuccess ? 'Berhasil publish ke semua platform!' : 'Beberapa platform gagal dipublish.',
            'results' => $results
        ]);
    }

    private function executeFacebookPublish($service, $account, $accessToken, $caption, $mediaUrl, $mediaType): array
    {
        if ($mediaType === 'video' || $mediaType === 'reels') {
            return $service->publishVideo($account['account_id'], $accessToken, $mediaUrl, $caption);
        } elseif ($mediaType === 'image' || $mediaType === 'carousel') {
            return $service->publishImage($account['account_id'], $accessToken, $mediaUrl, $caption);
        } else {
            return $service->publishText($account['account_id'], $accessToken, $caption);
        }
    }

    private function executeInstagramPublish($service, $account, $accessToken, $caption, $mediaUrl, $mediaType): array
    {
        // Instagram needs Instagram Business Account ID
        $igAccountId = $account['account_id'];
        
        if ($mediaType === 'video' || $mediaType === 'reels') {
            return $service->publishVideo($igAccountId, $accessToken, $mediaUrl, $caption);
        } elseif ($mediaType === 'image' || $mediaType === 'carousel') {
            return $service->publishImage($igAccountId, $accessToken, $mediaUrl, $caption);
        } else {
            return ['success' => false, 'error' => 'Instagram memerlukan media (image/video)'];
        }
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

        private function saveConnectedAccount(string $platform, array $acc): void
    {
        $platformData = Database::fetch("SELECT id FROM platform_sosmed WHERE slug = ?", [$platform]);
        if (!$platformData) return;

        $platformId = $platformData['id'];
        $userId = Session::get('user_id') ?? 1;

        $encryptedToken = Security::encrypt($acc['access_token']);
        $encryptedRefresh = !empty($acc['refresh_token']) ? Security::encrypt($acc['refresh_token']) : null;
        $expiresAt = $acc['token_expires_at'] ?? date('Y-m-d H:i:s', time() + 5184000);

        
        Database::execute(
            "UPDATE platform_akun SET is_connected = 0, token_status = 'expired' WHERE platform_id = ?",
            [$platformId]
        );

        $existing = Database::fetch(
            "SELECT id FROM platform_akun WHERE platform_id = ? AND account_id = ?",
            [$platformId, $acc['account_id']]
        );

        if ($existing) {
            Database::execute(
                "UPDATE platform_akun SET 
                 account_name = ?, account_username = ?, access_token = ?, refresh_token = ?, 
                 token_expires_at = ?, token_status = 'active', is_connected = 1, is_active = 1, 
                 user_id = ?, updated_at = NOW() 
                 WHERE id = ?",
                [
                    $acc['account_name'],
                    $acc['account_username'] ?? null,
                    $encryptedToken,
                    $encryptedRefresh,
                    $expiresAt,
                    $userId,
                    $existing['id']
                ]
            );
        } else {
            Database::execute(
                "INSERT INTO platform_akun 
                 (platform_id, user_id, account_name, account_id, account_username, access_token, refresh_token, token_expires_at, token_status, is_connected, is_active, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, 1, NOW(), NOW())",
                [
                    $platformId,
                    $userId,
                    $acc['account_name'],
                    $acc['account_id'],
                    $acc['account_username'] ?? null,
                    $encryptedToken,
                    $encryptedRefresh,
                    $expiresAt
                ]
            );
        }
    }

    


    private function logActivity(string $action, string $description): void
    {
        Database::execute(
            "INSERT INTO activity_logs (user_id, role_id, action, module, description, ip_address, user_agent) 
             VALUES (?, ?, ?, 'social_media', ?, ?, ?)",
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

