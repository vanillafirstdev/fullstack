<?php
// One field, with its error message linked for screen readers.
$field = static function (string $name, string $label, array $errors, array $old, string $attrs = ''): string {
    $invalid = isset($errors[$name])
        ? ' aria-invalid="true" aria-describedby="' . e($name) . '-error"'
        : '';
    $html = '<label>' . e($label)
        . ' <input name="' . e($name) . '" value="' . e($old[$name] ?? '') . '"' . $attrs . $invalid . '></label>';
    if (isset($errors[$name])) {
        $html .= '<p class="field-error" id="' . e($name) . '-error">' . e($errors[$name]) . '</p>';
    }
    return $html;
};
?>
<section class="intro">
    <h1>It runs.</h1>
    <p>This page was routed by <code>app/Router.php</code>, read from SQLite in WAL mode,
       rendered by a plain PHP template and enhanced by a native Web Component.
       Edit any file and refresh.</p>
    <p class="muted">The demo is a shared bookmark list: no accounts, no owners.
       Building something people sign in to? Start from
       <a href="https://vanillasaas.dev">VanillaSaaS Core</a> instead. The README explains which to use.</p>
    <p>This page renders on the server and saves with a form post. <a href="client">The client-side page</a>
       shows the same bookmarks driven by JavaScript calling this kit's JSON API.</p>
</section>

<section>
    <h2>Add a bookmark</h2>

    <form class="stack" method="post" action="bookmarks" novalidate>
        <?= Csrf::field() ?>
        <?= $field('title', 'Title', $errors, $old, ' maxlength="80" required') ?>
        <?= $field('url', 'Address', $errors, $old, ' type="url" placeholder="https://" required') ?>
        <?= $field('note', 'Note (optional)', $errors, $old, ' maxlength="200"') ?>
        <button class="btn" type="submit">Save bookmark</button>
    </form>
</section>

<section>
    <h2>Bookmarks <small>(<?= count($bookmarks) ?>)</small></h2>
    <div class="grid">
        <?php foreach ($bookmarks as $bookmark): ?>
            <bookmark-card label="<?= e($bookmark['title']) ?>" url="<?= e($bookmark['url']) ?>"
                           note="<?= e($bookmark['note']) ?>"></bookmark-card>
        <?php endforeach; ?>
    </div>
    <p class="muted">The same data as JSON: <a href="api/bookmarks">api/bookmarks</a>.
       <button class="btn btn-ghost" type="button" id="refresh-count">Count via fetch()</button>
       <output id="api-count"></output></p>
</section>
