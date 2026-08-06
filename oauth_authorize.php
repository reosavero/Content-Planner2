<?php









require_once __DIR__ . '/config/app.php';

if (GOOGLE_DRIVE_CLIENT_ID === 'ISI_CLIENT_ID_ANDA') {
    http_response_code(500);
    exit('<h3 style="font-family:sans-serif;color:#c62828;">Konfigurasi belum lengkap</h3>'
        . '<p style="font-family:sans-serif;">Isi <b>GOOGLE_DRIVE_CLIENT_ID</b> dan <b>GOOGLE_DRIVE_CLIENT_SECRET</b> di <code>config/app.php</code> terlebih dahulu.</p>');
}

$params = [
    'client_id' => GOOGLE_DRIVE_CLIENT_ID,
    'redirect_uri' => GOOGLE_DRIVE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'access_type' => 'offline',
    'prompt' => 'consent',
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
