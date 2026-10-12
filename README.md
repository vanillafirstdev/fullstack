# VanillaFirst Full-Stack Kit

> A zero-dependency, zero-build-step template for web applications in plain PHP, SQLite, JavaScript and CSS.

No `npm install`. No `composer install`. No bundler, no transpiler, no database server. You need **PHP 8.1+** with `pdo_sqlite` enabled, which most PHP installs, XAMPP and shared hosts already have.

---

## Which one do I need?

The VanillaFirst Frontend, Backend and Full-Stack Kits and VanillaSaaS Core follow the same conventions, but they are different starting points. Choose one before you write any code.

Ask two questions: **does anything need saving on a server?** and **will anyone sign in?**

| Your project | Start from |
|---|---|
| Nothing is saved on a server: a portfolio, a static site, a front-end exercise, a page that reads someone else's API | **VanillaFirst Frontend Kit** |
| Data is saved on a server, but this project serves no pages: an API for a separate front end, a mobile app or another service | **VanillaFirst Backend Kit** |
| Data is saved on a server, the same project serves the pages, and nobody signs in: a content site with a contact form, docs, a calculator, an internal tool behind your host's password protection | **VanillaFirst Full-Stack Kit** |
| People sign in, and the public side is a few pages (home, pricing, terms) | **VanillaSaaS Core** on its own. A public page is a page that doesn't call `require_auth()`. |
| People sign in, and the public side is a real content site (docs, a blog, dozens of pages) | **Full-Stack Kit and Core, as two separate sites:** the kit at `example.com`, Core at `app.example.com`. The site's "Sign in" button links to the app. |

Moving between the kits is cheap, because they share files. The Frontend Kit's CSS, `validate.js` and `<bookmark-card>` are identical in the Full-Stack Kit; only its `localStorage` module is swapped for one that calls the server, and the Full-Stack Kit's `/client` page is exactly that swap. The Backend Kit's router, `Bookmark` rules and API controller are identical in the Full-Stack Kit, which serves the same JSON API. Moving between any kit and Core means rewriting every page, because the kits send every request through one front controller and a router, while Core uses one PHP file per address.

The kits double as a learning sequence: front end, back end, full stack. Core is not a fourth step in that sequence. It's where a real project with accounts starts, and its commented code is the reference to read when coursework asks you to build a login yourself.

Don't add registration, login or passwords to any kit. Accounts need password hashing, reset tokens that expire, rate limiting on sign-in, session timeouts, and every query scoped to the user who owns the row. Core has all of that written, commented and free under the MIT licence. Starting on a kit because it looks simpler, then bolting on a login in month two, is the mistake this section exists to prevent.

