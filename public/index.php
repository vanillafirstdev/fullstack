<?php
// public/index.php — the front controller. Every request that isn't a real
// file (CSS, JS, images) lands here.
declare(strict_types=1);

// PHP's built-in server: serve real files in public/ directly.
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require __DIR__ . '/../app/bootstrap.php';

$router = new Router();

// 404 and 405: JSON under /api/, the site's error page everywhere else.
$router->onError(function (int $status, string $message): void {
    if (str_starts_with(request_path(), '/api/')) {
        json_error($status, $message);
        return;
    }
    View::render('pages/error', [
        'title'   => $status === 404 ? 'Page not found' : 'Method not allowed',
        'message' => $status === 404 ? 'No page lives at this address.' : $message,
    ], $status);
});

$home = new Controllers\HomeController();
$api  = new Controllers\BookmarkController();   // the same file as the Backend Kit's

// Pages: rendered on the server, forms posted the classic way.
$router->get('/', [$home, 'index']);
$router->get('/about', [$home, 'about']);
// Not /app: a route named after a top-level folder (app, database, storage)
// hits that folder's deny-all .htaccess in a subfolder install and gets a 403.
$router->get('/client', [$home, 'client']);
$router->post('/bookmarks', [$home, 'store']);

// JSON API: what /client's JavaScript calls. Same routes as the Backend Kit.
$router->get('/api/bookmarks', [$api, 'index']);
$router->post('/api/bookmarks', [$api, 'store']);
$router->get('/api/bookmarks/{id}', [$api, 'show']);
$router->patch('/api/bookmarks/{id}', [$api, 'update']);
$router->delete('/api/bookmarks/{id}', [$api, 'destroy']);

$router->dispatch(request_path(), $_SERVER['REQUEST_METHOD'] ?? 'GET');
