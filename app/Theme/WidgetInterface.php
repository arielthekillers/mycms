<?php

declare(strict_types=1);

namespace App\Theme;

use App\Core\Database;

interface WidgetInterface
{
    /**
     * @param array<string, mixed> $settings decoded from widget_instances.settings
     */
    public function render(array $settings, Database $db, ?string $titleOverride): string;

    /**
     * Define form fields for the widget settings.
     * @return array<int, array<string, mixed>>
     */
    public function getFormFields(): array;
}
