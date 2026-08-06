<?php





class FacebookService
{
    private string $appId;
    private string $appSecret;
    private string $graphVersion;
    private string $redirectUri;

    public function __construct()
    {
        $this->appId = META_APP_ID;
        $this->appSecret = META_APP_SECRET;
        $this->graphVersion = META_GRAPH_VERSION;
        $this->redirectUri = FB_REDIRECT_URI;
    }

    


    public function getAuthorizationUrl(string $state): string
    {
        $permissions = [
            'pages_manage_posts',
            'pages_read_engagement',
            'pages_show_list',
            'public_profile'
        ];

        $params = [
            'client_id' => $this->appId,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'scope' => implode(',', $permissions),
            'response_type' => 'code'
        ];

        return "https://www.facebook.com/{$this->graphVersion}/dialog/oauth?" . http_build_query($params);
    }

    


    public function handleCallback(string $code): array
    {
        if (empty($this->appId) || empty($this->appSecret)) {
            return [
                'success' => false,
                'error' => 'Credential Meta (META_APP_ID / META_APP_SECRET) belum dikonfigurasi pada server.'
            ];
        }

        
        $tokenUrl = "https://graph.facebook.com/{$this->graphVersion}/oauth/access_token?" . http_build_query([
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code
        ]);

        $response = $this->httpRequest($tokenUrl);
        if (!$response['success']) {
            return ['success' => false, 'error' => 'Gagal terhubung ke server Meta: ' . $response['error']];
        }

        $data = json_decode($response['data'], true);
        if (empty($data['access_token'])) {
            $errorMsg = $data['error']['message'] ?? 'Gagal mendapatkan user access token';
            return ['success' => false, 'error' => $errorMsg];
        }

        $userToken = $data['access_token'];

        
        $longTokenUrl = "https://graph.facebook.com/{$this->graphVersion}/oauth/access_token?" . http_build_query([
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'fb_exchange_token' => $userToken
        ]);

        $longResponse = $this->httpRequest($longTokenUrl);
        if ($longResponse['success']) {
            $longData = json_decode($longResponse['data'], true);
            if (!empty($longData['access_token'])) {
                $userToken = $longData['access_token'];
            }
        }

        
        $pagesUrl = "https://graph.facebook.com/{$this->graphVersion}/me/accounts?fields=id,name,access_token,category,picture&access_token=" . urlencode($userToken);
        $pagesResponse = $this->httpRequest($pagesUrl);

        if (!$pagesResponse['success']) {
            return ['success' => false, 'error' => 'Gagal mengambil daftar Facebook Page: ' . $pagesResponse['error']];
        }

        $pagesData = json_decode($pagesResponse['data'], true);
        if (empty($pagesData['data'])) {
            return [
                'success' => false,
                'error' => 'Tidak ada Facebook Page yang ditemukan pada akun Facebook ini.'
            ];
        }

        
        $primaryPage = $pagesData['data'][0];

        return [
            'success' => true,
            'account' => [
                'account_id' => $primaryPage['id'],
                'account_name' => $primaryPage['name'],
                'account_username' => $primaryPage['name'],
                'access_token' => $primaryPage['access_token'], 
                'refresh_token' => $userToken,
                'token_expires_at' => date('Y-m-d H:i:s', time() + 5184000) 
            ]
        ];
    }

    


    public function publishText(string $pageId, string $pageToken, string $message): array
    {
        $url = "https://graph.facebook.com/{$this->graphVersion}/{$pageId}/feed";
        $payload = [
            'message' => $message,
            'access_token' => $pageToken
        ];

        $res = $this->httpRequest($url, 'POST', $payload);
        if (!$res['success']) {
            return ['success' => false, 'error' => $res['error']];
        }

        $data = json_decode($res['data'], true);
        if (empty($data['id'])) {
            $errorMsg = $data['error']['message'] ?? 'Publishing text to Facebook failed';
            return ['success' => false, 'error' => $errorMsg];
        }

        return [
            'success' => true,
            'post_id' => $data['id'],
            'post_url' => 'https://facebook.com/' . $data['id']
        ];
    }

    


    public function publishImage(string $pageId, string $pageToken, string $imageUrl, string $caption): array
    {
        $url = "https://graph.facebook.com/{$this->graphVersion}/{$pageId}/photos";
        $payload = [
            'url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $pageToken
        ];

        $res = $this->httpRequest($url, 'POST', $payload);
        if (!$res['success']) {
            return ['success' => false, 'error' => $res['error']];
        }

        $data = json_decode($res['data'], true);
        if (empty($data['id']) && empty($data['post_id'])) {
            $errorMsg = $data['error']['message'] ?? 'Publishing image to Facebook failed';
            return ['success' => false, 'error' => $errorMsg];
        }

        $postId = $data['post_id'] ?? $data['id'];

        return [
            'success' => true,
            'post_id' => $postId,
            'post_url' => 'https://facebook.com/' . $postId
        ];
    }

    


    public function publishVideo(string $pageId, string $pageToken, string $videoUrl, string $title, string $description): array
    {
        $url = "https://graph-video.facebook.com/{$this->graphVersion}/{$pageId}/videos";
        $payload = [
            'file_url' => $videoUrl,
            'title' => $title,
            'description' => $description,
            'access_token' => $pageToken
        ];

        $res = $this->httpRequest($url, 'POST', $payload);
        if (!$res['success']) {
            return ['success' => false, 'error' => $res['error']];
        }

        $data = json_decode($res['data'], true);
        if (empty($data['id'])) {
            $errorMsg = $data['error']['message'] ?? 'Publishing video to Facebook failed';
            return ['success' => false, 'error' => $errorMsg];
        }

        return [
            'success' => true,
            'post_id' => $data['id'],
            'post_url' => 'https://facebook.com/' . $data['id']
        ];
    }

    


    private function httpRequest(string $url, string $method = 'GET', array $params = []): array
    {
        $ch = curl_init();

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => $curlError, 'http_code' => $httpCode];
        }

        if ($httpCode >= 400) {
            $decoded = json_decode($response, true);
            $msg = $decoded['error']['message'] ?? "HTTP Error {$httpCode}";
            return ['success' => false, 'error' => $msg, 'http_code' => $httpCode, 'data' => $response];
        }

        return ['success' => true, 'data' => $response, 'http_code' => $httpCode];
    }
}
