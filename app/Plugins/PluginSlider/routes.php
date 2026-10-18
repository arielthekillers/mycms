<?php

use App\Core\Application;

$app = Application::getInstance();
$pluginPath = __DIR__;

// ---------------------------------------------------------
// 2. ADMIN MENU & PAGES
// ---------------------------------------------------------
$app->events->listen('admin.menu', function($menu) {
    return $menu;
});

$router->get('/admin/panel/sliders', function(\App\Core\Request $request, \App\Core\Response $response) use ($pluginPath) {
    $app = Application::getInstance();
    ob_start();
    include $pluginPath . '/admin_view.php';
    $content = ob_get_clean();
    
    $title = 'Manajer Slider';
    $page = 'sliders';
    
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
    $data['id'] = $id;
    
    // Defaults for settings
    if (!isset($data['autoplay'])) $data['autoplay'] = true;
    if (empty($data['interval'])) $data['interval'] = 5000;
    if (!isset($data['pause_on_hover'])) $data['pause_on_hover'] = false;
    if (empty($data['effect'])) $data['effect'] = 'fade';
    if (empty($data['content_anim'])) $data['content_anim'] = 'fade-up';
    if (empty($data['speed'])) $data['speed'] = 800;
    if (empty($data['easing'])) $data['easing'] = 'ease-out';
    if (empty($data['layout'])) $data['layout'] = 'hero';
    if (!isset($data['height'])) $data['height'] = '';
    
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
function render_slider($sliderId, array $options = []) {
    $id = preg_replace('/[^a-z0-9_-]/', '', strtolower((string)$sliderId));
    $file = getSliderStorageDir() . '/' . $id . '.json';
    
    if (!file_exists($file)) return '';
    
    $data = json_decode(file_get_contents($file), true);
    if (empty($data['slides'])) return '';

    // Unique instance counter for DOM IDs & JS scoped functions
    static $sliderInstanceCounter = 0;
    $sliderInstanceCounter++;
    $instanceDomId = 'slider-' . $id . '-' . $sliderInstanceCounter;
    $cleanInstanceId = str_replace(['-', '.'], '_', $instanceDomId);

    // Layout mode resolution: 'hero' | 'standard' | 'article'
    $layout = $options['layout'] ?? $data['layout'] ?? 'hero';
    if (!in_array($layout, ['hero', 'standard', 'article'], true)) {
        $layout = 'hero';
    }

    // Custom height resolution
    $customHeight = trim((string)($options['height'] ?? $data['height'] ?? ''));

    // Compute dimensions and styling tokens per layout
    switch ($layout) {
        case 'article':
            $containerHeight = !empty($customHeight) ? $customHeight : '400px';
            $minHeight = '300px';
            $maxWidth = '100%';
            $margin = '2.25rem 0';
            $borderRadius = '16px';
            $boxShadow = '0 10px 30px -4px rgba(15, 23, 42, 0.15), 0 4px 10px -2px rgba(15, 23, 42, 0.06)';
            $contentPaddingBottom = '2.25rem';
            $contentPaddingX = '2rem';
            $contentGap = '0.4rem';
            $headingSize = 'clamp(1.3rem, 2.5vw, 1.75rem)';
            $headingMarginBottom = '0.35rem';
            $descSize = '0.92rem';
            $descMarginBottom = '0.85rem';
            $descLineClamp = 'display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;';
            $btnPadding = '0.55rem 1.35rem';
            $btnFontSize = '0.88rem';
            $arrowSize = '36px';
            $arrowIconSize = '14px';
            $paginationBottom = '0.75rem';
            break;

        case 'standard':
            $containerHeight = !empty($customHeight) ? $customHeight : '480px';
            $minHeight = '360px';
            $maxWidth = '1200px';
            $margin = '2rem auto';
            $borderRadius = '18px';
            $boxShadow = '0 16px 40px -6px rgba(15, 23, 42, 0.14), 0 6px 16px -3px rgba(15, 23, 42, 0.08)';
            $contentPaddingBottom = '3rem';
            $contentPaddingX = '2.5rem';
            $contentGap = '1.5rem';
            $headingSize = 'clamp(1.6rem, 3.2vw, 2.35rem)';
            $headingMarginBottom = '0.75rem';
            $descSize = '1.02rem';
            $descMarginBottom = '1.65rem';
            $descLineClamp = '';
            $btnPadding = '0.75rem 1.85rem';
            $btnFontSize = '0.95rem';
            $arrowSize = '40px';
            $arrowIconSize = '16px';
            $paginationBottom = '1.15rem';
            break;

        case 'hero':
        default:
            $containerHeight = !empty($customHeight) ? $customHeight : '100vh';
            $minHeight = '600px';
            $maxWidth = '100%';
            $margin = '0';
            $borderRadius = '0';
            $boxShadow = 'none';
            $contentPaddingBottom = '5.5rem';
            $contentPaddingX = '5%';
            $contentGap = '2rem';
            $headingSize = 'clamp(2.2rem, 4.5vw, 3.5rem)';
            $headingMarginBottom = '1.15rem';
            $descSize = '1.15rem';
            $descMarginBottom = '2.25rem';
            $descLineClamp = '';
            $btnPadding = '0.9rem 2.2rem';
            $btnFontSize = '1.05rem';
            $arrowSize = '46px';
            $arrowIconSize = '18px';
            $paginationBottom = '1.75rem';
            break;
    }

    // Load CSS/JS only once
    static $splideLoaded = false;
    $assets = '';
    if (!$splideLoaded) {
        $assets .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">';
        $assets .= '<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>';
        $splideLoaded = true;
    }
    
    // Configuration values
    $isAutoplay = isset($data['autoplay']) ? (bool)$data['autoplay'] : true;
    $autoplayVal = $isAutoplay ? 'true' : 'false';
    $interval = !empty($data['interval']) ? (int)$data['interval'] : 5000;
    $pauseOnHover = isset($data['pause_on_hover']) ? ((bool)$data['pause_on_hover'] ? 'true' : 'false') : 'false';
    $effect = !empty($data['effect']) ? $data['effect'] : 'fade';
    $contentAnim = !empty($data['content_anim']) ? $data['content_anim'] : 'fade-up';
    $speed = !empty($data['speed']) ? (int)$data['speed'] : 800;
    $easing = !empty($data['easing']) ? $data['easing'] : 'ease-out';

    // Map Splide transition type
    $splideType = 'fade';
    $direction = 'ltr';
    if ($effect === 'loop' || $effect === 'slide') {
        $splideType = 'loop';
    } elseif ($effect === 'ttb') {
        $splideType = 'slide';
        $direction = 'ttb';
    }

    // Map easing curve
    $easingCurve = match($easing) {
        'ease-in-out' => 'ease-in-out',
        'linear' => 'linear',
        default => 'cubic-bezier(0.16, 1, 0.3, 1)',
    };
    
    // CSS Styles
    $html = "
    <style>
        #{$instanceDomId} {
            overflow: hidden !important;
            position: relative !important;
            width: 100% !important;
            max-width: {$maxWidth} !important;
            min-width: 0 !important;
            height: {$containerHeight} !important;
            min-height: {$minHeight} !important;
            margin: {$margin} !important;
            border-radius: {$borderRadius} !important;
            box-shadow: {$boxShadow} !important;
            padding: 0 !important;
            background-color: #0f172a !important;
            box-sizing: border-box !important;
        }
        #{$instanceDomId} * {
            box-sizing: border-box !important;
        }
        #{$instanceDomId} .splide__track {
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            border-radius: inherit !important;
            overflow: hidden !important;
        }
        #{$instanceDomId} ul.splide__list,
        #{$instanceDomId} .splide__list {
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
            height: 100% !important;
            min-width: 0 !important;
            display: flex !important;
        }
        #{$instanceDomId} li.splide__slide,
        #{$instanceDomId} .splide__slide {
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
            height: 100% !important;
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
            overflow: hidden !important;
            position: relative !important;
        }

        /* Typography & Content Resets */
        #{$instanceDomId} h2.hero-heading-animated,
        #{$instanceDomId} .hero-heading-animated {
            margin: 0 0 {$headingMarginBottom} 0 !important;
            padding: 0 !important;
            color: #ffffff !important;
            font-size: {$headingSize} !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.75) !important;
            letter-spacing: -0.02em !important;
        }
        #{$instanceDomId} p.hero-content-animated,
        #{$instanceDomId} .hero-content-animated {
            margin: 0 0 {$descMarginBottom} 0 !important;
            padding: 0 !important;
            color: rgba(255, 255, 255, 0.95) !important;
            font-size: {$descSize} !important;
            line-height: 1.6 !important;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.6) !important;
            font-weight: 400 !important;
        }
        #{$instanceDomId} .hero-btn-animated {
            margin: 0 !important;
            padding: 0 !important;
            display: inline-flex !important;
        }
        #{$instanceDomId} .hero-btn-animated a {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            font-weight: 600 !important;
            font-size: {$btnFontSize} !important;
            background: var(--gold, #f4b41a) !important;
            color: #0f172a !important;
            padding: {$btnPadding} !important;
            border-radius: 50px !important;
            text-decoration: none !important;
            box-shadow: 0 6px 20px rgba(244, 180, 26, 0.4) !important;
            transition: all 0.25s ease !important;
            line-height: 1.2 !important;
        }
        #{$instanceDomId} .hero-btn-animated a:hover {
            background: #e09f0f !important;
            color: #000000 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 10px 25px rgba(244, 180, 26, 0.5) !important;
        }

        /* Modern Glass Arrows */
        #{$instanceDomId} .splide__arrows {
            display: block !important;
        }
        #{$instanceDomId} .splide__arrow {
            z-index: 15 !important;
            position: absolute !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            border: 1px solid rgba(255, 255, 255, 0.35) !important;
            width: {$arrowSize} !important;
            height: {$arrowSize} !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            opacity: 0.85 !important;
            transition: all 0.25s ease !important;
            outline: none !important;
        }
        #{$instanceDomId} .splide__arrow--prev {
            left: 1rem !important;
        }
        #{$instanceDomId} .splide__arrow--next {
            right: 1rem !important;
        }
        #{$instanceDomId} .splide__arrow:hover {
            background: rgba(15, 23, 42, 0.95) !important;
            opacity: 1 !important;
            transform: translateY(-50%) scale(1.1) !important;
            border-color: rgba(255, 255, 255, 0.6) !important;
        }
        #{$instanceDomId} .splide__arrow svg {
            fill: #ffffff !important;
            width: {$arrowIconSize} !important;
            height: {$arrowIconSize} !important;
        }

        /* Modern Pagination Pills */
        #{$instanceDomId} ul.splide__pagination,
        #{$instanceDomId} .splide__pagination {
            z-index: 15 !important;
            position: absolute !important;
            bottom: {$paginationBottom} !important;
            left: 0 !important;
            right: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 8px !important;
            pointer-events: auto !important;
        }
        #{$instanceDomId} ul.splide__pagination li,
        #{$instanceDomId} .splide__pagination li {
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
            display: inline-flex !important;
        }
        #{$instanceDomId} .splide__pagination__page {
            background: rgba(255, 255, 255, 0.5) !important;
            border: none !important;
            width: 10px !important;
            height: 10px !important;
            margin: 0 !important;
            padding: 0 !important;
            border-radius: 50% !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 1px 4px rgba(0,0,0,0.4) !important;
            outline: none !important;
        }
        #{$instanceDomId} .splide__pagination__page.is-active {
            background: var(--gold, #f4b41a) !important;
            width: 26px !important;
            border-radius: 6px !important;
            transform: scale(1.05) !important;
            box-shadow: 0 2px 8px rgba(244, 180, 26, 0.5) !important;
        }
        
        /* Ken Burns Cinematic Zoom */
        " . ($effect === 'kenburns' ? "
        #{$instanceDomId} .splide__slide .hero-bg-layer,
        #{$instanceDomId} .splide__slide img {
            transition: transform 6s ease-out;
            transform: scale(1);
        }
        #{$instanceDomId} .splide__slide.is-active .hero-bg-layer,
        #{$instanceDomId} .splide__slide.is-active img {
            transform: scale(1.12);
        }
        " : "") . "

        /* Text Content Entrance Animations */
        " . ($contentAnim !== 'none' ? "
        #{$instanceDomId} .hero-content-animated {
            opacity: 0;
            transition: all 0.75s {$easingCurve} 0.25s;
        }
        #{$instanceDomId} .hero-heading-animated {
            opacity: 0;
            transition: all 0.75s {$easingCurve} 0.15s;
        }
        #{$instanceDomId} .hero-btn-animated {
            opacity: 0;
            transition: all 0.65s {$easingCurve} 0.35s;
        }
        
        /* Initial Fallback: ensure first slide or active slide is visible */
        #{$instanceDomId} .splide__slide.is-active .hero-heading-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-content-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-btn-animated,
        #{$instanceDomId}:not(.is-initialized) .splide__slide:first-child .hero-heading-animated,
        #{$instanceDomId}:not(.is-initialized) .splide__slide:first-child .hero-content-animated,
        #{$instanceDomId}:not(.is-initialized) .splide__slide:first-child .hero-btn-animated {
            opacity: 1 !important;
            transform: none !important;
        }

        /* Fade Up */
        " . ($contentAnim === 'fade-up' ? "
        #{$instanceDomId} .hero-heading-animated,
        #{$instanceDomId} .hero-content-animated,
        #{$instanceDomId} .hero-btn-animated {
            transform: translateY(24px);
        }
        #{$instanceDomId} .splide__slide.is-active .hero-heading-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-content-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-btn-animated {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
        " : "") . "

        /* Fade Down */
        " . ($contentAnim === 'fade-down' ? "
        #{$instanceDomId} .hero-heading-animated,
        #{$instanceDomId} .hero-content-animated,
        #{$instanceDomId} .hero-btn-animated {
            transform: translateY(-24px);
        }
        #{$instanceDomId} .splide__slide.is-active .hero-heading-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-content-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-btn-animated {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
        " : "") . "

        /* Slide Left */
        " . ($contentAnim === 'slide-left' ? "
        #{$instanceDomId} .hero-heading-animated,
        #{$instanceDomId} .hero-content-animated,
        #{$instanceDomId} .hero-btn-animated {
            transform: translateX(-35px);
        }
        #{$instanceDomId} .splide__slide.is-active .hero-heading-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-content-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-btn-animated {
            opacity: 1 !important;
            transform: translateX(0) !important;
        }
        " : "") . "

        /* Slide Right */
        " . ($contentAnim === 'slide-right' ? "
        #{$instanceDomId} .hero-heading-animated,
        #{$instanceDomId} .hero-content-animated,
        #{$instanceDomId} .hero-btn-animated {
            transform: translateX(35px);
        }
        #{$instanceDomId} .splide__slide.is-active .hero-heading-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-content-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-btn-animated {
            opacity: 1 !important;
            transform: translateX(0) !important;
        }
        " : "") . "

        /* Zoom In */
        " . ($contentAnim === 'zoom-in' ? "
        #{$instanceDomId} .hero-heading-animated,
        #{$instanceDomId} .hero-content-animated,
        #{$instanceDomId} .hero-btn-animated {
            transform: scale(0.92);
        }
        #{$instanceDomId} .splide__slide.is-active .hero-heading-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-content-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-btn-animated {
            opacity: 1 !important;
            transform: scale(1) !important;
        }
        " : "") . "

        /* Zoom Out */
        " . ($contentAnim === 'zoom-out' ? "
        #{$instanceDomId} .hero-heading-animated,
        #{$instanceDomId} .hero-content-animated,
        #{$instanceDomId} .hero-btn-animated {
            transform: scale(1.08);
        }
        #{$instanceDomId} .splide__slide.is-active .hero-heading-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-content-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-btn-animated {
            opacity: 1 !important;
            transform: scale(1) !important;
        }
        " : "") . "

        /* Fade In */
        " . ($contentAnim === 'fade-in' ? "
        #{$instanceDomId} .splide__slide.is-active .hero-heading-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-content-animated,
        #{$instanceDomId} .splide__slide.is-active .hero-btn-animated {
            opacity: 1 !important;
        }
        " : "") . "
        " : "") . "
    </style>";

    // Generate HTML Markup
    $html .= '<div class="splide splide-plugin slider-layout-' . htmlspecialchars($layout) . '" id="' . $instanceDomId . '">';
    $html .= '<div class="splide__track"><ul class="splide__list">';
    
    foreach ($data['slides'] as $slide) {
        $html .= '<li class="splide__slide">';
        
        // Image Type
        if ($slide['type'] === 'image') {
            $html .= '<img src="' . htmlspecialchars($slide['image'] ?? '') . '" style="width:100%; height:100%; object-fit:cover; display:block;">';
        } 
        // Text + Button / Hero Type
        else if ($slide['type'] === 'hero') {
            $bgImg = !empty($slide['image']) ? htmlspecialchars($slide['image']) : '';
            
            $html .= '<div class="hero-slide-content" style="position:relative; width:100%; height:100%; display:flex; align-items:flex-end; padding-bottom:' . $contentPaddingBottom . '; color:#fff; overflow:hidden;">';
            
            // Background Layer for Ken Burns / Overlay
            if (!empty($bgImg)) {
                $html .= '<div class="hero-bg-layer" style="position:absolute; inset:0; background: linear-gradient(180deg, rgba(15, 23, 42, 0.25) 0%, rgba(15, 23, 42, 0.88) 100%), url(' . $bgImg . ') center/cover no-repeat; z-index:1;"></div>';
            } else {
                $html .= '<div class="hero-bg-layer" style="position:absolute; inset:0; background-color: var(--teal, #1bb2a6); z-index:1;"></div>';
            }

            if ($layout === 'article') {
                $html .= '<div class="hero-content-inner" style="position:relative; z-index:2; width:100%; max-width:100%; padding:0 ' . $contentPaddingX . '; display:flex; flex-direction:column; align-items:flex-start; justify-content:flex-end; gap:' . $contentGap . ';">';
                
                $html .= '<div style="max-width:100%;">';
                $html .= '<h2 class="hero-heading-animated" style="margin:0 0 ' . $headingMarginBottom . ' 0; font-size:' . $headingSize . '; font-weight:800; line-height:1.2; color:#ffffff !important; text-shadow:0 3px 15px rgba(0,0,0,0.6); letter-spacing:-0.02em;">' . htmlspecialchars($slide['title'] ?? '') . '</h2>';
                if (!empty($slide['description'])) {
                    $html .= '<p class="hero-content-animated" style="margin:0 0 ' . $descMarginBottom . ' 0; font-size:' . $descSize . '; color:rgba(255,255,255,0.92) !important; line-height:1.6; text-shadow:0 2px 6px rgba(0,0,0,0.5); font-weight:300; ' . $descLineClamp . '">' . htmlspecialchars($slide['description']) . '</p>';
                }
                $html .= '</div>';
                
                if (!empty($slide['button_text'])) {
                    $html .= '<div class="hero-btn-animated" style="margin:0; padding:0;">';
                    $html .= '<a href="' . htmlspecialchars($slide['button_link'] ?? '#') . '" style="display:inline-flex; align-items:center; gap:0.5rem; font-weight:600; font-size:' . $btnFontSize . '; background:var(--gold, #f4b41a); color:#0f172a !important; padding:' . $btnPadding . '; border-radius:50px; text-decoration:none; box-shadow:0 8px 25px rgba(244,180,26,0.35); transition:all 0.3s cubic-bezier(0.16,1,0.3,1);">';
                    $html .= '<span>' . htmlspecialchars($slide['button_text']) . '</span>';
                    $html .= '<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>';
                    $html .= '</a>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div style="position:relative; z-index:2; max-width:1240px; margin:0 auto; padding:0 ' . $contentPaddingX . '; width:100%; display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:' . $contentGap . ';">';
                
                $html .= '<div style="max-width:740px;">';
                $html .= '<h2 class="hero-heading-animated" style="margin:0 0 ' . $headingMarginBottom . ' 0; font-size:' . $headingSize . '; font-weight:800; line-height:1.18; color:#ffffff !important; text-shadow:0 3px 15px rgba(0,0,0,0.5); letter-spacing:-0.02em;">' . htmlspecialchars($slide['title'] ?? '') . '</h2>';
                if (!empty($slide['description'])) {
                    $html .= '<p class="hero-content-animated" style="margin:0 0 ' . $descMarginBottom . ' 0; font-size:' . $descSize . '; color:rgba(255,255,255,0.92) !important; line-height:1.65; text-shadow:0 2px 6px rgba(0,0,0,0.4); font-weight:300; ' . $descLineClamp . '">' . htmlspecialchars($slide['description']) . '</p>';
                }
                $html .= '</div>';
                
                if (!empty($slide['button_text'])) {
                    $html .= '<div class="hero-btn-animated">';
                    $html .= '<a href="' . htmlspecialchars($slide['button_link'] ?? '#') . '" style="display:inline-flex; align-items:center; gap:0.6rem; font-weight:600; font-size:' . $btnFontSize . '; background:var(--gold, #f4b41a); color:#0f172a !important; padding:' . $btnPadding . '; border-radius:50px; text-decoration:none; box-shadow:0 8px 25px rgba(244,180,26,0.35); transition:all 0.3s cubic-bezier(0.16,1,0.3,1);">';
                    $html .= '<span>' . htmlspecialchars($slide['button_text']) . '</span>';
                    $html .= '<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>';
                    $html .= '</a>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul></div></div>';
    
    // Script Init
    $html .= "<script>
        (function() {
            var attempts = 0;
            function initSplide_{$cleanInstanceId}() {
                var el = document.querySelector('#{$instanceDomId}');
                if (!el) return;
                if (typeof Splide === 'undefined') {
                    if (attempts++ < 60) {
                        setTimeout(initSplide_{$cleanInstanceId}, 50);
                    }
                    return;
                }
                try {
                    var splideOpts = {
                        type: '{$splideType}',
                        direction: '{$direction}',
                        rewind: true,
                        autoplay: {$autoplayVal},
                        interval: {$interval},
                        pauseOnHover: {$pauseOnHover},
                        pauseOnFocus: false,
                        resetProgress: false,
                        arrows: true,
                        pagination: true,
                        speed: {$speed},
                        easing: '{$easingCurve}'
                    };

                    if ('{$direction}' === 'ttb') {
                        var targetH = el.offsetHeight || el.clientHeight;
                        splideOpts.height = (targetH && targetH > 80) ? (targetH + 'px') : '{$containerHeight}';
                        splideOpts.autoHeight = false;
                    }

                    var splideInstance = new Splide(el, splideOpts);
                    splideInstance.mount();

                    if ('{$direction}' === 'ttb') {
                        window.addEventListener('resize', function() {
                            var newH = el.offsetHeight;
                            if (newH > 80 && splideInstance.options) {
                                splideInstance.options = { height: newH + 'px' };
                            }
                        });
                    }
                    " . ($isAutoplay ? "
                    if (splideInstance.Components && splideInstance.Components.Autoplay) {
                        splideInstance.Components.Autoplay.play();
                    }
                    " : "") . "
                } catch(err) {
                    console.warn('[PluginSlider] Init error:', err);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSplide_{$cleanInstanceId});
            } else {
                initSplide_{$cleanInstanceId}();
            }
        })();
    </script>";
    
    return $assets . $html;
}

// ---------------------------------------------------------
// 5. SHORTCODE PROCESSOR & CMS EVENT LISTENERS
// ---------------------------------------------------------
$shortcodeHandler = function($content, $defaultLayout = null) {
    if (!is_string($content) || !str_contains($content, '[slider')) return $content;
    
    return preg_replace_callback('/\[slider\s+([^\]]+)\]/i', function($matches) use ($defaultLayout) {
        $attrStr = $matches[1];
        $options = [];
        
        // Parse key="value" or key='value' or key=value
        if (preg_match_all('/([a-z_]+)\s*=\s*["\']?([^"\'\s]+)["\']?/i', $attrStr, $attrMatches, PREG_SET_ORDER)) {
            foreach ($attrMatches as $am) {
                $options[strtolower($am[1])] = $am[2];
            }
        }
        
        // If id wasn't an explicit attribute key (e.g. [slider promo-beranda])
        $sliderId = $options['id'] ?? '';
        if (empty($sliderId)) {
            if (preg_match('/^([a-z0-9_-]+)/i', trim($attrStr), $firstWord)) {
                $sliderId = $firstWord[1];
            }
        }
        
        if (empty($sliderId)) return '';

        // If layout was not specified in shortcode, but caller provided defaultLayout (e.g. 'article' for articles)
        if (empty($options['layout']) && !empty($defaultLayout)) {
            $options['layout'] = $defaultLayout;
        }
        
        return render_slider($sliderId, $options);
    }, $content);
};

// Listen on both standard content rendering and article rendering
$app->events->listen('content.render', function($content) use ($shortcodeHandler) {
    return $shortcodeHandler($content);
});

$app->events->listen('article.before_render', function($content) use ($shortcodeHandler) {
    return $shortcodeHandler($content, 'article');
});
