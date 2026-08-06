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

