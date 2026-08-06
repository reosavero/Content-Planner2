<?php





class PlatformAccountController extends Controller
{
    public function index(): void
    {
        $accounts = Database::fetchAll(
            "SELECT pa.*, p.name as platform_name, p.slug as platform_slug, 
                    p.icon as platform_icon, p.color as platform_color,
                    u.name as pic_name
             FROM platform_akun pa
             JOIN platform_sosmed p ON p.id = pa.platform_id
             LEFT JOIN users u ON u.id = pa.user_id
             ORDER BY p.sort_order, pa.account_name"
        );

        $platforms = Database::fetchAll(
            "SELECT * FROM platform_sosmed WHERE is_active = 1 ORDER BY sort_order"
        );

        $this->view('platform-accounts/index', [
            'title' => 'Akun Sosial Media',
            'accounts' => $accounts,
            'platforms' => $platforms,
            'breadcrumbs' => [['label' => 'Akun', 'url' => '#'], ['label' => 'Platform', 'url' => '#']],
        ]);
    }

    public function connect(string $platform): void
    {
        $platformData = Database::fetch(
            "SELECT * FROM platform_sosmed WHERE slug = ? AND is_active = 1",
            [$platform]
        );

        if (!$platformData) {
            $this->redirectWith('/platform-accounts', 'error', 'Platform tidak ditemukan.');
        }

        
        $oauthUrl = $this->getOAuthUrl($platform);
        if ($oauthUrl) {
            $this->redirect($oauthUrl);
        }

        
        $this->view('platform-accounts/connect', [
            'title' => 'Hubungkan ' . $platformData['name'],
            'platform' => $platformData,
            'breadcrumbs' => [
                ['label' => 'Platform Akun', 'url' => '/platform-accounts'],
                ['label' => 'Hubungkan ' . $platformData['name'], 'url' => '#'],
            ],
        ]);
    }

    public function disconnect(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }

        Database::execute(
            "UPDATE platform_akun SET access_token = NULL, refresh_token = NULL, 
             token_status = 'revoked', is_connected = 0 WHERE id = ?",
            [$id]
        );

        $this->json(['success' => true, 'message' => 'Akun berhasil diputuskan.']);
    }

    public function refreshToken(string $id): void
    {
        if (!Session::validateCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $this->json(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
        }

        
        Database::execute(
            "UPDATE platform_akun SET token_status = 'active', last_sync_at = NOW() WHERE id = ?",
            [$id]
        );

        $this->json(['success' => true, 'message' => 'Token berhasil direfresh.']);
    }

    public function sync(string $id): void
    {
        $account = Database::fetch("SELECT * FROM platform_akun WHERE id = ?", [$id]);
        if (!$account) {
            $this->redirectWith('/platform-accounts', 'error', 'Akun tidak ditemukan.');
        }

        
        Database::execute(
            "UPDATE platform_akun SET last_sync_at = NOW() WHERE id = ?",
            [$id]
        );

        $this->redirectWith('/platform-accounts', 'success', 'Sinkronisasi berhasil.');
    }

    private function getOAuthUrl(string $platform): ?string
    {
        $baseUrl = BASE_URL;
        
        return match($platform) {
            'facebook' => "https://www.facebook.com/" . FB_GRAPH_VERSION . "/dialog/oauth?client_id=" . FB_APP_ID . "&redirect_uri=" . urlencode(FB_REDIRECT_URI) . "&scope=pages_manage_posts,pages_read_engagement,pages_show_list,public_profile&response_type=code",
            'instagram' => "https://www.instagram.com/oauth/authorize?client_id=" . FB_APP_ID . "&redirect_uri=" . urlencode(defined('IG_REDIRECT_URI') ? IG_REDIRECT_URI : FB_REDIRECT_URI) . "&scope=instagram_basic,instagram_content_publish,pages_show_list&response_type=code",
            'youtube' => "https://accounts.google.com/o/oauth2/auth?client_id=" . YT_CLIENT_ID . "&redirect_uri=" . urlencode(YT_REDIRECT_URI) . "&scope=https://www.googleapis.com/auth/youtube.upload%20https://www.googleapis.com/auth/youtube.readonly&response_type=code&access_type=offline",
            'tiktok' => "https://www.tiktok.com/v2/auth/authorize?client_key=" . TT_CLIENT_KEY . "&redirect_uri=" . urlencode(TT_REDIRECT_URI) . "&scope=user.info.basic,video.publish&response_type=code",
            'twitter' => "https://twitter.com/i/oauth2/authorize?client_id=" . TW_API_KEY . "&redirect_uri=" . urlencode(TW_REDIRECT_URI) . "&scope=tweet.write%20tweet.read%20users.read%20offline.access&response_type=code&code_challenge=challenge&code_challenge_method=plain",
            'threads' => "https://threads.net/oauth/authorize?client_id=" . TH_CLIENT_ID . "&redirect_uri=" . urlencode(TH_REDIRECT_URI) . "&scope=threads_basic,threads_content_publish&response_type=code",
            default => null,
        };
    }
}
