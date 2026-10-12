<?php
// app/Controllers/HomeController.php
declare(strict_types=1);

namespace Controllers;

use Bookmark;
use Csrf;
use Database;
use View;

class HomeController
{
    public function index(): void
    {
        $bookmarks = Database::connect()
            ->query('SELECT id, title, url, note FROM bookmarks ORDER BY id DESC')
            ->fetchAll();

        View::render('pages/home', [
            'title'     => 'Home',
            'bookmarks' => $bookmarks,
            'errors'    => $_SESSION['errors'] ?? [],
            'old'       => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errors'], $_SESSION['old']);
    }

    public function about(): void
    {
        View::render('pages/about', ['title' => 'About']);
    }

    // The same bookmarks, rendered in the browser by JavaScript calling the JSON API.
    public function client(): void
    {
        View::render('pages/client', ['title' => 'Client-side', 'script' => 'client.js']);
    }

    // POST /bookmarks — verify, validate, insert, redirect (Post/Redirect/Get).
    public function store(): void
    {
        Csrf::verify();

        // The same rules as the JSON API: one Bookmark::validate() for both.
        [$clean, $errors] = Bookmark::validate([
            'title' => input('title'),
            'url'   => input('url'),
            'note'  => input('note'),
        ]);

        if ($errors) {
            // Errors and the values typed survive the redirect in the session.
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = ['title' => input('title'), 'url' => input('url'), 'note' => input('note')];
        } else {
            // ? placeholders: the values never become part of the SQL.
            Database::connect()
                ->prepare('INSERT INTO bookmarks (title, url, note) VALUES (?, ?, ?)')
                ->execute([$clean['title'], $clean['url'], $clean['note'] ?? '']);
        }

        redirect(base_href());
    }
}
