<?php

declare(strict_types=1);

namespace App\Theme;

use App\Core\Cache\CacheInterface;
use App\Core\Database;
use App\Core\View;

/**
 * Ties together the presentation tables (templates, layouts, widget_areas,
 * widgets, widget_instances) into actual HTML. Deliberately NOT a page
 * builder: the structure is fixed as Template -> Layout -> Widget Area ->
 * Widgets, matching the "Structured Layout" design principle we agreed on.
 */
final class ThemeEngine
{
    public function __construct(
        private Database $db,
        private string $themesBasePath,
        private string $activeTheme = 'pesantren-default',
        private ?CacheInterface $cache = null,
        private int $fragmentTtl = 300
    ) {
        $this->options = new ThemeOptionsManager($db);
    }

    private ThemeOptionsManager $options;

    /**
     * @param array<string, mixed> $data passed straight into the template view
     */
    public function render(string $templateKey, array $data = []): string
    {
        $layoutKey = $this->resolveLayoutKey($templateKey);
        $options = $this->options->all($this->activeTheme);
        
        $layoutAreas = $this->renderLayoutAreas($layoutKey);
        $globalAreas = $this->renderGlobalWidgetAreas();
        
        $data['options'] = $options; // Make options available to templates
        $data['areas'] = $layoutAreas;
        $data['global'] = $globalAreas;
        
        $content = $this->renderTemplateView($templateKey, $data);
        
        $settingsRows = $this->db->select("SELECT `key`, `value` FROM settings WHERE `key` IN ('site_name', 'site_description', 'site_logo', 'site_favicon')");
        $site = [];
        foreach ($settingsRows as $row) {
            $site[$row['key']] = $row['value'];
        }

        $html = $this->renderLayoutView($layoutKey, [
            'content' => $content,
            'areas' => $layoutAreas,
            'global' => $globalAreas,
            'site' => $site,
            'options' => $options,
            'meta' => $data['meta'] ?? [],
            'themeCss' => $this->buildThemeCss(),
        ]);

        return $this->injectAnalytics($html);
    }

