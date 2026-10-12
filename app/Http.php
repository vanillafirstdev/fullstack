<?php
// app/Http.php — HTTPS detection and the security headers sent with every page.
declare(strict_types=1);

class Http
{
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        return (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }

    public static function sendSecurityHeaders(): void
    {
        // Content-Security-Policy: the browser only runs scripts and styles from
        // our own origin. An injected <script> or onclick="" is refused, which is
        // why this kit has no inline JavaScript or CSS. Keep it that way.
        header(
            "Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; "
            . "img-src 'self' data:; font-src 'self'; connect-src 'self'; form-action 'self'; "
            . "frame-ancestors 'none'; base-uri 'self'; object-src 'none'"
        );

        // Don't guess content types: a text file is never run as a script.
        header('X-Content-Type-Options: nosniff');

        // No framing (clickjacking). frame-ancestors covers modern browsers; this covers old ones.
        header('X-Frame-Options: DENY');

        // Send only our origin, never full URLs, to other sites.
        header('Referrer-Policy: strict-origin-when-cross-origin');

        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        header('Cross-Origin-Opener-Policy: same-origin');

        // HSTS only in production over HTTPS. Sent on localhost, it can lock your
        // browser out of http://localhost for a year.
        if (APP_ENV === 'production' && self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000');
        }

        header_remove('X-Powered-By');
    }

    /**
     * Writes to the JSON API need this page's CSRF token in an X-CSRF-Token header.
     *
     * The pages run on a session cookie, and a browser attaches that cookie to
     * every request to this site, including one forged by another site. A form
     * field can't carry the token for fetch(), so the layout prints it in
     * <meta name="csrf-token"> and api.js sends it as a header. Another site
     * can't read our page, so it can't know the token.
     *
     * Same method name as the Backend Kit's API-key check, so the shared
     * BookmarkController works in both kits unchanged.
     */
    public static function writeAllowed(): bool
    {
        $sent = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $real = (string) ($_SESSION['_csrf'] ?? '');

        if ($real !== '' && $sent !== '' && hash_equals($real, $sent)) {
            return true;
        }
        json_error(403, 'Your page expired for security reasons. Refresh the page and try again.');
        return false;
    }
}
