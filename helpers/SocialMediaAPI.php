<?php










class SocialMediaAPI
{
    






    public static function post(array $planning, array $account): array
    {
        $platform = $account['platform_slug'] ?? '';
        
        return match ($platform) {
            'facebook' => self::postToFacebook($planning, $account),
            'instagram' => self::postToInstagram($planning, $account),
            'youtube' => self::postToYouTube($planning, $account),
            'tiktok' => self::postToTikTok($planning, $account),
            'twitter' => self::postToTwitter($planning, $account),
            'threads' => self::postToThreads($planning, $account),
            default => ['success' => false, 'error' => 'Platform tidak dikenal: ' . $platform]
        };
    }

    


    private static function postToFacebook(array $planning, array $account): array
    {
        $accessToken = Security::decrypt($account['access_token'] ?? '');
        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Access token tidak valid atau kadaluarsa'];
        }

        $pageId = $account['account_id'];
        $caption = $planning['caption'] ?? $planning['judul'] ?? '';
        
        
        if (!empty($planning['hashtag_text'])) {
            $caption .= "\n\n" . $planning['hashtag_text'];
        }

        $mediaPath = null;
        if (!empty($planning['media_path'])) {
            $mediaPath = BASE_PATH . $planning['media_path'];
        }

        $apiVersion = 'v18.0';
        $endpoint = "https://graph.facebook.com/{$apiVersion}/{$pageId}/";

        try {
            $mediaType = $planning['media_type'] ?? 'text';

            if ($mediaType === 'image' && $mediaPath && file_exists($mediaPath)) {
                
                $data = [
                    'url' => BASE_URL . '/' . $planning['media_path'],
                    'caption' => $caption,
                    'access_token' => $accessToken,
                ];
                
                if (!empty($planning['media_thumbnail'])) {
                    $data['thumb'] = BASE_URL . '/' . $planning['media_thumbnail'];
                }

                $ch = curl_init($endpoint . 'photos');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query($data),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $result = json_decode($response, true);
                
                if ($httpCode === 200 && isset($result['id'])) {
                    return [
                        'success' => true,
                        'post_id' => $result['id'],
                        'post_url' => "https://facebook.com/{$result['id']}",
                    ];
                }

                return ['success' => false, 'error' => $result['error']['message'] ?? 'Gagal posting ke Facebook'];

            } elseif (in_array($mediaType, ['video', 'reels']) && $mediaPath && file_exists($mediaPath)) {
                
                $data = [
                    'title' => $planning['judul'] ?? '',
                    'description' => $caption,
                    'file_url' => BASE_URL . '/' . $planning['media_path'],
                    'access_token' => $accessToken,
                ];

                $ch = curl_init($endpoint . 'videos');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query($data),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 300,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $result = json_decode($response, true);
                
                if ($httpCode === 200 && isset($result['id'])) {
                    return [
                        'success' => true,
                        'post_id' => $result['id'],
                        'post_url' => "https://facebook.com/{$result['id']}",
                    ];
                }

                return ['success' => false, 'error' => $result['error']['message'] ?? 'Gagal upload video ke Facebook'];

            } else {
                
                $data = [
                    'message' => $caption,
                    'access_token' => $accessToken,
                ];

                $ch = curl_init($endpoint . 'feed');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query($data),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $result = json_decode($response, true);
                
                if ($httpCode === 200 && isset($result['id'])) {
                    return [
                        'success' => true,
                        'post_id' => $result['id'],
                        'post_url' => "https://facebook.com/{$result['id']}",
                    ];
                }

                return ['success' => false, 'error' => $result['error']['message'] ?? 'Gagal posting status ke Facebook'];
            }

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }

    


    private static function postToInstagram(array $planning, array $account): array
    {
        $accessToken = Security::decrypt($account['access_token'] ?? '');
        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Access token Instagram tidak valid'];
        }

        $igUserId = $account['account_id'];
        $caption = $planning['caption'] ?? $planning['judul'] ?? '';
        if (!empty($planning['hashtag_text'])) {
            $caption .= "\n\n" . $planning['hashtag_text'];
        }

