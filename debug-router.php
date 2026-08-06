<?php



ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 Router Debug - Test showLoginForm()</h2>\n";


ob_start();


require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/social.php';
require_once __DIR__ . '/helpers/Session.php';
Session::init();
require_once __DIR__ . '/helpers/Security.php';
Security::setSecurityHeaders();
require_once __DIR__ . '/helpers/Database.php';
require_once __DIR__ . '/helpers/Autoloader.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/Validator.php';
require_once __DIR__ . '/helpers/Router.php';
require_once __DIR__ . '/helpers/Controller.php';
require_once __DIR__ . '/helpers/Model.php';
require_once __DIR__ . '/routes/web.php';

echo "✅ All files loaded<br>\n";


echo "<h3>Testing AuthController::showLoginForm()</h3>\n";
try {
    require_once CONTROLLERS_PATH . 'AuthController.php';
    echo "✅ AuthController loaded<br>\n";
    
    $controller = new AuthController();
    echo "✅ AuthController instantiated<br>\n";
    
    echo "Calling showLoginForm()...<br>\n";
    
    
    ob_flush();
    
    
    $controller->showLoginForm();
    
    echo "✅ showLoginForm() completed successfully!<br>\n";
} catch (Throwable $e) {
    
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo "<div style='background:#fde8e8;border:2px solid #d92d2d;padding:20px;margin:20px 0;border-radius:8px;'>";
    echo "<h3 style='color:#d92d2d;margin-top:0;'>❌ " . get_class($e) . "</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre style='background:#f5f5f5;padding:12px;border-radius:4px;overflow:auto;max-height:400px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<h3>✅ Test Complete</h3>\n";
