<?php
// app/Csrf.php — cross-site request forgery protection.
// Same field name (_token) and session key (_csrf) as VanillaSaaS Core.
//
// In every <form method="post">:   echo Csrf::field() with the short echo tag
// At the top of every POST handler: Csrf::verify();
declare(strict_types=1);

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }

    /**
     * Stop with 403 unless the request carries this session's token.
     * hash_equals() compares in constant time, so response timing can't leak it.
     */
    public static function verify(): void
    {
        $sent = input('_token');
        $real = $_SESSION['_csrf'] ?? '';

        if ($real === '' || $sent === '' || !hash_equals($real, $sent)) {
            View::render('pages/error', [
                'title'   => 'Page expired',
                'message' => 'Your form expired for security reasons. Go back, refresh the page and try again.',
            ], 403);
            exit;
        }
    }
}