        $mediaUrl = !empty($planning['media_path']) ? BASE_URL . '/' . $planning['media_path'] : null;
        $apiVersion = 'v18.0';
        $mediaType = $planning['media_type'] ?? 'image';

        try {
            
            if ($mediaType === 'image' || $mediaType === 'feed') {
                $endpoint = "https://graph.facebook.com/{$apiVersion}/{$igUserId}/media";
                $data = [
                    'image_url' => $mediaUrl,
                    'caption' => $caption,
                    'access_token' => $accessToken,
                ];
            } elseif (in_array($mediaType, ['video', 'reels'])) {
                $endpoint = "https://graph.facebook.com/{$apiVersion}/{$igUserId}/media";
                $data = [
                    'media_type' => 'VIDEO',
                    'video_url' => $mediaUrl,
                    'caption' => $caption,
                    'access_token' => $accessToken,
                ];
            } else {
                
                return ['success' => false, 'error' => 'Carousel belum didukung untuk Instagram'];
            }

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if (!isset($result['id'])) {
                return ['success' => false, 'error' => $result['error']['message'] ?? 'Gagal membuat container Instagram'];
            }

            $containerId = $result['id'];

            
            sleep(2); 
            
            $publishEndpoint = "https://graph.facebook.com/{$apiVersion}/{$igUserId}/media_publish";
            $publishData = [
                'creation_id' => $containerId,
                'access_token' => $accessToken,
            ];

            $ch = curl_init($publishEndpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($publishData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $publishResult = json_decode($response, true);

            if ($httpCode === 200 && isset($publishResult['id'])) {
                return [
                    'success' => true,
                    'post_id' => $publishResult['id'],
                    'post_url' => "https://instagram.com/p/{$publishResult['id']}/",
                ];
            }

            return ['success' => false, 'error' => $publishResult['error']['message'] ?? 'Gagal publish ke Instagram'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }

    


    private static function postToYouTube(array $planning, array $account): array
    {
        $accessToken = Security::decrypt($account['access_token'] ?? '');
        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Access token YouTube tidak valid'];
        }

        $mediaPath = !empty($planning['media_path']) ? BASE_PATH . $planning['media_path'] : null;
        if (!$mediaPath || !file_exists($mediaPath)) {
            return ['success' => false, 'error' => 'File video tidak ditemukan'];
        }

        try {
            $client = new \Google\Client();
            $client->setAccessToken($accessToken);
            $service = new \Google\Service\YouTube($client);

            $video = new \Google\Service\YouTube\Video();
            $video->setSnippet(new \Google\Service\YouTube\VideoSnippet());
            $video->getSnippet()->setTitle($planning['judul'] ?? 'Video TVRI Jawa Timur');
            $video->getSnippet()->setDescription(($planning['caption'] ?? '') . "\n\n" . ($planning['hashtag_text'] ?? ''));
            
            
            $video->getSnippet()->setCategoryId('25'); 

            
            $video->setStatus(new \Google\Service\YouTube\VideoStatus());
            $video->getStatus()->setPrivacyStatus('public');
            $video->getStatus()->setSelfDeclaredMadeForKids(false);

            
            $chunkSize = 1 * 1024 * 1024; 
            $client->setDefer(true);
            
            $request = $service->videos->insert('snippet,status', $video);
            
            $media = new \Google\Http\MediaFileUpload(
                $client,
                $request,
                mime_content_type($mediaPath),
                null,
                true,
                $chunkSize
            );
            $media->setFileSize(filesize($mediaPath));

            $status = false;
            $handle = fopen($mediaPath, 'rb');
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                $status = $media->nextChunk($chunk);
            }
            fclose($handle);

            $client->setDefer(false);

            if ($status && isset($status['id'])) {
                return [
                    'success' => true,
                    'post_id' => $status['id'],
                    'post_url' => "https://youtube.com/watch?v={$status['id']}",
                ];
            }

            return ['success' => false, 'error' => 'Gagal upload ke YouTube'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'YouTube Error: ' . $e->getMessage()];
        }
    }

    


    private static function postToTikTok(array $planning, array $account): array
    {
        $accessToken = Security::decrypt($account['access_token'] ?? '');
        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Access token TikTok tidak valid'];
        }

        $mediaPath = !empty($planning['media_path']) ? BASE_PATH . $planning['media_path'] : null;
        if (!$mediaPath || !file_exists($mediaPath)) {
            return ['success' => false, 'error' => 'File video tidak ditemukan'];
        }

        try {
            
            $openId = $account['account_id'];
            
            
            $initUrl = "https://open.tiktokapis.com/v2/video/upload/init/";
            $headers = [
                "Authorization: Bearer {$accessToken}",
                "Content-Type: application/json; charset=UTF-8",
            ];

            $initData = json_encode([
                'source_info' => [
                    'source' => 'FILE_UPLOAD',
                    'video_size' => filesize($mediaPath),
                    'chunk_size' => 5 * 1024 * 1024, 
                    'total_chunk_count' => ceil(filesize($mediaPath) / (5 * 1024 * 1024)),
                ],
            ]);

            $ch = curl_init($initUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $initData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);
            
            if (!isset($result['data']['upload_url'])) {
                return ['success' => false, 'error' => $result['error']['message'] ?? 'Gagal inisialisasi upload TikTok'];
            }

            $uploadUrl = $result['data']['upload_url'];
            
            
            $ch = curl_init($uploadUrl);
            curl_setopt_array($ch, [
                CURLOPT_PUT => true,
                CURLOPT_INFILE => fopen($mediaPath, 'rb'),
                CURLOPT_INFILESIZE => filesize($mediaPath),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return ['success' => false, 'error' => 'Gagal upload video ke TikTok'];
            }

            
            $postUrl = "https://open.tiktokapis.com/v2/video/publish/";
            $postData = json_encode([
                'post_info' => [
                    'title' => $planning['judul'] ?? '',
                    'description' => $planning['caption'] ?? '',
                    'disable_comment' => false,
                    'disable_duet' => false,
                    'disable_stitch' => false,
                    'brand_organic_opt_in' => true,
                ],
                'source_info' => [
                    'source' => 'FILE_UPLOAD',
                ],
            ]);

            $ch = curl_init($postUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $postResult = json_decode($response, true);

            if (isset($postResult['data']['publish_id'])) {
                return [
                    'success' => true,
                    'post_id' => $postResult['data']['publish_id'],
                    'post_url' => "https://tiktok.com/@{$account['account_username']}/video/{$postResult['data']['publish_id']}",
                ];
            }

            return ['success' => false, 'error' => $postResult['error']['message'] ?? 'Gagal publish ke TikTok'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'TikTok Error: ' . $e->getMessage()];
        }
    }

    


    private static function postToTwitter(array $planning, array $account): array
    {
        $accessToken = Security::decrypt($account['access_token'] ?? '');
        $apiKey = Security::decrypt($account['api_key'] ?? '');
        $apiSecret = Security::decrypt($account['api_secret'] ?? '');
        
        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Access token Twitter tidak valid'];
        }

        try {
            $text = $planning['caption'] ?? $planning['judul'] ?? '';
            if (!empty($planning['hashtag_text'])) {
                $text .= "\n\n" . $planning['hashtag_text'];
            }

            
            $text = mb_substr($text, 0, 4000);

            $mediaPath = !empty($planning['media_path']) ? BASE_PATH . $planning['media_path'] : null;
            $mediaIds = [];

            
            if ($mediaPath && file_exists($mediaPath)) {
                $mediaEndpoint = 'https://upload.twitter.com/1.1/media/upload.json';
                
                $ch = curl_init($mediaEndpoint);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => ["Authorization: Bearer {$accessToken}"],
                    CURLOPT_POSTFIELDS => ['media' => new CURLFile($mediaPath)],
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $mediaResult = json_decode($response, true);
                if ($httpCode === 200 && isset($mediaResult['media_id_string'])) {
                    $mediaIds[] = $mediaResult['media_id_string'];
                }
            }

            
            $postData = ['text' => $text];
            if (!empty($mediaIds)) {
                $postData['media'] = ['media_ids' => $mediaIds];
            }

            $tweetEndpoint = 'https://api.twitter.com/2/tweets';
            $ch = curl_init($tweetEndpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$accessToken}",
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($postData),
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($httpCode === 201 && isset($result['data']['id'])) {
                return [
                    'success' => true,
                    'post_id' => $result['data']['id'],
                    'post_url' => "https://x.com/{$account['account_username']}/status/{$result['data']['id']}",
                ];
            }

            return ['success' => false, 'error' => $result['detail'] ?? ($result['title'] ?? 'Gagal posting ke Twitter')];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Twitter Error: ' . $e->getMessage()];
        }
    }

    


    private static function postToThreads(array $planning, array $account): array
    {
        $accessToken = Security::decrypt($account['access_token'] ?? '');
        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Access token Threads tidak valid'];
        }

        try {
            $text = $planning['caption'] ?? $planning['judul'] ?? '';
            $text = mb_substr($text, 0, 500); 

            $userId = $account['account_id'];
            $apiVersion = 'v1.0';

            
            $endpoint = "https://graph.threads.net/{$apiVersion}/{$userId}/threads";
            $data = [
                'media_type' => 'TEXT',
                'text' => $text,
                'access_token' => $accessToken,
            ];

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);
            
            if (!isset($result['id'])) {
                return ['success' => false, 'error' => $result['error']['message'] ?? 'Gagal membuat Threads container'];
            }

            $containerId = $result['id'];
            sleep(2);

            
            $publishEndpoint = "https://graph.threads.net/{$apiVersion}/{$userId}/threads_publish";
            $publishData = [
                'creation_id' => $containerId,
                'access_token' => $accessToken,
            ];

            $ch = curl_init($publishEndpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($publishData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $publishResult = json_decode($response, true);

            if ($httpCode === 200 && isset($publishResult['id'])) {
                return [
                    'success' => true,
                    'post_id' => $publishResult['id'],
                    'post_url' => "https://threads.net/{$account['account_username']}/post/{$publishResult['id']}",
                ];
            }

            return ['success' => false, 'error' => $publishResult['error']['message'] ?? 'Gagal publish ke Threads'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Threads Error: ' . $e->getMessage()];
        }
    }

    


    public static function refreshToken(array $account): array
    {
        $platform = $account['platform_slug'] ?? '';
        
        return match ($platform) {
            'facebook', 'instagram' => self::refreshFacebookToken($account),
            'youtube' => self::refreshYouTubeToken($account),
            'tiktok' => self::refreshTikTokToken($account),
            'twitter' => self::refreshTwitterToken($account),
            default => ['success' => false, 'error' => 'Platform tidak didukung']
        };
    }

    


    private static function refreshFacebookToken(array $account): array
    {
        $currentToken = Security::decrypt($account['access_token'] ?? '');
        $appId = defined('FB_APP_ID') ? FB_APP_ID : '';
        $appSecret = defined('FB_APP_SECRET') ? FB_APP_SECRET : '';

        if (empty($currentToken) || empty($appId) || empty($appSecret)) {
            return ['success' => false, 'error' => 'Konfigurasi token tidak lengkap'];
        }

        $url = "https://graph.facebook.com/v18.0/oauth/access_token?grant_type=fb_exchange_token&client_id={$appId}&client_secret={$appSecret}&fb_exchange_token={$currentToken}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['access_token'])) {
            $expiresAt = date('Y-m-d H:i:s', time() + ($result['expires_in'] ?? 5184000));
            
            Database::execute(
                "UPDATE platform_akun SET access_token = ?, token_expires_at = ?, token_status = 'active', updated_at = NOW() WHERE id = ?",
                [Security::encrypt($result['access_token']), $expiresAt, $account['id']]
            );

            return ['success' => true, 'expires_at' => $expiresAt];
        }

        return ['success' => false, 'error' => $result['error']['message'] ?? 'Gagal refresh token'];
    }

    private static function refreshYouTubeToken(array $account): array { return ['success' => false, 'error' => 'YouTube token refresh manual']; }
    private static function refreshTikTokToken(array $account): array { return ['success' => false, 'error' => 'TikTok token refresh manual']; }
    private static function refreshTwitterToken(array $account): array { return ['success' => false, 'error' => 'Twitter token refresh manual']; }
}
