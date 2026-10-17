<?php

use App\Core\Application;

$app = Application::getInstance();
$pluginPath = __DIR__;

// ---------------------------------------------------------
// 2. ADMIN MENU & PAGES
// ---------------------------------------------------------
$app->events->listen('admin.menu', function($menu) {
    // If admin menu hook exists, we inject. For now we use the direct route.
    return $menu;
});

$router->get('/admin/panel/sliders', function(\App\Core\Request $request, \App\Core\Response $response) use ($pluginPath) {
    $app = Application::getInstance();
    ob_start();
    include $pluginPath . '/admin_view.php';
    $content = ob_get_clean();
    
    $title = 'Manajer Slider';
    $page = 'sliders'; // For sidebar active link
    
    ob_start();
    require $app->basePath . '/resources/admin/layout.php';
    return $response->html(ob_get_clean());
});

// ---------------------------------------------------------
// 3. API ENDPOINTS (JSON Storage)
// ---------------------------------------------------------
function getSliderStorageDir() {
    $app = Application::getInstance();
    $dir = $app->basePath . '/storage/data/sliders';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    return $dir;
}

// Get all slider groups
$router->get('/admin/api/sliders', function(\App\Core\Request $request, \App\Core\Response $response) {
    $dir = getSliderStorageDir();
    $files = glob($dir . '/*.json');
    $sliders = [];
    foreach ($files as $file) {
        $sliders[] = json_decode(file_get_contents($file), true);
    }
    return $response->json(['status' => 'success', 'data' => $sliders]);
});

// Save slider group
$router->post('/admin/api/sliders', function(\App\Core\Request $request, \App\Core\Response $response) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        return $response->json(['status' => 'error', 'message' => 'ID Slider wajib diisi'], 400);
    }
    
    // Ensure clean ID
    $id = preg_replace('/[^a-z0-9_-]/', '', strtolower($data['id']));
    $data['id'] = $id; // Set clean ID back
    
    $file = getSliderStorageDir() . '/' . $id . '.json';
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    
    return $response->json(['status' => 'success', 'message' => 'Slider berhasil disimpan', 'data' => $data]);
});

// Delete slider group
$router->delete('/admin/api/sliders', function(\App\Core\Request $request, \App\Core\Response $response) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) return $response->json(['status' => 'error'], 400);
    
    $id = preg_replace('/[^a-z0-9_-]/', '', strtolower($data['id']));
    $file = getSliderStorageDir() . '/' . $id . '.json';
    if (file_exists($file)) {
        unlink($file);
    }
    return $response->json(['status' => 'success', 'message' => 'Slider berhasil dihapus']);
});

// ---------------------------------------------------------
// 4. FRONTEND RENDERER & SHORTCODE
// ---------------------------------------------------------
function render_slider($sliderId) {
    $id = preg_replace('/[^a-z0-9_-]/', '', strtolower($sliderId));
    $file = getSliderStorageDir() . '/' . $id . '.json';
    
    if (!file_exists($file)) return '';
    
    $data = json_decode(file_get_contents($file), true);
    if (empty($data['slides'])) return '';
    
    // Load CSS/JS only if not loaded yet
    static $splideLoaded = false;
    $assets = '';
    if (!$splideLoaded) {
        $assets .= '<link rel="stylesheet" href="/plugins/PluginSlider/assets/splide.min.css">';
        $assets .= '<script src="/plugins/PluginSlider/assets/splide.min.js"></script>';
        $splideLoaded = true;
    }
    
    // Generate HTML
    $html = '<div class="splide splide-plugin" id="slider-' . $id . '" style="width:100%;">';
    $html .= '<div class="splide__track"><ul class="splide__list">';
    
    foreach ($data['slides'] as $slide) {
        $html .= '<li class="splide__slide">';
        
        // Image Type
        if ($slide['type'] === 'image') {
            $html .= '<img src="' . htmlspecialchars($slide['image'] ?? '') . '" style="width:100%; height:100%; object-fit:cover;">';
        } 
        // Text + Button / Hero Type
        else if ($slide['type'] === 'hero') {
            $bg = !empty($slide['image']) ? 'background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('.htmlspecialchars($slide['image']).') center/cover no-repeat;' : 'background-color: var(--primary-color, #0f766e);';
            $html .= '<div class="hero-slide-content" style="width:100%; height:100%; display:flex; align-items:flex-end; padding-bottom:5rem; color:#fff; ' . $bg . '">';
            $html .= '<div style="max-width:1200px; margin:0 auto; padding:0 5%; width:100%; display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:2rem;">';
            
            $html .= '<div style="max-width:600px;">';
            $html .= '<h2 style="margin-bottom:1rem; font-size:clamp(2rem, 4vw, 3rem); font-weight:700; line-height:1.2; text-shadow:2px 2px 4px rgba(0,0,0,0.3);">' . htmlspecialchars($slide['title'] ?? '') . '</h2>';
            if (!empty($slide['description'])) {
                $html .= '<p style="margin-bottom:2rem; font-size:1.1rem; opacity:0.9;">' . htmlspecialchars($slide['description']) . '</p>';
            }
            $html .= '</div>';
            
            if (!empty($slide['button_text'])) {
                $html .= '<a href="' . htmlspecialchars($slide['button_link'] ?? '#') . '" style="display:flex; align-items:center; gap:1rem; font-weight:600; font-size:1.1rem; color:#fff; text-decoration:none; transition:color 0.3s;" onmouseover="this.style.color=\'var(--gold, #d4af37)\'" onmouseout="this.style.color=\'#fff\'">';
                $html .= '<span>' . htmlspecialchars($slide['button_text']) . '</span>';
                $html .= '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>';
                $html .= '</a>';
            }
            $html .= '</div></div>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul></div></div>';
    
    // Script Init
    $html .= "<script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Splide !== 'undefined') {
                new Splide('#slider-{$id}', {
                    type: 'fade',
                    rewind: true,
                    autoplay: true,
                    interval: 5000,
                    arrows: true,
                    pagination: true
                }).mount();
            }
        });
    </script>";
    
    return $assets . $html;
}

// Register Event for Shortcode (Optional depending on CMS hooks)
$app->events->listen('content.render', function($content) {
    if (!is_string($content)) return $content;
    return preg_replace_callback('/\[slider\s+id="([^"]+)"\]/i', function($matches) {
        return render_slider($matches[1]);
    }, $content);
});
