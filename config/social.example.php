<?php
/**
 * social.example.php — TEMPLATE konfigurasi integrasi API sosial media.
 *
 * Salin file ini menjadi `social.php` lalu isi kredensial aplikasimu sendiri:
 *     copy config/social.example.php config/social.php
 *
 * File `social.php` asli TIDAK di-commit ke git (ada di .gitignore)
 * karena berisi client secret & key asli (YouTube, TikTok, dll).
 * Nilai kredensial dibaca dari environment variable; kosongkan fallback
 * yang tidak dipakai.
 */

define('META_APP_ID', getenv('META_APP_ID') ?: '');
define('META_APP_SECRET', getenv('META_APP_SECRET') ?: '');
define('META_GRAPH_VERSION', 'v18.0');

define('FB_APP_ID', META_APP_ID);
define('FB_APP_SECRET', META_APP_SECRET);
define('FB_GRAPH_VERSION', META_GRAPH_VERSION);
define('FB_REDIRECT_URI', BASE_URL . '/social-media/facebook/callback');
define('IG_REDIRECT_URI', BASE_URL . '/social-media/instagram/callback');

define('YT_API_KEY', getenv('YT_API_KEY') ?: '');
define('YT_CLIENT_ID', getenv('YT_CLIENT_ID') ?: '');
define('YT_CLIENT_SECRET', getenv('YT_CLIENT_SECRET') ?: '');
define('YT_REDIRECT_URI', BASE_URL . '/social-media/youtube/callback');

define('TT_CLIENT_KEY', getenv('TT_CLIENT_KEY') ?: '');
define('TT_CLIENT_SECRET', getenv('TT_CLIENT_SECRET') ?: '');
define('TT_REDIRECT_URI', BASE_URL . '/social-media/tiktok/callback');

define('TW_API_KEY', '');
define('TW_API_SECRET', '');
define('TW_BEARER_TOKEN', '');
define('TW_CLIENT_ID', '');
define('TW_CLIENT_SECRET', '');
define('TW_REDIRECT_URI', BASE_URL . '/social-media/twitter/callback');

define('TH_CLIENT_ID', '');
define('TH_CLIENT_SECRET', '');
define('TH_REDIRECT_URI', BASE_URL . '/social-media/threads/callback');

define('AUTO_POST_INTERVAL_SECONDS', 30);
define('AUTO_POST_MAX_RETRY', 3);
define('AUTO_POST_RETRY_DELAY_MINUTES', 5);
define('AUTO_POST_BATCH_SIZE', 5);
