<?php

session_start();

define('BASE_PATH', dirname(__DIR__));

// 1. Helpers (no class deps)
require_once BASE_PATH . '/helpers/functions.php';

// 2. Composer autoloader (Google API client, etc.)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// 3. Load .env
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

// 4. Core framework — order matters (dependency first)
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';

// 5. Models — loaded eagerly so every controller can reference any model
foreach (glob(BASE_PATH . '/app/models/*.php') as $file) {
    require_once $file;
}

// 6. Controllers — loaded eagerly after models
foreach (glob(BASE_PATH . '/app/controllers/*.php') as $file) {
    require_once $file;
}

// 7. Boot router
$router = new Router();
require_once BASE_PATH . '/routes/web.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($base_dir === '/' || $base_dir === '\\') {
    $base_dir = '';
}
if ($base_dir !== '' && strpos($uri, $base_dir) === 0) {
    $uri = substr($uri, strlen($base_dir));
}
if ($uri === '' || $uri === false) {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];
$router->route($uri, $method);
