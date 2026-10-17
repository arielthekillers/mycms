<?php

use App\Core\Application;
use App\Http\Middleware\AuthMiddleware;

$app = Application::getInstance();
$pluginPath = __DIR__;

// 1. ADMIN MENU & PAGES
$router->get('/admin/panel/card-builder', function(\App\Core\Request $request, \App\Core\Response $response) use ($pluginPath, $app) {
    ob_start();
    include $pluginPath . '/admin_view.php';
    $content = ob_get_clean();
    
    $title = 'Card Builder';
    $page = 'card-builder';
    
    ob_start();
    require $app->basePath . '/resources/admin/layout.php';
    return $response->html(ob_get_clean());
}, [new AuthMiddleware($app->auth)]);

// 2. API ENDPOINTS
$router->get('/admin/api/aw-widgets', function(\App\Core\Request $request, \App\Core\Response $response) use ($app) {
    $widgets = $app->db->select("SELECT * FROM aw_widgets ORDER BY id DESC");
    return $response->json(['data' => $widgets]);
}, [new AuthMiddleware($app->auth)]);

$router->get('/admin/api/aw-widgets/{id}', function(\App\Core\Request $request, \App\Core\Response $response) use ($app) {
    $id = (int) $request->param('id');
    $widget = $app->db->selectOne("SELECT * FROM aw_widgets WHERE id = ?", [$id]);
    
    if (!$widget) return $response->status(404)->json(['error' => 'Not found']);

    $items = $app->db->select("SELECT * FROM aw_widget_items WHERE widget_id = ? ORDER BY sort_order ASC", [$id]);
    $widget['items'] = $items;

    return $response->json(['data' => $widget]);
}, [new AuthMiddleware($app->auth)]);

$router->post('/admin/api/aw-widgets', function(\App\Core\Request $request, \App\Core\Response $response) use ($app) {
    $data = $request->json();
    
    $widgetId = $app->db->insert(
        "INSERT INTO aw_widgets (name, shortcode_id, title, intro, extro, layout, columns, design_mode, card_radius, card_shadow)
         VALUES (:name, :shortcode_id, :title, :intro, :extro, :layout, :columns, :design_mode, :card_radius, :card_shadow)",
        [
            'name' => $data['name'] ?? 'Widget Tanpa Nama',
            'shortcode_id' => $data['shortcode_id'] ?? 'widget-' . uniqid(),
            'title' => $data['title'] ?? null,
            'intro' => $data['intro'] ?? null,
            'extro' => $data['extro'] ?? null,
            'layout' => $data['layout'] ?? 'grid',
            'columns' => (int) ($data['columns'] ?? 3),
            'design_mode' => $data['design_mode'] ?? 'light',
            'card_radius' => $data['card_radius'] ?? 'rounded',
            'card_shadow' => (int) ($data['card_shadow'] ?? 1)
        ]
    );

    if (!empty($data['items']) && is_array($data['items'])) {
        foreach ($data['items'] as $index => $item) {
            $app->db->insert(
                "INSERT INTO aw_widget_items (widget_id, title, description, image_url, badge_text, badge_color, badge_position, button_text, button_url, button_style, sort_order)
                 VALUES (:w, :t, :d, :i, :bt, :bc, :bp, :but, :buu, :bus, :so)",
                [
                    'w' => $widgetId,
                    't' => $item['title'] ?? 'Card',
                    'd' => $item['description'] ?? null,
                    'i' => $item['image_url'] ?? null,
                    'bt' => $item['badge_text'] ?? null,
                    'bc' => $item['badge_color'] ?? 'red',
                    'bp' => $item['badge_position'] ?? 'left',
                    'but' => $item['button_text'] ?? null,
                    'buu' => $item['button_url'] ?? null,
                    'bus' => $item['button_style'] ?? 'solid',
                    'so' => $index
                ]
            );
        }
    }
    return $response->json(['success' => true]);
}, [new AuthMiddleware($app->auth)]);

