<?php
// app/config.php — settings. A plain array: open it, change a value, save.
// It lives outside public/, so the web server never serves it.
return [
    // 'local'      → errors shown on screen.
    // 'production' → errors logged to storage/logs/app.log, never shown;
    //                HSTS sent over HTTPS. ALWAYS set this on a live server.
    'env' => 'local',

    // The SQLite file. Kept in storage/, never in public/.
    'db_path' => dirname(__DIR__) . '/storage/database/app.sqlite',
];
