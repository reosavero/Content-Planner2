<?php




spl_autoload_register(function (string $class) {
    
    $controllerFile = CONTROLLERS_PATH . $class . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return;
    }

    
    $modelFile = MODELS_PATH . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return;
    }

    
    $helperFile = HELPERS_PATH . $class . '.php';
    if (file_exists($helperFile)) {
        require_once $helperFile;
        return;
    }

    
    $middlewareFile = MIDDLEWARE_PATH . $class . '.php';
    if (file_exists($middlewareFile)) {
        require_once $middlewareFile;
        return;
    }
});