    private function injectAnalytics(string $html): string
    {
        $trackerCode = <<<HTML
        <!-- Pesantren CMS Native Tracker -->
        <script>
            (function() {
                try {
                    var path = window.location.pathname;
                    if (!path.startsWith('/admin')) {
                        fetch('/api/track', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ path: path }),
                            keepalive: true
                        }).catch(function(e) {});
                    }
                } catch (e) {}
            })();
        </script>
        HTML;

        // Check if Google Analytics is configured
        $gaId = $this->db->selectOne("SELECT `value` FROM settings WHERE `key` = 'ga_measurement_id'")['value'] ?? null;
        if (!empty($gaId)) {
            $gaId = htmlspecialchars($gaId);
            $trackerCode .= <<<HTML
            
            <!-- Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id={$gaId}"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '{$gaId}');
            </script>
            HTML;
        }

        // Inject right before closing body tag
        if (str_contains($html, '</body>')) {
            return str_replace('</body>', $trackerCode . "\n</body>", $html);
        }
        
        return $html . $trackerCode;
    }

    /**
     * Renders Theme Options (colors, fonts) as CSS custom properties, so any
     * layout/template can use var(--primary-color) etc. without querying
     * theme_options directly. Falls back to sensible defaults when unset.
     */
    private function buildThemeCss(): string
    {
        $options = $this->options->all($this->activeTheme);

        $vars = [
            '--primary-color' => $options['primary_color'] ?? '#0f766e',
            '--secondary-color' => $options['secondary_color'] ?? '#1e293b',
            '--font-heading' => $options['font_heading'] ?? "'Segoe UI', sans-serif",
            '--font-body' => $options['font_body'] ?? "'Segoe UI', sans-serif",
        ];

        $declarations = '';

        foreach ($vars as $name => $value) {
            $declarations .= "{$name}: {$value};";
        }

        return "<style>:root {{$declarations}}</style>";
    }

    private function resolveLayoutKey(string $templateKey): string
    {
        $lookup = function () use ($templateKey): array {
            return $this->db->selectOne(
                'SELECT l.key_name AS layout_key
                 FROM templates t
                 LEFT JOIN layouts l ON l.id = t.default_layout_id
                 WHERE t.theme = :theme AND t.key_name = :key',
                ['theme' => $this->activeTheme, 'key' => $templateKey]
            ) ?? [];
        };

        // Query cache: this join rarely changes (only when an admin edits
        // Template/Layout assignments), but is hit on every single render.
        $template = $this->cache !== null
            ? $this->cache->remember("template-layout:{$this->activeTheme}:{$templateKey}", $this->fragmentTtl, $lookup)
            : $lookup();

        // Falls back to full-width so rendering still works before Template
        // rows are seeded for a given theme/template combination.
        return $template['layout_key'] ?? 'full-width';
    }

    private function renderTemplateView(string $templateKey, array $data): string
    {
        $path = "{$this->themesBasePath}/{$this->activeTheme}/templates/{$templateKey}.php";

        if (!is_file($path)) {
            $path = "{$this->themesBasePath}/{$this->activeTheme}/templates/404.php";
        }

        return View::render($path, $data);
    }

    private function renderLayoutView(string $layoutKey, array $data): string
    {
        $path = "{$this->themesBasePath}/{$this->activeTheme}/layouts/{$layoutKey}.php";

        if (!is_file($path)) {
            $path = "{$this->themesBasePath}/{$this->activeTheme}/layouts/full-width.php";
        }

        return View::render($path, $data);
    }

    /** @return array<string, string> area key_name => rendered widgets HTML */
    private function renderLayoutAreas(string $layoutKey): array
    {
        $layout = $this->db->selectOne('SELECT structure FROM layouts WHERE key_name = :key', ['key' => $layoutKey]);

        if ($layout === null) {
            return [];
        }

        $structure = json_decode($layout['structure'], true) ?: [];
        $areas = [];

        foreach ($structure as $areaDef) {
            $areaKey = $areaDef['area'] ?? null;

            if ($areaKey === null || $areaKey === 'main') {
                continue; // 'main' is the template content itself, not a widget area
            }

            $areas[$areaKey] = $this->renderWidgetArea($areaKey);
        }

        return $areas;
    }

    /** @return array<string, string> area key_name => rendered widgets HTML, for header/footer regardless of layout */
    private function renderGlobalWidgetAreas(): array
    {
        $jsonPath = $this->themesBasePath . '/' . $this->activeTheme . '/theme.json';
        $themeGlobalKeys = null;

        if (file_exists($jsonPath)) {
            $meta = json_decode(file_get_contents($jsonPath), true) ?? [];
            if (!empty($meta['widget_areas']) && is_array($meta['widget_areas'])) {
                $themeGlobalKeys = [];
                foreach ($meta['widget_areas'] as $item) {
                    if (is_array($item)) {
                        $scope = $item['scope'] ?? 'global';
                        $key = $item['key_name'] ?? $item['key'] ?? null;
                        if ($key && $scope === 'global') {
                            $themeGlobalKeys[] = $key;
                        }
                    } elseif (is_string($item)) {
                        $themeGlobalKeys[] = $item;
                    }
                }
            }
        }

        if ($themeGlobalKeys !== null) {
            if (empty($themeGlobalKeys)) {
                return [];
            }
            $inClause = implode(',', array_fill(0, count($themeGlobalKeys), '?'));
            $rows = $this->db->select(
                "SELECT key_name FROM widget_areas WHERE scope = 'global' AND key_name IN ($inClause)",
                $themeGlobalKeys
            );
        } else {
            $rows = $this->db->select("SELECT key_name FROM widget_areas WHERE scope = 'global'");
        }

        $areas = [];

        foreach ($rows as $row) {
            $areas[$row['key_name']] = $this->renderWidgetArea($row['key_name']);
        }

        return $areas;
    }

    private function renderWidgetArea(string $areaKey): string
    {
        $showPositions = isset($_GET['tp']) && $_GET['tp'] === '1';

        $render = function () use ($areaKey, $showPositions): string {
            $instances = $this->db->select(
                'SELECT wi.title_override, wi.settings, w.provider
                 FROM widget_instances wi
                 JOIN widgets w ON w.id = wi.widget_id
                 JOIN widget_areas wa ON wa.id = wi.widget_area_id
                 WHERE wa.key_name = :key AND wi.is_active = 1
                 ORDER BY wi.sort_order ASC',
                ['key' => $areaKey]
            );

            $html = '';

            foreach ($instances as $instance) {
                $html .= $this->renderWidgetInstance($instance);
            }

            if ($showPositions) {
                $wrapper = '<div style="border: 2px dashed #f4b41a; padding: 1rem; margin: 1rem 0; background: rgba(244,180,26,0.1); position: relative; min-height: 60px;">';
                $wrapper .= '<div style="position: absolute; top: -12px; left: 10px; background: #f4b41a; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; z-index: 9999; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"><i class="fas fa-plug"></i> Area: ' . htmlspecialchars($areaKey) . '</div>';
                $wrapper .= $html ?: '<div style="color: #666; font-style: italic; text-align: center; padding-top: 5px;">(Tidak ada widget yang dipasang)</div>';
                $wrapper .= '</div>';
                return $wrapper;
            }

            return $html;
        };

        if ($this->cache === null || $showPositions) {
            return $render();
        }

        // Fragment cache: widget areas (sidebar, footer, etc.) rarely change
        // per-request, so cache the rendered HTML per area instead of
        // re-querying + re-rendering every widget on every page view.
        return $this->cache->remember("widget-area:{$this->activeTheme}:{$areaKey}", $this->fragmentTtl, $render);
    }

    private function renderWidgetInstance(array $instance): string
    {
        $providerClass = $instance['provider'];

        if (!class_exists($providerClass) || !is_a($providerClass, WidgetInterface::class, true)) {
            return '';
        }

        $settings = json_decode($instance['settings'] ?? '[]', true) ?: [];
        /** @var WidgetInterface $widget */
        $widget = new $providerClass();

        return $widget->render($settings, $this->db, $instance['title_override']);
    }
}