$router->put('/admin/api/aw-widgets/{id}', function(\App\Core\Request $request, \App\Core\Response $response) use ($app) {
    $id = (int) $request->param('id');
    $data = $request->json();

    $app->db->transaction(function() use ($app, $id, $data) {
        $app->db->execute(
            "UPDATE aw_widgets SET 
                name = :name, shortcode_id = :shortcode_id, title = :title, intro = :intro, extro = :extro,
                layout = :layout, columns = :columns, design_mode = :design_mode, card_radius = :card_radius, card_shadow = :card_shadow
             WHERE id = :id",
            [
                'name' => $data['name'] ?? 'Widget Tanpa Nama',
                'shortcode_id' => $data['shortcode_id'] ?? 'widget-' . uniqid(),
                'title' => $data['title'] ?? null,
                'intro' => $data['intro'] ?? null,
                'extro' => $data['extro'] ?? null,
                'layout' => $data['layout'] ?? 'grid',
                'columns' => (int) ($data['columns'] ?? 3),
                'design_mode' => $data['design_mode'] ?? 'light',
                'card_radius' => $data['card_radius'] ?? 'rounded',
                'card_shadow' => (int) ($data['card_shadow'] ?? 1),
                'id' => $id
            ]
        );

        $app->db->execute("DELETE FROM aw_widget_items WHERE widget_id = ?", [$id]);

        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                $app->db->insert(
                    "INSERT INTO aw_widget_items (widget_id, title, description, image_url, badge_text, badge_color, badge_position, button_text, button_url, button_style, sort_order)
                     VALUES (:w, :t, :d, :i, :bt, :bc, :bp, :but, :buu, :bus, :so)",
                    [
                        'w' => $id,
                        't' => $item['title'] ?? 'Card',
                        'd' => $item['description'] ?? null,
                        'i' => $item['image_url'] ?? null,
                        'bt' => $item['badge_text'] ?? null,
                        'bc' => $item['badge_color'] ?? 'red',
                        'bp' => $item['badge_position'] ?? 'left',
                        'but' => $item['button_text'] ?? null,
                        'buu' => $item['button_url'] ?? null,
                        'bus' => $item['button_style'] ?? 'solid',
                        'so' => $index
                    ]
                );
            }
        }
    });
    return $response->json(['success' => true]);
}, [new AuthMiddleware($app->auth)]);

$router->delete('/admin/api/aw-widgets/{id}', function(\App\Core\Request $request, \App\Core\Response $response) use ($app) {
    $id = (int) $request->param('id');
    $app->db->execute("DELETE FROM aw_widgets WHERE id = ?", [$id]);
    return $response->json(['success' => true]);
}, [new AuthMiddleware($app->auth)]);

