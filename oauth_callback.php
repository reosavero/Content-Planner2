<?php





require_once __DIR__ . '/config/app.php';

function h(string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

if (empty($_GET['code'])) {
    $err = $_GET['error'] ?? 'Tidak ada kode otorisasi yang diterima.';
    $errDesc = $_GET['error_description'] ?? '';
    http_response_code(400);
    exit('<h3 style="font-family:sans-serif;color:#c62828;">Otorisasi gagal</h3>'
        . '<p style="font-family:sans-serif;">' . h($err) . ' ' . h($errDesc) . '</p>'
        . '<p style="font-family:sans-serif;"><a href="oauth_authorize.php">Coba lagi</a></p>');
}

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'code' => $_GET['code'],
        'client_id' => GOOGLE_DRIVE_CLIENT_ID,
        'client_secret' => GOOGLE_DRIVE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_DRIVE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
    ]),
    CURLOPT_CAINFO => CONFIG_PATH . 'cacert.pem',
    CURLOPT_TIMEOUT => 30,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode((string)$resp, true);

if ($code !== 200 || empty($data['refresh_token'])) {
    http_response_code(500);
    exit('<h3 style="font-family:sans-serif;color:#c62828;">Gagal mendapatkan token</h3>'
        . '<p style="font-family:sans-serif;">' . h($data['error'] ?? 'error') . ': ' . h($data['error_description'] ?? ('HTTP ' . $code)) . '</p>'
        . '<p style="font-family:sans-serif;"><a href="oauth_authorize.php">Coba lagi</a></p>');
}


$saved = [
    'refresh_token' => $data['refresh_token'],
    'client_id' => GOOGLE_DRIVE_CLIENT_ID,
    'saved_at' => date('c'),
];
if (!@file_put_contents(GOOGLE_DRIVE_OAUTH_TOKEN, json_encode($saved, JSON_PRETTY_PRINT), LOCK_EX)) {
    http_response_code(500);
    exit('<h3 style="font-family:sans-serif;color:#c62828;">Gagal menyimpan token</h3>'
        . '<p style="font-family:sans-serif;">Tidak bisa menulis ke <code>' . h(GOOGLE_DRIVE_OAUTH_TOKEN) . '</code>. Periksa izin tulis folder config/.</p>');
}

echo '<div style="font-family:sans-serif;max-width:560px;margin:60px auto;padding:30px;border:1px solid #ddd;border-radius:10px;">'
    . '<h2 style="color:#1a237e;margin-top:0;">✅ Login Google Drive Berhasil</h2>'
    . '<p>Token refresh sudah tersimpan di <code>config/google-drive-oauth.json</code>.</p>'
    . '<p>Silakan kembali ke aplikasi dan coba <b>upload file lampiran / hasil pekerjaan</b> lagi.</p>'
    . '<p><a href="' . h(BASE_URL) . '/timeline">&#8592; Kembali ke Timeline</a></p>'
    . '</div>';
