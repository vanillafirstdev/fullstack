<?php
// app/helpers.php — small functions used everywhere.
// These names and behaviours are shared with VanillaSaaS Core on purpose:
// e(), input() and redirect() mean the same thing in every VanillaFirst project;
// json(), json_error() and json_body() are word for word the Backend Kit's.
declare(strict_types=1);

/**
 * Escape a value for HTML text or a quoted attribute.
 * Every value echoed into a template goes through e(). No exceptions:
 * a name from your own database is still user input.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Read a POST field as a string.
 * A request can send name[]=x, making $_POST['name'] an ARRAY; trim() would
 * then throw and the visitor would get a 500. Anything that isn't a plain
 * string becomes ''. Don't trim passwords: trailing spaces are valid.
 */
function input(string $key, bool $trim = true): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    return $trim ? trim($value) : $value;
}

/** Redirect and stop. 303 after a POST makes Refresh safe (Post/Redirect/Get). */
function redirect(string $to, int $status = 303): never
{
    header('Location: ' . $to, true, $status);
    exit;
}

// ---- JSON, for the API routes. Identical to the Backend Kit's. ----

/** Send $data as JSON with a status code. */
function json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}

/**
 * Every error has the same shape, so a client needs one piece of code to read them:
 *   { "error": "What went wrong", "fields": { "url": "Why this field is wrong" } }
 * "fields" only appears on validation errors (422).
 */
function json_error(int $status, string $message, array $fields = []): void
{
    $body = ['error' => $message];
    if ($fields) {
        $body['fields'] = $fields;
    }
    json($body, $status);
}

/**
 * Read the request body as a JSON object.
 * Returns the decoded array, or null after sending the error response itself.
 *
 *   $data = json_body();
 *   if ($data === null) return;
 */
function json_body(): ?array
{
    // 415: the client sent something that isn't JSON (e.g. an HTML form).
    $type = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (!str_starts_with($type, 'application/json')) {
        json_error(415, 'Send the body as JSON with the header Content-Type: application/json.');
        return null;
    }

    try {
        $data = json_decode((string) file_get_contents('php://input'), true, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException $ex) {
        json_error(400, 'The body is not valid JSON: ' . $ex->getMessage() . '.');
        return null;
    }

    // [1, 2, 3] and "text" are valid JSON but not an object with fields.
    if (!is_array($data) || array_is_list($data) && $data !== []) {
        json_error(400, 'The body must be a JSON object, like {"title": "..."}.');
        return null;
    }
    return $data;
}

// ---- Paths ----

/**
 * The folder the app is served from: '' at https://myapp.com/,
 * '/my-app' at http://localhost/my-app/ on XAMPP.
 *
 * When the root .htaccess routes a request into public/, PHP's SCRIPT_NAME
 * ends in /public but the address the visitor typed doesn't, so we drop it.
 */
function base_path(): string
{
    $scriptDir = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/'))), '/');
    $requested = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

    $inside = static fn (string $dir): bool =>
        $dir === '' || $requested === $dir || str_starts_with($requested, $dir . '/');

    if (!$inside($scriptDir) && str_ends_with($scriptDir, '/public')) {
        $scriptDir = substr($scriptDir, 0, -strlen('/public'));
    }
    return $inside($scriptDir) ? $scriptDir : '';
}

/**
 * base_path() with a trailing slash. The layout puts it in <base href>, so
 * every link is relative ("about", "assets/css/main.css") and the app works
 * unchanged at a domain root or in a subfolder.
 */
function base_href(): string
{
    return base_path() . '/';
}

/** The route being asked for, relative to the app: '/', '/about', '/api/bookmarks'. */
function request_path(): string
{
    $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return '/' . trim(rawurldecode($path), '/');
}