[Frontend Kit](https://github.com/vanillafirstdev/frontend) · [Backend Kit](https://github.com/vanillafirstdev/backend) · [Full-Stack Kit](https://github.com/vanillafirstdev/fullstack) · [VanillaSaaS Core](https://vanillasaas.dev)

---

## Quick start

```bash
git clone https://github.com/vanillafirstdev/fullstack.git my-app
cd my-app
php -S localhost:8000 -t public
```

Open `http://localhost:8000`. On XAMPP, put the folder in `htdocs` and open `http://localhost/my-app/` instead: the root `.htaccess` routes requests into `public/`, and every link is relative, so nothing needs configuring.

The first request creates `storage/database/app.sqlite` from `database/schema.sql`. Edit any file and refresh.

---

## What's in the box

A shared bookmark list, built twice so you can compare the two ways a full-stack page works. It has no accounts on purpose: see [Which one do I need?](#which-one-do-i-need)

| | Home page `/` | Client-side page `/client` |
|---|---|---|
| Who draws the list | PHP, on the server | JavaScript, in the browser |
| How a bookmark is saved | A form posts to `/bookmarks`, then redirects | `fetch()` sends JSON to `/api/bookmarks` |
| CSRF token travels in | A hidden `_token` form field | An `X-CSRF-Token` header, read from `<meta name="csrf-token">` |
| Validation errors come back as | A redirect with messages in the session | A 422 with `fields`, shown beside the same inputs |
| Works without JavaScript | Yes | No |

Both read and write the same table through the same `Bookmark::validate()` rules, so a bookmark added on one page appears on the other, and the two can never disagree about what's valid.

The client-side page is the Frontend Kit with its storage swapped: `bookmark-card.js` and `validate.js` are the same files, and `lib/api.js` does the job `store.js` did there. The API is the Backend Kit's: `Router.php`, `Bookmark.php` and `BookmarkController.php` are the same files.

- **Routing:** `public/index.php` registers the pages and the API on `app/Router.php`: exact paths plus `{placeholders}`, trailing slashes ignored, HEAD handled, 405 with an `Allow` header when the path exists under another method. 404s and 405s come back as JSON under `/api/` and as the error page everywhere else.
- **Database:** `app/Database.php` opens one PDO connection with foreign keys on, WAL mode and a 5-second busy timeout, and installs the schema on an empty file.
- **Templates:** `app/View.php` renders a page into `$content` inside `app/views/layouts/app.php`. Every value goes through `e()`.
- **Forms:** `POST /bookmarks` checks a CSRF token, reads fields with `input()`, validates (including rejecting `javascript:` addresses), inserts with a prepared statement and redirects (Post/Redirect/Get). Errors and typed values survive the redirect.
- **Security headers:** a strict Content-Security-Policy (no inline scripts or styles run), nosniff, no framing, a referrer policy, and HSTS in production over HTTPS.
- **Session:** HttpOnly, SameSite=Lax, Secure over HTTPS, strict mode, one cookie name per project folder.
- **JSON API:** list, read, create, change (`PATCH`) and delete at `/api/bookmarks`, with the Backend Kit's status codes and error shape. Writes need the page's CSRF token in a header, because the page's session cookie would otherwise let another site forge them.
- **Web Component:** `<bookmark-card>` builds its DOM with `textContent`, so stored data is never parsed as HTML, links only `http(s)` addresses, opens a native `<dialog>`, and shows a Remove button only when given the `removable` attribute.
- **AI instructions:** `AGENTS.md` tells Copilot, Cursor, Codex and Claude Code the rules of this codebase and how to review against them.

---

## Directory structure

```text
my-app/
├── .htaccess                   # Routes requests into public/ when you can't set the web root
├── AGENTS.md / CLAUDE.md       # Instructions for AI coding assistants
├── public/                     # Web root: the only folder a browser can reach
│   ├── index.php               # Front controller: routes and dispatch
│   ├── .htaccess
│   └── assets/
│       ├── css/  main.css, components.css
│       └── js/   main.js, client.js, lib/api.js, lib/validate.js, components/bookmark-card.js
├── app/                        # Application code
│   ├── bootstrap.php           # Config, errors, helpers, autoloader, headers, session
│   ├── config.php              # env and database path
│   ├── helpers.php             # e(), input(), redirect(), json(), json_error(), json_body(), base_href()
│   ├── Bookmark.php            # Validation rules, shared by the form and the API
│   ├── Router.php  Database.php  View.php  Csrf.php  Session.php  Http.php
│   ├── Controllers/            # HomeController (pages), BookmarkController (API)
│   └── views/
│       ├── layouts/app.php
│       └── pages/  home.php, client.php, about.php, error.php
├── database/
│   └── schema.sql              # Runs once on an empty database
├── storage/                    # Writable by PHP
│   ├── database/               # app.sqlite (+ -wal, -shm)
│   └── logs/                   # app.log
└── server/                     # Example Nginx and Caddy configs
```

`app/`, `database/` and `storage/` each carry a deny-all `.htaccess` as a second line of defence.

---

## Adding a page

1. Add a method to a controller that calls `View::render('pages/contact', ['title' => 'Contact']);`
2. Create `app/views/pages/contact.php`.
3. Register it in `public/index.php`: `$router->get('/contact', [$home, 'contact']);`

Link to it as `contact`, not `/contact`. Relative links are what make subfolder installs work.

Don't name a route after a top-level folder: `/app`, `/database`, `/storage`, `/server` or `/public`. In a subfolder install, Apache finds the real folder before the router sees the request, and answers 403.

---

## Deployment

1. Copy the project to any host running PHP 8.1+ with `pdo_sqlite`.
2. Point the document root at `public/` if you can. If you can't, the root `.htaccess` handles it on Apache. For Nginx or Caddy, start from `server/`.
3. Make `storage/` writable by the PHP user. SQLite writes `-wal` and `-shm` files beside the database, so the folder must be writable, not just the file.
4. Set `'env' => 'production'` in `app/config.php`. Errors are then logged to `storage/logs/app.log` instead of shown.

---

## Limits you should know

- **One writer at a time.** WAL lets reads run alongside a write, but writes queue. The busy timeout makes them wait rather than fail. That covers most content sites, dashboards and internal tools; it doesn't suit sustained heavy concurrent writes.
- **One server.** The database is a local file. Several app servers behind a load balancer needs a different database or a replication layer.
- **PHP versions move.** HTML, CSS and the DOM keep old code working; PHP removes deprecated features in major releases. Pin the PHP version on your host and read the migration notes before upgrading.
- **No migrations runner.** `database/schema.sql` runs once. Changes to a live database are SQL you write and run yourself.

---

## Coursework that requires a login

Many full-stack briefs ask you to build authentication yourself. This kit won't do it for you, and its `AGENTS.md` tells AI assistants not to either, because a login copied without understanding is the most common way student projects leak data.

Read VanillaSaaS Core's commented code first: `app/lib/auth.php` (hashing with `password_hash()`, `session_regenerate_id(true)` at sign-in, equal timing for unknown emails), `app/lib/session.php` (cookie flags and timeouts), `app/lib/password_reset.php` (hashed, expiring, single-use tokens) and `app/lib/throttle.php` (rate limiting). Then build your own, one step at a time, and check each step against those files. When the coursework becomes a real product, start that product from Core.

---

## License

MIT. Free for personal, open-source and commercial use.
