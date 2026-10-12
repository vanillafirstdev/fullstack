<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'VanillaFirst App') ?> · VanillaFirst</title>
    <!-- Every link below is relative to this base, so the app runs at a domain
         root or in a subfolder (http://localhost/my-app/) without changes. -->
    <base href="<?= e(base_href()) ?>">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <!-- The session's CSRF token, for fetch() calls that write (see assets/js/lib/api.js). -->
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <!-- Module scripts are deferred by default. No bundler involved. -->
    <script type="module" src="assets/js/<?= e($script ?? 'main.js') ?>"></script>
</head>
<body>
<header class="site-header">
    <nav class="container nav">
        <a class="logo" href="./">VanillaFirst</a>
        <ul class="nav-links">
            <li><a href="./">Home</a></li>
            <li><a href="client">Client-side</a></li>
            <li><a href="about">About</a></li>
            <li><a href="api/bookmarks">API</a></li>
        </ul>
    </nav>
</header>
<main class="container">
<?= $content /* already-rendered HTML from the page template, so not escaped */ ?>
</main>
<footer class="site-footer container">
    <p>Plain HTML, CSS, JavaScript, PHP and SQLite. Zero build steps.</p>
</footer>
</body>
</html>
