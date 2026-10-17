<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    /**
     * Render a plain PHP view file with $data extracted as local variables.
     * No compiler/cache layer — deliberately simple, consistent with the
     * "native PHP" stack decision for this project.
     */
    public static function render(string $absolutePath, array $data = []): string
    {
        if (!is_file($absolutePath)) {
            throw new RuntimeException("View not found: {$absolutePath}");
        }

        $renderer = function (string $__path, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            include $__path;

            return (string) ob_get_clean();
        };

        return $renderer($absolutePath, $data);
    }
}
