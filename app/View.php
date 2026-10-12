<?php
// app/View.php — render a page template inside a layout.
//
//   View::render('pages/about', ['title' => 'About']);
//
// renders app/views/pages/about.php, captures it as $content, then renders
// app/views/layouts/app.php, which prints $content where it wants.
// Data keys become template variables: ['title' => 'x'] gives $title.
declare(strict_types=1);

class View
{
    public static function render(string $template, array $data = [], int $status = 200, string $layout = 'app'): void
    {
        http_response_code($status);
        $content = self::file(VIEW_PATH . "/{$template}.php", $data);
        echo self::file(VIEW_PATH . "/layouts/{$layout}.php", $data + ['content' => $content]);
    }

    // Include a template in its own scope and return the output.
    // The leading underscores keep $__file and $__data from clashing with template variables.
    private static function file(string $__file, array $__data): string
    {
        if (!is_file($__file)) {
            throw new RuntimeException("View not found: {$__file}");
        }
        extract($__data, EXTR_SKIP);
        ob_start();
        try {
            require $__file;
            return (string) ob_get_clean();
        } catch (Throwable $ex) {
            ob_end_clean();
            throw $ex;
        }
    }
}