// 3. FRONTEND RENDERER & SHORTCODE
function render_aw_card($shortcode_id) {
    $app = Application::getInstance();
    if (!$shortcode_id) return '<!-- AW Widget ID Missing -->';
    
    try {
        $widget = $app->db->selectOne("SELECT * FROM aw_widgets WHERE shortcode_id = ?", [$shortcode_id]);
        if (!$widget) return "<!-- AW Widget [$shortcode_id] not found -->";

        $items = $app->db->select("SELECT * FROM aw_widget_items WHERE widget_id = ? ORDER BY sort_order ASC", [$widget['id']]);
    } catch (\Exception $e) {
        return "<!-- AW Widget Error: Table missing or DB Error -->";
    }

    $html = '<link rel="stylesheet" href="/assets/css/aw-card-widget.css">';
    $html .= '<section id="widget-' . htmlspecialchars($widget['shortcode_id']) . '" class="aw-widget-section" style="margin-top: 2rem; margin-bottom: 2rem;">';
    
    if (!empty($widget['title']) || !empty($widget['intro'])) {
        $html .= '  <div class="aw-widget-header aw-widget-header--center">';
        if (!empty($widget['title'])) $html .= '    <h2 class="aw-widget-title">' . htmlspecialchars($widget['title']) . '</h2>';
        if (!empty($widget['intro'])) $html .= '    <p class="aw-widget-intro">' . htmlspecialchars($widget['intro']) . '</p>';
        $html .= '  </div>';
    }
    
    // Layout Configs
    $gridClass = $widget['layout'] === 'scroll' ? 'aw-card-grid--scroll' : 'aw-card-grid--cols-' . $widget['columns'];
    $html .= '  <div class="aw-card-grid ' . $gridClass . '">';
    
    foreach ($items as $item) {
        $cardClasses = ['aw-card', 'aw-card--hoverable'];
        if ($widget['design_mode'] === 'dark') $cardClasses[] = 'aw-card--dark';
        if ($widget['design_mode'] === 'overlay') $cardClasses[] = 'aw-card--overlay';
        if (empty($widget['card_shadow'])) $cardClasses[] = 'no-shadow'; 

        $html .= '    <div class="' . implode(' ', $cardClasses) . '" style="' . ($widget['card_radius']==='boxed'?'border-radius:0;':'') . '">';
        if (!empty($item['image_url'])) {
            $html .= '      <div class="aw-card__image-wrapper">';
            if (!empty($item['badge_text'])) {
                $html .= '<span class="aw-badge aw-badge--' . htmlspecialchars($item['badge_position']) . ' aw-badge--' . htmlspecialchars($item['badge_color']) . '">' . htmlspecialchars($item['badge_text']) . '</span>';
            }
            $html .= '        <img src="' . htmlspecialchars($item['image_url']) . '" class="aw-card__image">';
            $html .= '      </div>';
        } elseif (!empty($item['badge_text'])) {
            $html .= '      <div style="padding: 1rem 1.5rem 0;"><span class="aw-badge aw-badge--' . htmlspecialchars($item['badge_color']) . '" style="position:relative; top:auto; left:auto; right:auto; display:inline-block;">' . htmlspecialchars($item['badge_text']) . '</span></div>';
        }
        
        $html .= '      <div class="aw-card__body">';
        $html .= '        <h3 class="aw-card__title">' . htmlspecialchars($item['title']) . '</h3>';
        $html .= '        <p class="aw-card__text">' . nl2br(htmlspecialchars($item['description'] ?? '')) . '</p>';
        $html .= '      </div>';
        
        if (!empty($item['button_text']) || !empty($item['button_url'])) {
            $html .= '      <div class="aw-card__footer">';
            $btnText = !empty($item['button_text']) ? $item['button_text'] : 'Buka Tautan';
            $btnUrl = !empty($item['button_url']) ? $item['button_url'] : '#';
            $html .= '        <a href="' . htmlspecialchars($btnUrl) . '" class="aw-card__btn aw-card__btn--' . htmlspecialchars($item['button_style']) . ' aw-card__btn--block">' . htmlspecialchars($btnText) . '</a>';
            $html .= '      </div>';
        }
        $html .= '    </div>';
    }
    
    $html .= '  </div>';
    
    if (!empty($widget['extro'])) {
        $html .= '  <div class="aw-widget-footer">';
        $html .= '    <p class="aw-widget-extro">' . htmlspecialchars($widget['extro']) . '</p>';
        $html .= '  </div>';
    }
    
    $html .= '</section>';
    
    return $html;
}

// Hook ke renderer CMS
$app->events->listen('article.before_render', function($content) {
    if (!is_string($content)) return $content;
    return preg_replace_callback('/\[aw_card(?:_widget)?(?: id="(.*?)")?\]/s', function($matches) {
        $id = !empty($matches[1]) ? $matches[1] : '';
        return render_aw_card($id);
    }, $content);
});

$app->events->listen('content.render', function($content) {
    if (!is_string($content)) return $content;
    return preg_replace_callback('/\[aw_card(?:_widget)?(?: id="(.*?)")?\]/s', function($matches) {
        $id = !empty($matches[1]) ? $matches[1] : '';
        return render_aw_card($id);
    }, $content);
});
