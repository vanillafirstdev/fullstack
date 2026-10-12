<?php
// app/Session.php — a session cookie JavaScript can't read and other sites can't send.
declare(strict_types=1);

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Reject session IDs the server didn't issue (blocks session fixation).
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        $https = Http::isHttps();

        // A name per project folder, so two apps on http://localhost/ don't share
        // a cookie. Over HTTPS the __Host- prefix makes the browser refuse the
        // cookie unless it is Secure, Path=/ and has no Domain.
        $name = 'vf_' . substr(hash('sha256', APP_ROOT), 0, 10);
        session_name($https ? '__Host-' . $name : $name);

        session_set_cookie_params([
            'lifetime' => 0,          // until the browser closes
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,       // invisible to document.cookie
            'samesite' => 'Lax',      // not sent on cross-site POSTs
        ]);

        session_start();
    }
}
