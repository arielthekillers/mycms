<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use App\Theme\Widgets\RecentArticlesWidget;
use App\Theme\Widgets\TextHtmlWidget;

final class ThemeSeeder
{
    private const THEME_KEY = 'pesantren-default';

    public function run(Database $db): void
    {
        $db->execute(
            'INSERT IGNORE INTO themes (key_name, name, version, is_active) VALUES (:key, :name, :version, 1)',
            ['key' => self::THEME_KEY, 'name' => 'Pesantren Default', 'version' => '1.0.0']
        );

        $this->seedLayouts($db);
        $this->seedTemplates($db);
        $this->seedWidgetAreas($db);
        $this->seedWidgets($db);
        $this->seedSampleWidgetInstances($db);
        $this->seedThemeOptions($db);
    }

    private function seedThemeOptions(Database $db): void
    {
        $theme = $db->selectOne('SELECT id FROM themes WHERE key_name = :key', ['key' => self::THEME_KEY]);

        if ($theme === null) {
            return;
        }

        $defaults = [
            'primary_color' => '#0f766e',
            'secondary_color' => '#1e293b',
            'font_heading' => "'Segoe UI', sans-serif",
            'font_body' => "'Segoe UI', sans-serif",
        ];

        foreach ($defaults as $key => $value) {
            $db->execute(
                'INSERT IGNORE INTO theme_options (theme_id, option_key, option_value)
                 VALUES (:theme_id, :option_key, :option_value)',
                ['theme_id' => $theme['id'], 'option_key' => $key, 'option_value' => json_encode($value)]
            );
        }
    }

    private function seedLayouts(Database $db): void
    {
        $layouts = [
            ['key_name' => 'full-width', 'label' => 'Full Width', 'structure' => [['area' => 'main', 'width' => 12]]],
            ['key_name' => 'content-sidebar', 'label' => 'Content + Sidebar', 'structure' => [
                ['area' => 'main', 'width' => 8],
                ['area' => 'sidebar-main', 'width' => 4],
            ]],
        ];

        foreach ($layouts as $layout) {
            $db->execute(
                'INSERT IGNORE INTO layouts (key_name, label, structure) VALUES (:key_name, :label, :structure)',
                [
                    'key_name' => $layout['key_name'],
                    'label' => $layout['label'],
                    'structure' => json_encode($layout['structure']),
                ]
            );
        }
    }

    private function seedTemplates(Database $db): void
    {
        $templates = [
            ['key_name' => 'homepage', 'label' => 'Homepage', 'layout' => 'content-sidebar'],
            ['key_name' => 'single-article', 'label' => 'Single Article', 'layout' => 'content-sidebar'],
            ['key_name' => 'archive', 'label' => 'Archive', 'layout' => 'content-sidebar'],
            ['key_name' => 'page', 'label' => 'Static Page', 'layout' => 'full-width'],
            ['key_name' => 'single-module', 'label' => 'Module Detail', 'layout' => 'content-sidebar'],
            ['key_name' => '404', 'label' => 'Not Found', 'layout' => 'full-width'],
        ];

        foreach ($templates as $tpl) {
            $layout = $db->selectOne('SELECT id FROM layouts WHERE key_name = :key', ['key' => $tpl['layout']]);

            $db->execute(
                'INSERT IGNORE INTO templates (theme, key_name, label, default_layout_id)
                 VALUES (:theme, :key_name, :label, :layout_id)',
                [
                    'theme' => self::THEME_KEY,
                    'key_name' => $tpl['key_name'],
                    'label' => $tpl['label'],
                    'layout_id' => $layout['id'] ?? null,
                ]
            );
        }
    }

    private function seedWidgetAreas(Database $db): void
    {
        $areas = [
            ['key_name' => 'header-top', 'label' => 'Header', 'scope' => 'global'],
            ['key_name' => 'footer', 'label' => 'Footer', 'scope' => 'global'],
            ['key_name' => 'sidebar-main', 'label' => 'Sidebar', 'scope' => 'layout'],
        ];

        foreach ($areas as $area) {
            $db->execute(
                'INSERT IGNORE INTO widget_areas (key_name, label, scope) VALUES (:key_name, :label, :scope)',
                $area
            );
        }
    }

    private function seedWidgets(Database $db): void
    {
        $widgets = [
            [
                'key_name' => 'recent_articles',
                'label' => 'Artikel Terbaru',
                'provider' => RecentArticlesWidget::class,
                'settings_schema' => ['title' => 'string', 'limit' => 'integer', 'category_id' => 'integer|nullable'],
            ],
            [
                'key_name' => 'text_html',
                'label' => 'Teks / HTML Bebas',
                'provider' => TextHtmlWidget::class,
                'settings_schema' => ['title' => 'string', 'html' => 'text'],
            ],
        ];

        foreach ($widgets as $widget) {
            $db->execute(
                'INSERT IGNORE INTO widgets (key_name, label, provider, settings_schema)
                 VALUES (:key_name, :label, :provider, :settings_schema)',
                [
                    'key_name' => $widget['key_name'],
                    'label' => $widget['label'],
                    'provider' => $widget['provider'],
                    'settings_schema' => json_encode($widget['settings_schema']),
                ]
            );
        }
    }

    private function seedSampleWidgetInstances(Database $db): void
    {
        $sidebar = $db->selectOne('SELECT id FROM widget_areas WHERE key_name = :key', ['key' => 'sidebar-main']);
        $recentArticles = $db->selectOne('SELECT id FROM widgets WHERE key_name = :key', ['key' => 'recent_articles']);

        if ($sidebar === null || $recentArticles === null) {
            return;
        }

        $exists = $db->selectOne(
            'SELECT id FROM widget_instances WHERE widget_area_id = :area_id AND widget_id = :widget_id',
            ['area_id' => $sidebar['id'], 'widget_id' => $recentArticles['id']]
        );

        if ($exists !== null) {
            return;
        }

        $db->execute(
            'INSERT INTO widget_instances (widget_id, widget_area_id, settings, sort_order)
             VALUES (:widget_id, :area_id, :settings, 0)',
            [
                'widget_id' => $recentArticles['id'],
                'area_id' => $sidebar['id'],
                'settings' => json_encode(['title' => 'Artikel Terbaru', 'limit' => 5]),
            ]
        );
    }
}
