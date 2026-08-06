<?php





class InstagramService
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
        $this->redirectUri = IG_REDIRECT_URI;
    }

    


    public function getAuthorizationUrl(string $state): string
    {
        if (empty($this->appId)) {
            return '';
        }

        $permissions = [
            'instagram_basic',
            'instagram_content_publish',
            'pages_show_list',
            'pages_read_engagement',
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
                'error' => 'Credential Meta (META_APP_ID / META_APP_SECRET) belum dikonfigurasi.'
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
            return ['success' => false, 'error' => 'Gagal terhubung ke Meta: ' . $response['error']];
        }

        $data = json_decode($response['data'], true);
        if (empty($data['access_token'])) {
            $errorMsg = $data['error']['message'] ?? 'Gagal mendapatkan access token';
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

        
        $accountsUrl = "https://graph.facebook.com/{$this->graphVersion}/me/accounts?fields=name,access_token,instagram_business_account{id,username,name,profile_picture_url}&access_token=" . urlencode($userToken);
        $accountsResponse = $this->httpRequest($accountsUrl);

        if (!$accountsResponse['success']) {
            return ['success' => false, 'error' => 'Gagal mengambil akun Instagram: ' . $accountsResponse['error']];
        }

        $accountsData = json_decode($accountsResponse['data'], true);
        if (empty($accountsData['data'])) {
            return ['success' => false, 'error' => 'Tidak ada Facebook Page / Instagram Business account yang ditemukan.'];
        }

        $igAccount = null;
        $pageToken = $userToken;

        foreach ($accountsData['data'] as $page) {
            if (!empty($page['instagram_business_account'])) {
                $igAccount = $page['instagram_business_account'];
                $pageToken = $page['access_token'] ?? $userToken;
                break;
            }
        }

        if (!$igAccount) {
            return [
                'success' => false,
                'error' => 'Akun Instagram TVRI belum dikoneksikan ke Facebook Page sebagai Akun Profesional/Bisnis.'
            ];
        }

        return [
            'success' => true,
            'account' => [
                'account_id' => $igAccount['id'],
                'account_name' => $igAccount['name'] ?? $igAccount['username'],
                'account_username' => '@' . ltrim($igAccount['username'], '@'),
                'access_token' => $pageToken,
                'refresh_token' => $userToken,
                'token_expires_at' => date('Y-m-d H:i:s', time() + 5184000) 
            ]
        ];
    }

    


    public function publishImage(string $igAccountId, string $accessToken, string $imageUrl, string $caption): array
    {
        
        $containerUrl = "https://graph.facebook.com/{$this->graphVersion}/{$igAccountId}/media";
        $containerPayload = [
            'image_url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $accessToken
        ];

        $res = $this->httpRequest($containerUrl, 'POST', $containerPayload);
        if (!$res['success']) {
            return ['success' => false, 'error' => 'Gagal membuat container media Instagram: ' . $res['error']];
        }

        $containerData = json_decode($res['data'], true);
        if (empty($containerData['id'])) {
            $errorMsg = $containerData['error']['message'] ?? 'Gagal membuat container media Instagram';
            return ['success' => false, 'error' => $errorMsg];
        }

        $creationId = $containerData['id'];

        
        $statusCheck = $this->waitForContainer($creationId, $accessToken);
        if (!$statusCheck['success']) {
            return ['success' => false, 'error' => $statusCheck['error']];
        }

        
        $publishUrl = "https://graph.facebook.com/{$this->graphVersion}/{$igAccountId}/media_publish";
        $publishPayload = [
            'creation_id' => $creationId,
            'access_token' => $accessToken
        ];

        $pubRes = $this->httpRequest($publishUrl, 'POST', $publishPayload);
        if (!$pubRes['success']) {
            return ['success' => false, 'error' => 'Gagal mempublikasikan media Instagram: ' . $pubRes['error']];
        }

        $pubData = json_decode($pubRes['data'], true);
        if (empty($pubData['id'])) {
            $errorMsg = $pubData['error']['message'] ?? 'Publishing to Instagram failed';
            return ['success' => false, 'error' => $errorMsg];
        }

        return [
            'success' => true,
            'post_id' => $pubData['id'],
            'post_url' => 'https://instagram.com/p/' . $pubData['id']
        ];
    }

    


    public function publishVideo(string $igAccountId, string $accessToken, string $videoUrl, string $caption): array
    {
        
        $containerUrl = "https://graph.facebook.com/{$this->graphVersion}/{$igAccountId}/media";
        $containerPayload = [
            'media_type' => 'REELS',
            'video_url' => $videoUrl,
            'caption' => $caption,
            'access_token' => $accessToken
        ];

        $res = $this->httpRequest($containerUrl, 'POST', $containerPayload);
        if (!$res['success']) {
            return ['success' => false, 'error' => 'Gagal membuat container video Instagram: ' . $res['error']];
        }

        $containerData = json_decode($res['data'], true);
        if (empty($containerData['id'])) {
            $errorMsg = $containerData['error']['message'] ?? 'Gagal membuat container video Instagram';
            return ['success' => false, 'error' => $errorMsg];
        }

        $creationId = $containerData['id'];

        
        $statusCheck = $this->waitForContainer($creationId, $accessToken, 15);
        if (!$statusCheck['success']) {
            return ['success' => false, 'error' => $statusCheck['error']];
        }

        
        $publishUrl = "https://graph.facebook.com/{$this->graphVersion}/{$igAccountId}/media_publish";
        $publishPayload = [
            'creation_id' => $creationId,
            'access_token' => $accessToken
        ];

        $pubRes = $this->httpRequest($publishUrl, 'POST', $publishPayload);
        if (!$pubRes['success']) {
            return ['success' => false, 'error' => 'Gagal mempublikasikan video Instagram: ' . $pubRes['error']];
        }

        $pubData = json_decode($pubRes['data'], true);
        if (empty($pubData['id'])) {
            $errorMsg = $pubData['error']['message'] ?? 'Publishing video to Instagram failed';
            return ['success' => false, 'error' => $errorMsg];
        }

        return [
            'success' => true,
            'post_id' => $pubData['id'],
            'post_url' => 'https://instagram.com/p/' . $pubData['id']
        ];
    }

    


    private function waitForContainer(string $containerId, string $accessToken, int $maxRetries = 10): array
    {
        $statusUrl = "https://graph.facebook.com/{$this->graphVersion}/{$containerId}?fields=status_code,status&access_token=" . urlencode($accessToken);

        for ($i = 0; $i < $maxRetries; $i++) {
            $res = $this->httpRequest($statusUrl);
            if ($res['success']) {
                $data = json_decode($res['data'], true);
                $statusCode = $data['status_code'] ?? '';

                if ($statusCode === 'FINISHED') {
                    return ['success' => true];
                }

                if ($statusCode === 'ERROR') {
                    $errorMsg = $data['status'] ?? 'Pemrosesan media Instagram gagal pada server Meta.';
                    return ['success' => false, 'error' => $errorMsg];
                }
            }

            sleep(2); 
        }

        return ['success' => false, 'error' => 'Timeout: Pemrosesan media Instagram membutuhkan waktu terlalu lama.'];
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
