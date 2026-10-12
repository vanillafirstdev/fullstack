<?php
// app/bootstrap.php — every request starts here, via public/index.php.
// Order matters: config, then errors, then classes, then headers and session.
declare(strict_types=1);

if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    exit('This app requires PHP 8.1 or newer. This server runs PHP ' . PHP_VERSION . '.');
}

define('APP_ROOT', dirname(__DIR__));
define('STORAGE_PATH', APP_ROOT . '/storage');
define('VIEW_PATH', __DIR__ . '/views');

$config = require __DIR__ . '/config.php';
define('APP_ENV', $config['env']);
define('DB_PATH', $config['db_path']);

// Errors: shown while developing, logged (never shown) in production.
// A stack trace on a live site tells an attacker your file paths and queries.
error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/app.log');

// Plain functions used everywhere: e(), input(), redirect(), base_href().
require __DIR__ . '/helpers.php';

// No Composer: classes load by name from app/.
// Router → app/Router.php, Controllers\HomeController → app/Controllers/HomeController.php
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

if (PHP_SAPI !== 'cli') {
    Http::sendSecurityHeaders();
    Session::start();
}
