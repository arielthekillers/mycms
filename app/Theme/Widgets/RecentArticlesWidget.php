<?php

declare(strict_types=1);

namespace App\Theme\Widgets;

use App\Core\Database;
use App\Theme\WidgetInterface;

final class RecentArticlesWidget implements WidgetInterface
{
    public function render(array $settings, Database $db, ?string $titleOverride): string
    {
        $limit = (int) ($settings['limit'] ?? 5);
        $categoryId = $settings['category_id'] ?? null;

        $sql = "SELECT a.title, a.slug FROM articles a";
        $bindings = [];

        if ($categoryId !== null) {
            $sql .= ' JOIN article_categories ac ON ac.article_id = a.id';
            $sql .= ' WHERE ac.category_id = :category_id AND a.status = "published"';
            $bindings['category_id'] = $categoryId;
        } else {
            $sql .= ' WHERE a.status = "published"';
        }

        $sql .= " ORDER BY a.published_at DESC LIMIT {$limit}";

        $articles = $db->select($sql, $bindings);

        $title = $titleOverride ?? ($settings['title'] ?? 'Artikel Terbaru');
        $items = '';

        foreach ($articles as $article) {
            $label = htmlspecialchars($article['title']);
            $href = '/artikel/' . htmlspecialchars($article['slug']);
            $items .= "<li><a href=\"{$href}\">{$label}</a></li>";
        }

        if ($items === '') {
            $items = '<li class="widget-empty">Belum ada artikel.</li>';
        }

        return '<div class="widget widget-recent-articles">'
            . '<h3 class="widget-title">' . htmlspecialchars($title) . '</h3>'
            . "<ul>{$items}</ul>"
            . '</div>';
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'limit',
                'type' => 'number',
                'label' => 'Jumlah Artikel',
                'default' => 5,
            ],
            [
                'name' => 'category_id',
                'type' => 'number',
                'label' => 'ID Kategori (opsional)',
                'default' => '',
            ],
        ];
    }
}
