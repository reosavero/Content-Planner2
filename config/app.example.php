<?php
/**
 * app.example.php — TEMPLATE konfigurasi aplikasi.
 *
 * Salin file ini menjadi `app.php` lalu isi nilai kredensialmu sendiri:
 *     copy config/app.example.php config/app.php
 *
 * File `app.php` asli TIDAK di-commit ke git (ada di .gitignore)
 * karena berisi kredensial rahasia (SMTP app password, OAuth secret, dll).
 * Semua nilai kredensial wajib diisi sebelum fitur SMTP / Google Drive dipakai.
 */

define('APP_NAME', 'Content Planner');
define('APP_VERSION', '2.1.1');
define('APP_ENV', 'development');

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

define('BASE_URL', $baseUrl);
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

define('CONFIG_PATH', BASE_PATH . 'config' . DIRECTORY_SEPARATOR);
define('CONTROLLERS_PATH', BASE_PATH . 'controllers' . DIRECTORY_SEPARATOR);
define('MODELS_PATH', BASE_PATH . 'models' . DIRECTORY_SEPARATOR);
define('VIEWS_PATH', BASE_PATH . 'views' . DIRECTORY_SEPARATOR);
define('HELPERS_PATH', BASE_PATH . 'helpers' . DIRECTORY_SEPARATOR);
define('MIDDLEWARE_PATH', BASE_PATH . 'middleware' . DIRECTORY_SEPARATOR);
define('SERVICES_PATH', BASE_PATH . 'services' . DIRECTORY_SEPARATOR);
define('ROUTES_PATH', BASE_PATH . 'routes' . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', BASE_PATH . 'assets' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', BASE_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('LOGS_PATH', BASE_PATH . 'logs' . DIRECTORY_SEPARATOR);

date_default_timezone_set('Asia/Jakarta');

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 86400);

ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '256M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

// ===== Google Drive (isi kredensialmu sendiri) =====
define('GOOGLE_DRIVE_CLIENT_ID', getenv('GOOGLE_DRIVE_CLIENT_ID') ?: '');
define('GOOGLE_DRIVE_CLIENT_SECRET', getenv('GOOGLE_DRIVE_CLIENT_SECRET') ?: '');
define('GOOGLE_DRIVE_REDIRECT_URI', BASE_URL . '/oauth_callback.php');
define('GOOGLE_DRIVE_FOLDER_ID', getenv('GOOGLE_DRIVE_FOLDER_ID') ?: '');
define('GOOGLE_DRIVE_OAUTH_TOKEN', CONFIG_PATH . 'google-drive-oauth.json');

// ===== SMTP (isi email & app password-mu sendiri) =====
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', (int) (getenv('SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: '');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Content Planner TVRI');
