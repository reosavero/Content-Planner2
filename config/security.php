<?php





define('CSRF_TOKEN_NAME', '_csrf_token');
define('CSRF_TOKEN_EXPIRY', 3600); 



define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_MIXED_CASE', false);
define('PASSWORD_REQUIRE_NUMBERS', false);
define('PASSWORD_REQUIRE_SPECIAL', false);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12);


define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15); 
define('LOGIN_ATTEMPT_WINDOW', 15); 


define('SESSION_LIFETIME', 86400); 
define('SESSION_REGENERATE_INTERVAL', 1800); 


define('ENCRYPTION_KEY', 'change-this-to-a-random-32-byte-key!');
define('ENCRYPTION_METHOD', 'aes-256-cbc');


define('SECURITY_HEADERS', serialize([
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
]));
