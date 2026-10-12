<section class="intro">
    <h1>The same bookmarks, from JavaScript.</h1>
    <p>The home page renders the list on the server and saves with a classic form post.
       This page does it the other way: the HTML arrives empty, and
       <code>assets/js/client.js</code> fills it by calling this kit's own JSON API
       (<code>/api/bookmarks</code>) with <code>fetch()</code>. Both pages read and write the
       same SQLite table, so a bookmark added on one appears on the other.</p>
    <p class="muted">Because the page and the API share one address, there is no CORS to set up.
       Because the page runs on a session cookie, every write carries the CSRF token from
       <code>&lt;meta name="csrf-token"&gt;</code> in an <code>X-CSRF-Token</code> header.</p>
</section>

<section aria-labelledby="add-heading">
    <h2 id="add-heading">Add a bookmark</h2>

    <form class="stack" id="bookmark-form" novalidate>
        <label>Title
            <input name="title" maxlength="80" required aria-describedby="title-error">
        </label>
        <p class="field-error" id="title-error" hidden></p>

        <label>Address
            <input name="url" type="url" placeholder="https://" required aria-describedby="url-error">
        </label>
        <p class="field-error" id="url-error" hidden></p>

        <label>Note (optional)
            <input name="note" maxlength="200" aria-describedby="note-error">
        </label>
        <p class="field-error" id="note-error" hidden></p>

        <button class="btn" type="submit">Save bookmark</button>
    </form>
    <p class="muted" id="app-status" role="status"></p>
</section>

<section aria-labelledby="list-heading">
    <div class="toolbar">
        <h2 id="list-heading">Bookmarks <small id="bookmark-count"></small></h2>
    </div>
    <div class="grid" id="bookmark-list" aria-live="polite">
        <p class="notice">Loading bookmarks… If this message stays, the JavaScript didn't load:
           open the browser's developer tools console to see why.</p>
    </div>
</section>
