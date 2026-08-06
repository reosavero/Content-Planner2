<?php





class YouTubeService
{
    private string $clientId;
    private string $clientSecret;
    private string $apiKey;
    private string $redirectUri;

    public function __construct()
    {
        $this->clientId = YT_CLIENT_ID;
        $this->clientSecret = YT_CLIENT_SECRET;
        $this->apiKey = YT_API_KEY;
        $this->redirectUri = YT_REDIRECT_URI;
    }

    


    public function getAuthorizationUrl(string $state): string
    {
        if (empty($this->clientId)) {
            return '';
        }

        $scopes = [
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube.readonly',
            'https://www.googleapis.com/auth/userinfo.profile'
        ];

        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state
        ];

        return "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
    }

    


    public function handleCallback(string $code): array
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            return [
                'success' => false,
                'error' => 'Credential YouTube (YT_CLIENT_ID / YT_CLIENT_SECRET) belum dikonfigurasi pada config/social.php.'
            ];
        }

        $tokenUrl = "https://oauth2.googleapis.com/token";
        $postData = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
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
            $errorMsg = $data['error_description'] ?? $data['error'] ?? 'Gagal menukar kode otorisasi Google YouTube.';
            return ['success' => false, 'error' => $errorMsg];
        }

        $accessToken = $data['access_token'];
        $refreshToken = $data['refresh_token'] ?? null;
        $expiresIn = $data['expires_in'] ?? 3600;

        
        $channelInfo = $this->getChannelInfo($accessToken);

        return [
            'success' => true,
            'account' => [
                'account_id' => $channelInfo['id'] ?? 'yt_channel',
                'account_name' => $channelInfo['title'] ?? 'Channel TVRI Jawa Timur',
                'account_username' => $channelInfo['customUrl'] ?? $channelInfo['title'] ?? 'TVRI Jatim',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_expires_at' => date('Y-m-d H:i:s', time() + $expiresIn)
            ]
        ];
    }

    


    private function getChannelInfo(string $accessToken): array
    {
        
        $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet&mine=true";
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
        if (!empty($json['items'][0])) {
            $item = $json['items'][0];
            return [
                'id' => $item['id'],
                'title' => $item['snippet']['title'] ?? 'Channel YouTube',
                'customUrl' => $item['snippet']['customUrl'] ?? null
            ];
        }

        
        $userUrl = "https://www.googleapis.com/oauth2/v2/userinfo";
        $ch2 = curl_init($userUrl);
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken
            ],
            CURLOPT_TIMEOUT => 15
        ]);

        $res2 = curl_exec($ch2);
        curl_close($ch2);
        $userJson = json_decode($res2, true);

        if (!empty($userJson['id']) || !empty($userJson['email'])) {
            return [
                'id' => $userJson['id'] ?? ('yt_' . time()),
                'title' => $userJson['name'] ?? $userJson['email'] ?? 'Akun YouTube TVRI',
                'customUrl' => $userJson['email'] ?? null
            ];
        }

        return [
            'id' => 'yt_' . time(),
            'title' => 'Channel YouTube TVRI Jatim',
            'customUrl' => 'tvrijatim'
        ];
    }

    


    public function publishVideo(string $channelId, string $accessToken, ?string $videoPath, string $title, string $description = ''): array
    {
        if (empty($videoPath)) {
            return [
                'success' => false,
                'error' => 'YouTube membutuhkan file video untuk diupload.'
            ];
        }

        
        $fileContent = null;
        if (file_exists($videoPath)) {
            $fileContent = file_get_contents($videoPath);
        } else {
            
            $fileContent = @file_get_contents($videoPath);
        }

        if (empty($fileContent)) {
            return ['success' => false, 'error' => 'Gagal membaca file video untuk upload ke YouTube.'];
        }

        $url = "https://www.googleapis.com/upload/youtube/v3/videos?uploadType=multipart&part=snippet,status";

        $metadata = [
            'snippet' => [
                'title' => mb_substr($title, 0, 100),
                'description' => $description,
                'categoryId' => '22' 
            ],
            'status' => [
                'privacyStatus' => 'public'
            ]
        ];

        $boundary = '---------------------------' . microtime(true);
        $delimiter = "--" . $boundary;
        $closeDelimiter = "--" . $boundary . "--";

        $postData = $delimiter . "\r\n";
        $postData .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $postData .= json_encode($metadata) . "\r\n";
        $postData .= $delimiter . "\r\n";
        $postData .= "Content-Type: video/mp4\r\n";
        $postData .= "Content-Transfer-Encoding: binary\r\n\r\n";
        $postData .= $fileContent . "\r\n";
        $postData .= $closeDelimiter;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: multipart/related; boundary=' . $boundary,
                'Content-Length: ' . strlen($postData)
            ],
            CURLOPT_TIMEOUT => 300 
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);

        if ($httpCode === 200 && isset($json['id'])) {
            $videoId = $json['id'];
            return [
                'success' => true,
                'post_id' => $videoId,
                'post_url' => 'https://www.youtube.com/watch?v=' . $videoId
            ];
        }

        $errorMsg = $json['error']['message'] ?? 'Gagal mengupload video ke YouTube API (HTTP ' . $httpCode . ')';
        return ['success' => false, 'error' => $errorMsg, 'http_code' => $httpCode];
    }
}
