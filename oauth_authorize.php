<?php









require_once __DIR__ . '/config/app.php';

if (GOOGLE_DRIVE_CLIENT_ID === 'ISI_CLIENT_ID_ANDA') {
    http_response_code(500);
    exit('<h3 style="font-family:sans-serif;color:#c62828;">Konfigurasi belum lengkap</h3>'
        . '<p style="font-family:sans-serif;">Isi <b>GOOGLE_DRIVE_CLIENT_ID</b> dan <b>GOOGLE_DRIVE_CLIENT_SECRET</b> di <code>config/app.php</code> terlebih dahulu.</p>');
}

if (isset($_GET['info']) || isset($_GET['debug'])) {
    echo '<div style="font-family:sans-serif;max-width:600px;margin:50px auto;padding:24px;border:1px solid #e0e0e0;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">';
    echo '<h3 style="color:#1a237e;margin-top:0;">Info Konfigurasi OAuth Google Drive</h3>';
    echo '<p><b>Client ID:</b> <code>' . htmlspecialchars(GOOGLE_DRIVE_CLIENT_ID) . '</code></p>';
    echo '<p><b>Redirect URI saat ini:</b> <code style="background:#f5f5f5;padding:4px 8px;border-radius:4px;color:#c62828;">' . htmlspecialchars(GOOGLE_DRIVE_REDIRECT_URI) . '</code></p>';
    echo '<p style="color:#555;font-size:14px;">Pastikan <b>Redirect URI saat ini</b> di atas sudah didaftarkan persis di <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a> pada bagian <i>Authorized redirect URIs</i> Client ID Anda.</p>';
    echo '<p style="margin-top:20px;"><a href="oauth_authorize.php" style="background:#1a237e;color:#fff;padding:10px 18px;text-decoration:none;border-radius:6px;display:inline-block;">Lanjutkan Login Google &rarr;</a></p>';
    echo '</div>';
    exit;
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
