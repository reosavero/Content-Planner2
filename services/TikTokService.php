<?php





class TikTokService
{
    private string $clientKey;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->clientKey = TT_CLIENT_KEY;
        $this->clientSecret = TT_CLIENT_SECRET;
        $this->redirectUri = TT_REDIRECT_URI;
    }

    


    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    


    private function generateCodeChallenge(string $codeVerifier): string
    {
        $hash = hash('sha256', $codeVerifier, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    


    public function getAuthorizationUrl(string $state): string
    {
        if (empty($this->clientKey)) {
            return '';
        }

        
        $codeVerifier = $this->generateCodeVerifier();
        Session::set('tiktok_code_verifier', $codeVerifier);
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        $params = [
            'client_key' => $this->clientKey,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'user.info.basic,video.publish,video.upload',
            'response_type' => 'code',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256'
        ];

        return "https://www.tiktok.com/v2/auth/authorize/?" . http_build_query($params);
    }

    


    public function handleCallback(string $code): array
    {
        if (empty($this->clientKey) || empty($this->clientSecret)) {
            return [
                'success' => false,
                'error' => 'Credential TikTok (TT_CLIENT_KEY / TT_CLIENT_SECRET) belum dikonfigurasi pada config/social.php.'
            ];
        }

        
        $codeVerifier = Session::get('tiktok_code_verifier', '');
        Session::remove('tiktok_code_verifier');

        
        $tokenUrl = "https://open.tiktokapis.com/v2/oauth/token/";
        $postData = [
            'client_key' => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'code_verifier' => $codeVerifier
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode !== 200 || empty($data['access_token'])) {
            $errorMsg = $data['error_description'] ?? $data['message'] ?? 'Gagal menukar kode otorisasi TikTok.';
            return ['success' => false, 'error' => $errorMsg];
        }

        $accessToken = $data['access_token'];
        $openId = $data['open_id'] ?? '';
        $expiresIn = $data['expires_in'] ?? 86400;
        $refreshToken = $data['refresh_token'] ?? null;

        
        $userInfo = $this->getUserInfo($accessToken);

        return [
            'success' => true,
            'account' => [
                'account_id' => $openId,
                'account_name' => $userInfo['display_name'] ?? 'Akun TikTok TVRI',
                'account_username' => $userInfo['display_name'] ?? $openId,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_expires_at' => date('Y-m-d H:i:s', time() + $expiresIn)
            ]
        ];
    }

    


    private function getUserInfo(string $accessToken): array
    {
        $url = "https://open.tiktokapis.com/v2/user/info/?fields=open_id,display_name,avatar_url";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken
            ],
            CURLOPT_TIMEOUT => 15
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($res, true);
        return $json['data']['user'] ?? [];
    }

    


    public function publishVideo(string $openId, string $accessToken, ?string $videoUrl, string $caption): array
    {
        if (empty($videoUrl)) {
            return [
                'success' => false,
                'error' => 'TikTok membutuhkan file video untuk publikasi.'
            ];
        }

        $url = "https://open.tiktokapis.com/v2/post/publish/video/init/";
        $body = [
            'post_info' => [
                'title' => mb_substr($caption, 0, 2200),
                'privacy_level' => 'PUBLIC_TO_EVERYONE',
                'disable_duet' => false,
                'disable_comment' => false,
                'disable_stitch' => false
            ],
            'source_info' => [
                'source' => 'PULL_FROM_URL',
                'video_url' => $videoUrl
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json; charset=UTF-8'
            ],
            CURLOPT_TIMEOUT => 60
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);

        if ($httpCode === 200 && isset($json['data']['publish_id'])) {
            $publishId = $json['data']['publish_id'];
            return [
                'success' => true,
                'post_id' => $publishId,
                'post_url' => 'https://www.tiktok.com/@' . $openId
            ];
        }

        $errorMsg = $json['error']['message'] ?? $json['message'] ?? 'Gagal mengirim video ke TikTok API (HTTP ' . $httpCode . ')';
        return ['success' => false, 'error' => $errorMsg, 'http_code' => $httpCode];
    }
}
