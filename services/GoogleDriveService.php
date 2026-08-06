<?php








class GoogleDriveService
{
    private string $credentialsPath;
    private string $folderId;
    private string $tokenCachePath;
    private ?string $accessToken = null;

    public function __construct(string $credentialsPath, string $folderId)
    {
        $this->credentialsPath = $credentialsPath;
        $this->folderId = $folderId;
        $this->tokenCachePath = LOGS_PATH . 'google_drive_token.json';
    }

    


    public function uploadFromUploadedFile(array $file): array
    {
        $fileName = $file['name'] ?? 'file';
        $tmpPath = $file['tmp_name'] ?? '';
        $mimeType = !empty($file['type']) ? $file['type'] : $this->mimeFromPath($fileName);
        return $this->uploadFile($tmpPath, $fileName, $mimeType);
    }

    


    public function uploadFile(string $filePath, string $fileName, string $mimeType = 'application/octet-stream'): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException('File sementara tidak ditemukan');
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false) {
            throw new RuntimeException('Gagal membaca ukuran file');
        }

        $token = $this->getAccessToken();
        $uploadUrl = $this->initiateResumable($token, $fileName, $mimeType, $fileSize);
        $file = $this->uploadContent($uploadUrl, $filePath, $mimeType);

        try {
            $this->setAnyoneWithLink($file['id']);
        } catch (\Throwable $e) {
            error_log('GoogleDriveService: gagal set permission: ' . $e->getMessage());
        }

        return [
            'id' => $file['id'],
            'viewLink' => $file['webViewLink'] ?? ('https://drive.google.com/file/d/' . $file['id'] . '/view'),
            'name' => $file['name'] ?? $fileName,
        ];
    }

    


    public function deleteFile(string $fileId): void
    {
        $token = $this->getAccessToken();
        $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?supportsAllDrives=true');
        $this->setOptions($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    


    public static function extractFileId(string $link): ?string
    {
        if (preg_match('#/file/d/([^/]+)#', $link, $m)) {
            return $m[1];
        }
        if (preg_match('#[?&]id=([^&]+)#', $link, $m)) {
            return $m[1];
        }
        if (preg_match('#^1[A-Za-z0-9_-]{20,}$#', $link, $m)) {
            return $m[0];
        }
        return null;
    }

    public static function isDriveLink(string $url): bool
    {
        return str_contains($url, 'drive.google.com');
    }

    
    
    

    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        if (file_exists($this->tokenCachePath)) {
            $cached = json_decode((string)file_get_contents($this->tokenCachePath), true);
            if (is_array($cached) && isset($cached['access_token'], $cached['expires_at']) && $cached['expires_at'] > time() + 60) {
                $this->accessToken = $cached['access_token'];
                return $this->accessToken;
            }
        }

        $token = $this->requestAccessToken();
        @file_put_contents($this->tokenCachePath, json_encode([
            'access_token' => $token,
            'expires_at' => time() + 3540,
        ]), LOCK_EX);

        $this->accessToken = $token;
        return $token;
    }

    private function requestAccessToken(): string
    {
        if (!defined('GOOGLE_DRIVE_CLIENT_ID') || GOOGLE_DRIVE_CLIENT_ID === 'ISI_CLIENT_ID_ANDA') {
            throw new RuntimeException('OAuth Google Drive belum dikonfigurasi. Isi GOOGLE_DRIVE_CLIENT_ID & GOOGLE_DRIVE_CLIENT_SECRET di config/app.php, lalu buka ' . (defined('BASE_URL') ? BASE_URL : '') . '/oauth_authorize.php');
        }

        if (!file_exists($this->credentialsPath)) {
            throw new RuntimeException('Token OAuth Google Drive belum dibuat. Buka ' . (defined('BASE_URL') ? BASE_URL : '') . '/oauth_authorize.php untuk login dengan akun Google.');
        }

        $data = json_decode((string)file_get_contents($this->credentialsPath), true);
        if (!is_array($data) || empty($data['refresh_token'])) {
            throw new RuntimeException('Refresh token OAuth tidak ditemukan di ' . $this->credentialsPath . '. Jalankan ulang ' . (defined('BASE_URL') ? BASE_URL : '') . '/oauth_authorize.php');
        }

        $ch = curl_init('https://oauth2.googleapis.com/token');
        $this->setOptions($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'refresh_token',
                'refresh_token' => $data['refresh_token'],
                'client_id' => GOOGLE_DRIVE_CLIENT_ID,
                'client_secret' => GOOGLE_DRIVE_CLIENT_SECRET,
            ]),
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string)$resp, true);
        if ($code !== 200 || empty($data['access_token'])) {
            throw new RuntimeException(
                'Sesi Google Drive kedaluwarsa/ditolak: ' . ($data['error_description'] ?? ('HTTP ' . $code)) .
                ' — buka ' . (defined('BASE_URL') ? BASE_URL : '') . '/oauth_authorize.php untuk login ulang.'
            );
        }
        return $data['access_token'];
    }

    
    
    

    private function initiateResumable(string $token, string $fileName, string $mimeType, int $fileSize): string
    {
        $metadata = json_encode(['name' => $fileName, 'parents' => [$this->folderId]]);
        $location = null;

        $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable&fields=id,name,webViewLink,mimeType&supportsAllDrives=true');
        $this->setOptions($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json; charset=UTF-8',
                'X-Upload-Content-Type: ' . $mimeType,
                'X-Upload-Content-Length: ' . $fileSize,
            ],
            CURLOPT_POSTFIELDS => $metadata,
            CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$location) {
                if (stripos($header, 'Location:') === 0) {
                    $location = trim(substr($header, 9));
                }
                return strlen($header);
            },
            CURLOPT_TIMEOUT => 60,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($code !== 200 || !$location) {
            throw new RuntimeException(
                'Gagal memulai upload Google Drive (HTTP ' . $code . '): ' . $curlErr . ' ' . substr((string)$body, 0, 300)
            );
        }
        return $location;
    }

    private function uploadContent(string $uploadUrl, string $filePath, string $mimeType): array
    {
        $fileSize = filesize($filePath);
        $fp = fopen($filePath, 'rb');
        if ($fp === false) {
            throw new RuntimeException('Gagal membuka file untuk upload');
        }

        $ch = curl_init($uploadUrl);
        $this->setOptions($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PUT => true,
            CURLOPT_INFILE => $fp,
            CURLOPT_INFILESIZE => $fileSize,
            CURLOPT_HTTPHEADER => ['Content-Type: ' . $mimeType],
            CURLOPT_TIMEOUT => 600,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if ($code !== 200 && $code !== 201) {
            throw new RuntimeException(
                'Gagal upload konten Google Drive (HTTP ' . $code . '): ' . $curlErr . ' ' . substr((string)$body, 0, 300)
            );
        }

        $data = json_decode((string)$body, true);
        if (!is_array($data) || empty($data['id'])) {
            throw new RuntimeException('Respons upload Google Drive tidak valid');
        }
        return $data;
    }

    private function setAnyoneWithLink(string $fileId): void
    {
        $token = $this->getAccessToken();
        $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '/permissions?fields=id&supportsAllDrives=true');
        $this->setOptions($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json; charset=UTF-8',
            ],
            CURLOPT_POSTFIELDS => json_encode(['role' => 'reader', 'type' => 'anyone']),
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            throw new RuntimeException('Gagal mengatur izin file Google Drive (HTTP ' . $code . ')');
        }
    }

    
    
    

    


    private function caOption(): array
    {
        $candidates = [];
        if (defined('CONFIG_PATH')) {
            $candidates[] = CONFIG_PATH . 'cacert.pem';
        }
        $candidates[] = 'D:/xampp/apache/bin/curl-ca-bundle.crt';

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return [CURLOPT_CAINFO => $path];
            }
        }
        return [];
    }

    


    private function setOptions($ch, array $options): void
    {
        curl_setopt_array($ch, $options + $this->caOption());
    }

    private function mimeFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = [
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            'avi' => 'video/x-msvideo',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            'txt' => 'text/plain',
        ];
        return $map[$ext] ?? 'application/octet-stream';
    }
}
