<?php

declare(strict_types=1);

namespace App\Theme\Widgets;

use App\Core\Database;
use App\Theme\WidgetInterface;

final class TextHtmlWidget implements WidgetInterface
{
    public function render(array $settings, Database $db, ?string $titleOverride): string
    {
        $title = $titleOverride ?? ($settings['title'] ?? '');
        $showTitle = $settings['show_title'] ?? 'yes';
        $body = $settings['html'] ?? '';

        $titleHtml = ($title !== '' && $showTitle === 'yes') ? '<h3 class="widget-title">' . htmlspecialchars($title) . '</h3>' : '';

        // Process shortcodes by dispatching content.render event
        $body = \App\Core\Application::getInstance()->events->dispatch('content.render', $body);

        return "<div class=\"widget widget-text\">{$titleHtml}{$body}</div>";
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'show_title',
                'type' => 'select',
                'label' => 'Tampilkan Judul Widget?',
                'options' => ['yes' => 'Ya, Tampilkan', 'no' => 'Tidak, Sembunyikan'],
                'default' => 'yes',
            ],
            [
                'name' => 'html',
                'type' => 'textarea',
                'label' => 'Konten HTML / Teks Bebas',
                'default' => '',
            ]
        ];
    }
}
