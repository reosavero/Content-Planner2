<?php





class SocialAuthController extends Controller
{
    


    public function callback(string $platform): void
    {
        $code = $_GET['code'] ?? '';
        $error = $_GET['error'] ?? '';

        if (!empty($error)) {
            Session::setFlash('error', 'Autentikasi dibatalkan atau gagal.');
            $this->redirect('/platform-accounts');
            return;
        }

        if (empty($code)) {
            Session::setFlash('error', 'Kode autorisasi tidak ditemukan.');
            $this->redirect('/platform-accounts');
            return;
        }

        try {
            $result = match($platform) {
                'facebook' => $this->handleFacebookCallback($code),
                'instagram' => $this->handleInstagramCallback($code),
                'youtube' => $this->handleYouTubeCallback($code),
                'tiktok' => $this->handleTikTokCallback($code),
                'twitter' => $this->handleTwitterCallback($code),
                'threads' => $this->handleThreadsCallback($code),
                default => throw new \Exception('Platform tidak dikenal'),
            };

            if ($result['success']) {
                Session::setFlash('success', 'Akun ' . ucfirst($platform) . ' berhasil dihubungkan.');
            } else {
                Session::setFlash('error', $result['error'] ?? 'Gagal menghubungkan akun.');
            }
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error: ' . $e->getMessage());
        }

        $this->redirect('/platform-accounts');
    }

    private function handleFacebookCallback(string $code): array
    {
        
        $tokenUrl = "https://graph.facebook.com/" . FB_GRAPH_VERSION . "/oauth/access_token";
        $tokenUrl .= "?client_id=" . FB_APP_ID;
        $tokenUrl .= "&client_secret=" . FB_APP_SECRET;
        $tokenUrl .= "&redirect_uri=" . urlencode(FB_REDIRECT_URI);
        $tokenUrl .= "&code=" . $code;

        $response = $this->httpGet($tokenUrl);
        $data = json_decode($response, true);

        if (empty($data['access_token'])) {
            return ['success' => false, 'error' => 'Gagal mendapatkan access token'];
        }

        
        $longUrl = "https://graph.facebook.com/" . FB_GRAPH_VERSION . "/oauth/access_token";
        $longUrl .= "?grant_type=fb_exchange_token";
        $longUrl .= "&client_id=" . FB_APP_ID;
        $longUrl .= "&client_secret=" . FB_APP_SECRET;
        $longUrl .= "&fb_exchange_token=" . $data['access_token'];

        $longResponse = $this->httpGet($longUrl);
        $longData = json_decode($longResponse, true);
        $accessToken = $longData['access_token'] ?? $data['access_token'];

        
        $pagesUrl = "https://graph.facebook.com/" . FB_GRAPH_VERSION . "/me/accounts?access_token=" . $accessToken;
        $pagesResponse = $this->httpGet($pagesUrl);
        $pagesData = json_decode($pagesResponse, true);

        if (!empty($pagesData['data'])) {
            foreach ($pagesData['data'] as $page) {
                $this->saveAccount('facebook', $page['id'], $page['name'], $accessToken, null, $page['access_token'] ?? $accessToken);
            }
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Tidak ada halaman Facebook yang ditemukan.'];
    }

    private function handleInstagramCallback(string $code): array
    {
        
        $tokenUrl = "https://graph.facebook.com/" . FB_GRAPH_VERSION . "/oauth/access_token";
        $tokenUrl .= "?client_id=" . FB_APP_ID;
        $tokenUrl .= "&client_secret=" . FB_APP_SECRET;
        $tokenUrl .= "&redirect_uri=" . urlencode(FB_REDIRECT_URI);
        $tokenUrl .= "&code=" . $code;

        $response = $this->httpGet($tokenUrl);
        $data = json_decode($response, true);

        if (empty($data['access_token'])) {
            return ['success' => false, 'error' => 'Gagal mendapatkan access token'];
        }

        
        $igUrl = "https://graph.facebook.com/" . FB_GRAPH_VERSION . "/me/accounts?fields=instagram_business_account{id,username,name,profile_picture_url}&access_token=" . $data['access_token'];
        $igResponse = $this->httpGet($igUrl);
        $igData = json_decode($igResponse, true);

        if (!empty($igData['data'])) {
            foreach ($igData['data'] as $page) {
                if (!empty($page['instagram_business_account'])) {
                    $ig = $page['instagram_business_account'];
                    $this->saveAccount('instagram', $ig['id'], $ig['name'] ?? $ig['username'], $data['access_token'], null, $data['access_token']);
                }
            }
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Tidak ada akun Instagram Business yang ditemukan.'];
    }

    private function handleYouTubeCallback(string $code): array
    {
        
        return ['success' => false, 'error' => 'YouTube integration coming soon.'];
    }

    private function handleTikTokCallback(string $code): array
    {
        
        return ['success' => false, 'error' => 'TikTok integration coming soon.'];
    }

    private function handleTwitterCallback(string $code): array
    {
        
        return ['success' => false, 'error' => 'Twitter/X integration coming soon.'];
    }

    private function handleThreadsCallback(string $code): array
    {
        
        return ['success' => false, 'error' => 'Threads integration coming soon.'];
    }

    


    private function saveAccount(string $platform, string $accountId, string $accountName, string $accessToken, ?string $refreshToken = null, ?string $pageToken = null): void
    {
        $platformData = Database::fetch("SELECT id FROM platform_sosmed WHERE slug = ?", [$platform]);
        if (!$platformData) return;

        $existing = Database::fetch(
            "SELECT id FROM platform_akun WHERE platform_id = ? AND account_id = ?",
            [$platformData['id'], $accountId]
        );

        $tokenToSave = Security::encrypt($pageToken ?? $accessToken);
        $refreshToSave = $refreshToken ? Security::encrypt($refreshToken) : null;
        $expiresAt = date('Y-m-d H:i:s', time() + 5184000); 

        if ($existing) {
            Database::execute(
                "UPDATE platform_akun SET access_token = ?, refresh_token = ?, token_expires_at = ?, 
                 token_status = 'active', is_connected = 1, updated_at = NOW() WHERE id = ?",
                [$tokenToSave, $refreshToSave, $expiresAt, $existing['id']]
            );
        } else {
            Database::execute(
                "INSERT INTO platform_akun (platform_id, user_id, account_name, account_id, access_token, 
                 refresh_token, token_expires_at, token_status, is_connected, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 1, 1, NOW(), NOW())",
                [$platformData['id'], Session::get('user_id'), $accountName, $accountId, 
                 $tokenToSave, $refreshToSave, $expiresAt]
            );
        }
    }

    


    private function httpGet(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ?: '';
    }
}
