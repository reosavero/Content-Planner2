<?php




class Security
{
    


    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    


    public static function escapeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = is_string($value) ? self::escape($value) : $value;
        }
        return $sanitized;
    }

    


    public static function sanitize(string $value): string
    {
        return trim(strip_tags($value));
    }

    


    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    


    public static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    


    public static function isStrongPassword(string $password): array
    {
        $errors = [];
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter';
        }
        if (PASSWORD_REQUIRE_MIXED_CASE && (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password))) {
            $errors[] = 'Password harus mengandung huruf besar dan kecil';
        }
        if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password harus mengandung angka';
        }
        if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password harus mengandung karakter spesial';
        }
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    


    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
    }

    


    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    


    public static function encrypt(string $data): string
    {
        $key = hash('sha256', ENCRYPTION_KEY, true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
        $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, $key, 0, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    


    public static function decrypt(string $data): string
    {
        $key = hash('sha256', ENCRYPTION_KEY, true);
        $parts = explode('::', base64_decode($data), 2);
        if (count($parts) !== 2) return '';
        [$iv, $encrypted] = $parts;
        return openssl_decrypt($encrypted, ENCRYPTION_METHOD, $key, 0, $iv);
    }

    


    public static function generateToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    


    public static function validateFile(array $file, array $allowedTypes = [], int $maxSize = 52428800): array
    {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Gagal mengupload file';
            return ['valid' => false, 'errors' => $errors];
        }

        if ($file['size'] > $maxSize) {
            $maxMB = $maxSize / 1048576;
            $errors[] = "Ukuran file maksimal {$maxMB}MB";
        }

        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'Tipe file tidak diizinkan';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $mimeType ?? null
        ];
    }

    


    public static function generateFilename(string $originalName): string
    {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        return date('Ymd_His') . '_' . self::generateToken(16) . '.' . $ext;
    }

    


    public static function setSecurityHeaders(): void
    {
        $headers = unserialize(SECURITY_HEADERS);
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }
        
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://graph.facebook.com https://graph.instagram.com https://www.googleapis.com; frame-src 'self' https://www.facebook.com https://www.youtube.com");
    }
}
