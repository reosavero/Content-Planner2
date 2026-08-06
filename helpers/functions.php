<?php








function formatTanggal(string $date, string $format = 'd F Y'): string
{
    if (empty($date)) return '-';
    
    $bulan = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember',
    ];
    
    $hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];
    
    $result = date($format, strtotime($date));
    $result = str_replace(array_keys($hari), array_values($hari), $result);
    $result = str_replace(array_keys($bulan), array_values($bulan), $result);
    
    return $result;
}




function waktuLalu(string $datetime): string
{
    if (empty($datetime)) return '';
    
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';
    if ($diff < 31536000) return floor($diff / 2592000) . ' bulan lalu';
    return floor($diff / 31536000) . ' tahun lalu';
}




function formatAngka(int $number): string
{
    return number_format($number, 0, ',', '.');
}




function generateSlug(string $string): string
{
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}




function generateRandom(int $length = 16): string
{
    return bin2hex(random_bytes($length));
}




function truncateText(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length - mb_strlen($suffix)) . $suffix;
}




function formatFileSize(int $bytes): string
{
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}




function statusLabel(string $status): string
{
    $labels = [
        'draft' => '<span class="status-label status-label-draft"><i class="bi bi-pencil"></i> Draft</span>',
        'review' => '<span class="status-label status-label-review"><i class="bi bi-eye"></i> Review</span>',
        'approved' => '<span class="status-label status-label-approved"><i class="bi bi-check-circle"></i> Approved</span>',
        'scheduled' => '<span class="status-label status-label-scheduled"><i class="bi bi-clock"></i> Terjadwal</span>',
        'posting' => '<span class="status-label status-label-posting"><i class="bi bi-arrow-up-circle"></i> Posting</span>',
        'success' => '<span class="status-label status-label-success"><i class="bi bi-check-circle-fill"></i> Berhasil</span>',
        'failed' => '<span class="status-label status-label-failed"><i class="bi bi-x-circle-fill"></i> Gagal</span>',
        'revision' => '<span class="status-label status-label-revision"><i class="bi bi-arrow-counterclockwise"></i> Revisi</span>',
        'cancelled' => '<span class="status-label status-label-cancelled"><i class="bi bi-slash-circle"></i> Dibatalkan</span>',
    ];
    
    return $labels[$status] ?? '<span class="status-label" style="background:var(--gray-100);color:var(--gray-500);">' . $status . '</span>';
}





function iconClass(?string $icon, string $default = 'bi-globe'): string
{
    $icon = trim($icon ?? '');
    if (empty($icon)) {
        $icon = $default;
    }
    if (str_starts_with($icon, 'bi bi-')) {
        return $icon;
    }
    if (str_starts_with($icon, 'bi-')) {
        return 'bi ' . $icon;
    }
    return 'bi bi-' . $icon;
}




function platformIcon(string $platform, string $color = ''): string
{
    static $baseUrl = null;
    if ($baseUrl === null) {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    }
    
    $p = strtolower(trim($platform));
    $svgIcons = [
        'facebook' => '<img src="' . $baseUrl . '/assets/img/platforms/facebook.svg" alt="Facebook" style="width:16px;height:16px;vertical-align:middle;">',
        'instagram' => '<img src="' . $baseUrl . '/assets/img/platforms/instagram.svg" alt="Instagram" style="width:16px;height:16px;vertical-align:middle;">',
        'youtube' => '<img src="' . $baseUrl . '/assets/img/platforms/youtube.svg" alt="YouTube" style="width:16px;height:16px;vertical-align:middle;">',
        'tiktok' => '<img src="' . $baseUrl . '/assets/img/platforms/tiktok.svg" alt="TikTok" style="width:16px;height:16px;vertical-align:middle;">',
        'twitter' => '<img src="' . $baseUrl . '/assets/img/platforms/twitter-x.svg" alt="X" style="width:16px;height:16px;vertical-align:middle;">',
        'x' => '<img src="' . $baseUrl . '/assets/img/platforms/twitter-x.svg" alt="X" style="width:16px;height:16px;vertical-align:middle;">',
        'threads' => '<img src="' . $baseUrl . '/assets/img/platforms/threads.svg" alt="Threads" style="width:16px;height:16px;vertical-align:middle;">',
    ];
    
    return $svgIcons[$p] ?? '<i class="' . iconClass($platform) . '"></i>';
}




function debug(mixed $data): void
{
    if (APP_ENV === 'development') {
        echo '<pre style="background: #1a1d29; color: #e4e7ed; padding: 16px; border-radius: 8px; font-size: 12px; max-height: 500px; overflow: auto; margin: 10px 0;">';
        print_r($data);
        echo '</pre>';
    }
}




function jsonSafe(mixed $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}




function getClientIp(): string
{
    $ips = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ips as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return '0.0.0.0';
}
