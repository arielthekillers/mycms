<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['title'] ?? $site['site_name'] ?? 'Pondok Pesantren') ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? $site['site_description'] ?? '') ?>">
    <?php if (!empty($site['site_favicon'])): ?>
        <link rel="icon" href="<?= htmlspecialchars($site['site_favicon']) ?>">
    <?php endif; ?>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?= $themeCss ?? '' ?>
    
    <style>
        :root {
            --primary-color: <?= htmlspecialchars($options['primary_color'] ?? '#1bb2a6') ?>;
            --secondary-color: <?= htmlspecialchars($options['secondary_color'] ?? '#0f172a') ?>;
            --teal: var(--primary-color, #1bb2a6);
            --teal-dark: color-mix(in srgb, var(--teal) 82%, black);
            --teal-light: color-mix(in srgb, var(--teal) 15%, white);
            --gold: #f4b41a;
            --gold-hover: #d99b0c;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-color: #ffffff;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --radius-md: 12px;
            --radius-lg: 20px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 8px 25px rgba(0,0,0,0.07);
            --shadow-lg: 0 16px 40px rgba(0,0,0,0.12);
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.7;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Header & Navbar */
        .site-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 900;
            background: linear-gradient(to bottom, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0) 100%);
            border-bottom: none;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .header-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 1.5rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            transition: padding 0.3s ease;
        }
        .site-header.is-sticky {
            position: fixed;
        }
        .site-header.scrolled {
            background: rgba(15, 23, 42, 0.94);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.25);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .site-header.solid-mode {
            position: relative;
            background: var(--secondary-color, #0f172a);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .site-header.solid-mode.is-sticky {
            position: sticky;
            top: 0;
        }
        .site-header.scrolled .header-container {
            padding: 0.85rem 5%;
        }
        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.15rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.25;
            transition: transform 0.25s ease;
        }
        .header-logo:hover {
            transform: translateY(-1px);
        }
        .site-header:not(.scrolled):not(.solid-mode) .header-logo,
        .site-header:not(.scrolled):not(.solid-mode) .nav-links > .nav-item > a,
        .site-header:not(.scrolled):not(.solid-mode) .nav-links > a {
            text-shadow: 0 2px 4px rgba(0,0,0,0.65), 0 0 2px rgba(0,0,0,0.4);
        }
        .header-logo span {
            max-width: <?= htmlspecialchars($options['logo_text_width'] ?? '220px') ?>;
            white-space: normal;
            display: inline-block;
        }
        .header-logo img {
            height: 44px;
            max-width: 160px;
            object-fit: contain;
        }
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: white;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.25s ease;
            position: relative;
        }
        .nav-links a:hover {
            color: var(--gold);
        }
        
        /* Dropdown Sub-menu */
        .nav-links .nav-item {
            position: relative;
            display: flex;
            align-items: center;
            height: 100%;
        }
        .nav-links .sub-menu, .nav-links .flyout-menu {
            position: absolute;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            min-width: 220px;
            padding: 0.6rem 0;
            border-radius: var(--radius-md);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1000;
        }
        .nav-links .sub-menu {
            top: 100%;
            left: 0;
            transform: translateY(12px);
        }
        .nav-links .nav-item:hover > .sub-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .nav-links .sub-nav-item {
            position: relative;
            display: block;
        }
        .nav-links .flyout-menu {
            top: 0;
            left: 100%;
            transform: translateX(12px);
            margin-left: -4px;
        }
        .nav-links .sub-nav-item:hover > .flyout-menu {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        .nav-links .sub-menu a, .nav-links .flyout-menu a {
            color: rgba(255, 255, 255, 0.92);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.7rem 1.4rem;
            font-size: 0.88rem;
            text-decoration: none;
            font-weight: 400;
            transition: background 0.2s, color 0.2s, padding-left 0.2s;
        }
        .nav-links .sub-menu a:hover, .nav-links .flyout-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--gold);
            padding-left: 1.6rem;
        }
        
        /* Edge Collision Classes */
        .nav-links .sub-menu.open-left {
            left: auto;
            right: 0;
        }
        .nav-links .flyout-menu.open-left {
            left: auto;
            right: 100%;
            margin-left: 0;
            margin-right: -4px;
        }

        /* Mobile Nav Toggle Button */
        .mobile-toggle-btn {
            display: none;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: transform 0.2s, color 0.2s;
        }
        .mobile-toggle-btn:hover {
            color: var(--gold);
            transform: scale(1.05);
        }

        /* Mobile Drawer & Backdrop */
        .mobile-nav-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 15, 28, 0.7);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .mobile-nav-backdrop.active {
            display: block;
            opacity: 1;
        }
        .mobile-nav-drawer {
            position: fixed;
            top: 0;
            right: -320px;
            width: 300px;
            max-width: 85vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.97);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            color: white;
            z-index: 999;
            box-shadow: -5px 0 30px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-y: auto;
        }
        .mobile-nav-drawer.active {
            right: 0;
        }
        .mobile-nav-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .mobile-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
        }
        .mobile-logo img {
            height: 32px;
            max-width: 120px;
            object-fit: contain;
        }
        .mobile-close-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }
        .mobile-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }
        .mobile-nav-body {
            padding: 1rem 0;
            flex: 1;
        }
        .mobile-menu-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .mobile-menu-item {
            position: relative;
        }
        .mobile-menu-link-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }
        .mobile-menu-link-wrapper a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            flex: 1;
            transition: color 0.2s;
        }
        .mobile-menu-link-wrapper a:hover {
            color: var(--gold);
        }
        .mobile-submenu-toggle {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            padding: 0.4rem 0.6rem;
            cursor: pointer;
            transition: transform 0.25s, color 0.2s;
        }
        .mobile-submenu-toggle.active {
            transform: rotate(180deg);
            color: var(--gold);
        }
        .mobile-submenu {
            display: none;
            background: rgba(0, 0, 0, 0.25);
            padding-left: 1rem;
        }
        .mobile-submenu.active {
            display: block;
        }

        /* Site Main Container */
        .site-main {
            flex: 1;
            width: 100%;
        }
        .site-main.subpage-main {
            min-height: 65vh;
            padding-bottom: 4rem;
        }

        /* Footer */
        .site-footer {
            background-color: var(--teal);
            color: white;
            padding: 4.5rem 5% 2.5rem;
            position: relative;
            margin-top: auto;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 3rem;
            max-width: 1240px;
            margin: 0 auto;
        }
        .footer-col h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            position: relative;
            padding-bottom: 0.5rem;
        }
        .footer-col h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 36px;
            height: 2px;
            background-color: var(--gold);
            border-radius: 2px;
        }
        .footer-center h3::after {
            left: 50%;
            transform: translateX(-50%);
        }
        .footer-col p {
            font-size: 0.92rem;
            margin-bottom: 0.75rem;
            opacity: 0.92;
            line-height: 1.65;
        }
        .footer-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.75rem;
            flex-wrap: wrap;
        }
        .footer-btn {
            background-color: white;
            color: var(--teal);
            padding: 0.5rem 1.6rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.25s ease;
        }
        .footer-btn:hover {
            background-color: var(--gold);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        .footer-center {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .footer-center img {
            width: 130px;
            margin-bottom: 1.25rem;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.15));
        }
        .footer-social {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .footer-social a {
            color: white;
            background: rgba(255,255,255,0.18);
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }
        .footer-social a:hover {
            background: var(--gold);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .site-footer-bottom {
            background-color: rgba(10, 15, 28, 1);
            color: rgba(255, 255, 255, 0.7);
            padding: 1.5rem 5%;
            text-align: center;
            font-size: 0.88rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        .footer-bottom-container {
            max-width: 1240px;
            margin: 0 auto;
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--teal);
            color: white;
            border: none;
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 800;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .back-to-top:hover {
            background: var(--gold);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .nav-links { display: none; }
            .mobile-toggle-btn { display: block; }
            .footer-grid { grid-template-columns: 1fr; gap: 2.5rem; text-align: center; }
            .footer-col h3::after { left: 50%; transform: translateX(-50%); }
            .footer-buttons, .footer-social { justify-content: center; }
            .site-header { background: rgba(15, 23, 42, 0.95); position: relative; }
        }
    </style>
</head>
<body>
    
    <?php
    $headerStyle = $options['header_style'] ?? 'auto';
    $isSolid = false;
    
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $isHomepage = ($uri === '/' || $uri === '/index.php' || empty($uri));

    $hasHero = $isHomepage && !empty($global['homepage_hero']);
    
    if ($headerStyle === 'solid') {
        $isSolid = true;
    } else if ($headerStyle === 'transparent') {
        $isSolid = !$hasHero;
    } else {
        // auto mode
        $isSolid = (!$isHomepage) || ($isHomepage && !$hasHero);
    }
    
    $isSticky = ($options['sticky_header'] ?? 'yes') === 'yes';
    $headerClasses = [];
    if ($isSticky) $headerClasses[] = 'is-sticky';
    if ($isSolid) $headerClasses[] = 'solid-mode';
    ?>
    
    <header class="site-header <?= implode(' ', $headerClasses) ?>" id="mainHeader">
        <div class="header-container">
            <a href="/" class="header-logo">
                <?php $logoDisplay = $options['logo_display'] ?? 'logo_text'; ?>
                
                <?php if ($logoDisplay === 'logo_text' || $logoDisplay === 'logo_only'): ?>
                    <?php if (!empty($site['site_logo'])): ?>
                        <img src="<?= htmlspecialchars($site['site_logo']) ?>" alt="Logo">
                    <?php else: ?>
                        <i class="fas fa-mosque" style="font-size: 2rem; color: var(--gold);"></i>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($logoDisplay === 'logo_text' || $logoDisplay === 'text_only'): ?>
                    <span><?= htmlspecialchars($site['site_name'] ?? 'Pondok Pesantren') ?></span>
                <?php endif; ?>
            </a>
            
            <nav class="nav-links">
                <?php
                $headerMenuSlug = $options['menu_header'] ?? '';
                $headerMenu = [];
                if (!empty($headerMenuSlug)) {
                    try {
                        $headerMenu = \App\Core\Application::getInstance()->menus()->getResolvedTree($headerMenuSlug);
                    } catch (\Exception $e) {}
                }
                
                if (!empty($headerMenu)):
                    $renderMenu = function($items, $level = 0) use (&$renderMenu) {
                        if (empty($items)) return;
                        
                        foreach ($items as $item) {
                            $hasChildren = !empty($item['children']);
                            $target = !empty($item['open_new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
                            
                            if ($level === 0) {
                                echo '<div class="nav-item">';
                                echo '<a href="' . htmlspecialchars($item['url']) . '"' . $target . '>';
                                echo htmlspecialchars($item['label']);
                                if ($hasChildren) echo ' <i class="fas fa-chevron-down" style="font-size: 0.7em; margin-left: 0.3rem;"></i>';
                                echo '</a>';
                                
                                if ($hasChildren) {
                                    echo '<div class="sub-menu">';
                                    $renderMenu($item['children'], $level + 1);
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="sub-nav-item">';
                                echo '<a href="' . htmlspecialchars($item['url']) . '"' . $target . '>';
                                echo htmlspecialchars($item['label']);
                                if ($hasChildren) echo ' <i class="fas fa-chevron-right" style="font-size: 0.7em; margin-left: 0.5rem;"></i>';
                                echo '</a>';
                                
                                if ($hasChildren) {
                                    echo '<div class="flyout-menu">';
                                    $renderMenu($item['children'], $level + 1);
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                        }
                    };
                    $renderMenu($headerMenu);
                else:
                ?>
                    <a href="/">Beranda</a>
                    <a href="/halaman/profil">Profil</a>
                    <a href="/kategori/kegiatan-santri">Kegiatan</a>
                    <a href="/admin/login">Login</a>
                <?php endif; ?>
            </nav>
            
            <button class="mobile-toggle-btn" id="mobileNavToggle" aria-label="Buka Menu Navigasi">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Mobile Drawer & Backdrop -->
    <div class="mobile-nav-backdrop" id="mobileNavBackdrop"></div>
    <div class="mobile-nav-drawer" id="mobileNavDrawer">
        <div class="mobile-nav-header">
            <div class="mobile-logo">
                <?php if (!empty($site['site_logo'])): ?>
                    <img src="<?= htmlspecialchars($site['site_logo']) ?>" alt="Logo">
                <?php else: ?>
                    <i class="fas fa-mosque" style="font-size: 1.3rem; color: var(--gold);"></i>
                <?php endif; ?>
                <span><?= htmlspecialchars($site['site_name'] ?? 'Pesantren') ?></span>
            </div>
            <button class="mobile-close-btn" id="mobileNavClose" aria-label="Tutup Menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mobile-nav-body">
            <?php
            if (!empty($headerMenu)):
                $renderMobileMenu = function($items, $level = 0) use (&$renderMobileMenu) {
                    if (empty($items)) return;
                    echo '<ul class="mobile-menu-list level-' . $level . '">';
                    foreach ($items as $item) {
                        $hasChildren = !empty($item['children']);
                        $target = !empty($item['open_new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
                        echo '<li class="mobile-menu-item' . ($hasChildren ? ' has-children' : '') . '">';
                        echo '<div class="mobile-menu-link-wrapper">';
                        echo '<a href="' . htmlspecialchars($item['url']) . '"' . $target . '>' . htmlspecialchars($item['label']) . '</a>';
                        if ($hasChildren) {
                            echo '<button type="button" class="mobile-submenu-toggle" aria-label="Buka Submenu"><i class="fas fa-chevron-down"></i></button>';
                        }
                        echo '</div>';
                        if ($hasChildren) {
                            echo '<div class="mobile-submenu">';
                            $renderMobileMenu($item['children'], $level + 1);
                            echo '</div>';
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                };
                $renderMobileMenu($headerMenu);
            else:
            ?>
                <ul class="mobile-menu-list">
                    <li class="mobile-menu-item"><div class="mobile-menu-link-wrapper"><a href="/">Beranda</a></div></li>
                    <li class="mobile-menu-item"><div class="mobile-menu-link-wrapper"><a href="/halaman/profil">Profil</a></div></li>
                    <li class="mobile-menu-item"><div class="mobile-menu-link-wrapper"><a href="/kategori/kegiatan-santri">Kegiatan</a></div></li>
                    <li class="mobile-menu-item"><div class="mobile-menu-link-wrapper"><a href="/admin/login">Login Admin</a></div></li>
                </ul>
            <?php endif; ?>
        </nav>
    </div>

    <main class="site-main <?= $isSolid ? 'subpage-main' : '' ?>">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="footer-grid">
            <div class="footer-col">
                <?php if (!empty($global['footer_1'])): ?>
                    <?= $global['footer_1'] ?>
                <?php else: ?>
                    <h3>Hubungi Kami</h3>
                    <p><i class="fas fa-envelope" style="margin-right: 8px; color: var(--gold);"></i> pesantrendarussalambogor@gmail.com</p>
                    <p><i class="fas fa-phone" style="margin-right: 8px; color: var(--gold);"></i> 021 1234567</p>
                    
                    <h3 style="margin-top: 2rem;">Alamat</h3>
                    <p><i class="fas fa-map-marker-alt" style="margin-right: 8px; color: var(--gold);"></i> Kampung Cibeureum, Jl. Cibeureum, Petir, Dramaga, Kabupaten Bogor, Jawa Barat 16680</p>
                    
                    <div class="footer-buttons">
                        <a href="https://maps.google.com" target="_blank" rel="noopener" class="footer-btn"><i class="fas fa-location-arrow"></i> Google Maps</a>
                        <a href="mailto:pesantrendarussalambogor@gmail.com" class="footer-btn"><i class="fas fa-paper-plane"></i> Kirim Email</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="footer-col footer-center">
                <?php if (!empty($global['footer_2'])): ?>
                    <?= $global['footer_2'] ?>
                <?php else: ?>
                    <i class="fas fa-mosque" style="font-size: 5rem; color: var(--gold); margin-bottom: 1.25rem;"></i>
                    <h3 style="margin-bottom: 0;">PONDOK PESANTREN</h3>
                    <h3>DARUSSALAM BOGOR</h3>
                <?php endif; ?>
            </div>
            
            <div class="footer-col">
                <?php if (!empty($global['footer_3'])): ?>
                    <?= $global['footer_3'] ?>
                <?php else: ?>
                    <h3>Tentang Lembaga</h3>
                    <p style="text-align: justify; font-size: 0.9rem;">
                        Pondok Pesantren Darussalam Bogor menyelenggarakan kegiatan belajar-mengajar 
                        sistem asrama (Islamic Boarding School) berupaya membekali para santrinya 
                        dengan pengetahuan agama dan umum, serta program pembinaan khusus dengan 
                        nilai-nilai kepesantrenan.
                    </p>
                    
                    <div class="footer-social">
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                    
                    <?php
                    $footerMenuSlug = $options['menu_footer'] ?? '';
                    if (!empty($footerMenuSlug)):
                    ?>
                        <div style="margin-top: 1.75rem; display: flex; flex-wrap: wrap; gap: 0.85rem;">
                        <?php
                            try {
                                $footerMenu = \App\Core\Application::getInstance()->menus()->getResolvedTree($footerMenuSlug);
                                foreach ($footerMenu as $item): 
                                    $target = !empty($item['open_new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
                        ?>
                                    <a href="<?= htmlspecialchars($item['url']) ?>"<?= $target ?> style="font-size: 0.85rem; color: #fff; text-decoration: underline; opacity: 0.85; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.85'"><?= htmlspecialchars($item['label']) ?></a>
                        <?php
                                endforeach;
                            } catch (\Exception $e) {}
                        ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    
    <div class="site-footer-bottom">
        <div class="footer-bottom-container">
            <?php if (!empty($global['footer_bottom'])): ?>
                <?= $global['footer_bottom'] ?>
            <?php else: ?>
                &copy; <?= date('Y') ?> <?= htmlspecialchars($site['site_name'] ?? 'Pondok Pesantren') ?>. Seluruh hak cipta dilindungi undang-undang.
            <?php endif; ?>
        </div>
    </div>

    <!-- Floating Back to Top Button -->
    <button id="backToTop" class="back-to-top" aria-label="Kembali ke atas">
        <i class="fas fa-chevron-up"></i>
    </button>

</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Desktop Sub-menu boundary collision detection
    const dropdownContainers = document.querySelectorAll('.nav-links .nav-item, .nav-links .sub-nav-item');
    dropdownContainers.forEach(container => {
        container.addEventListener('mouseenter', function() {
            let submenu = null;
            for (let i = 0; i < this.children.length; i++) {
                if (this.children[i].classList.contains('sub-menu') || this.children[i].classList.contains('flyout-menu')) {
                    submenu = this.children[i];
                    break;
                }
            }
            if (submenu) {
                submenu.classList.remove('open-left');
                const rect = submenu.getBoundingClientRect();
                if (rect.right > (window.innerWidth || document.documentElement.clientWidth)) {
                    submenu.classList.add('open-left');
                }
            }
        });
    });

    // Mobile Navigation Drawer Toggle
    const mobileToggle = document.getElementById('mobileNavToggle');
    const mobileDrawer = document.getElementById('mobileNavDrawer');
    const mobileBackdrop = document.getElementById('mobileNavBackdrop');
    const mobileClose = document.getElementById('mobileNavClose');

    function openMobileMenu() {
        if (mobileDrawer) mobileDrawer.classList.add('active');
        if (mobileBackdrop) mobileBackdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        if (mobileDrawer) mobileDrawer.classList.remove('active');
        if (mobileBackdrop) mobileBackdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileToggle) mobileToggle.addEventListener('click', openMobileMenu);
    if (mobileClose) mobileClose.addEventListener('click', closeMobileMenu);
    if (mobileBackdrop) mobileBackdrop.addEventListener('click', closeMobileMenu);

    // Mobile Accordion Submenu Toggles
    const submenuToggles = document.querySelectorAll('.mobile-submenu-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            const parentLi = this.closest('.mobile-menu-item');
            if (parentLi) {
                const submenu = parentLi.querySelector('.mobile-submenu');
                if (submenu) {
                    submenu.classList.toggle('active');
                }
            }
        });
    });

    // Back to top button
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 400) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
</script>

<?php if ($isSticky): ?>
<script>
window.addEventListener('scroll', function() {
    const header = document.getElementById('mainHeader');
    if (header) {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
});
</script>
<?php endif; ?>
</html>
