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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?= $themeCss ?? '' ?>
    
    <style>
        :root {
            --teal: #1bb2a6;
            --teal-dark: #128279;
            --gold: #f4b41a;
            --text-main: #333333;
            --text-muted: #666666;
            --bg-color: #ffffff;
            --bg-light: #f9f9f9;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Header & Navbar */
        .site-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 50;
            background: linear-gradient(to bottom, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0) 100%);
            border-bottom: none;
            transition: all 0.3s ease;
        }
        .header-container {
            max-width: 1200px;
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
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .site-header.solid-mode {
            position: relative;
            background: rgba(15, 23, 42, 1);
        }
        .site-header.solid-mode.is-sticky {
            position: sticky;
            top: 0;
        }
        .site-header.scrolled .header-container {
            padding: 0.75rem 5%;
        }
        .header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: uppercase;
            line-height: 1.2;
            transition: all 0.3s;
        }
        .site-header:not(.scrolled):not(.solid-mode) .header-logo,
        .site-header:not(.scrolled):not(.solid-mode) .nav-links > .nav-item > a,
        .site-header:not(.scrolled):not(.solid-mode) .nav-links > a {
            text-shadow: 0 2px 4px rgba(0,0,0,0.7), 0 0 2px rgba(0,0,0,0.5);
        }
        .header-logo span {
            max-width: <?= htmlspecialchars($options['logo_text_width'] ?? '200px') ?>; /* Membatasi lebar teks agar bisa turun ke bawah */
            white-space: normal;
            display: inline-block;
        }
        .header-logo img {
            height: 40px;
            max-width: 150px;
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
            transition: color 0.2s ease;
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
            background: rgba(15, 23, 42, 0.85); /* Elegan dark glassmorphism */
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            min-width: 200px;
            padding: 0.5rem 0;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 100;
        }
        .nav-links .sub-menu {
            top: 100%;
            left: 0;
            transform: translateY(10px);
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
            transform: translateX(10px);
            margin-left: -5px;
        }
        .nav-links .sub-nav-item:hover > .flyout-menu {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        .nav-links .sub-menu a, .nav-links .flyout-menu a {
            color: rgba(255, 255, 255, 0.9);
            display: block;
            padding: 0.6rem 1.25rem;
            font-size: 0.85rem;
            text-decoration: none;
            font-weight: 400;
        }
        .nav-links .sub-menu a:hover, .nav-links .flyout-menu a:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--gold);
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
            margin-right: -5px;
        }


        /* Footer */
        .site-footer {
            background-color: var(--teal);
            color: white;
            padding: 4rem 5% 2rem;
            position: relative;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-col h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .footer-col p {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
        .footer-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .footer-btn {
            background-color: white;
            color: var(--teal);
            padding: 0.4rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .footer-btn:hover {
            background-color: var(--gold);
            color: white;
        }
        .footer-center {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .footer-center img {
            width: 120px;
            margin-bottom: 1rem;
        }
        .footer-social {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .footer-social a {
            color: white;
            background: rgba(255,255,255,0.2);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s;
        }
        .footer-social a:hover {
            background: var(--gold);
            transform: translateY(-3px);
        }
        .site-footer-bottom {
            background-color: rgba(10, 15, 28, 1); /* Lebih gelap dari biru laut footer */
            color: rgba(255, 255, 255, 0.7);
            padding: 1.5rem 5%;
            text-align: center;
            font-size: 0.9rem;
        }
        .footer-bottom-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
            .footer-buttons, .footer-social { justify-content: center; }
            .site-header { background: var(--teal); position: relative; }
        }
    </style>
</head>
<body>
    
    <?php
    $headerStyle = $options['header_style'] ?? 'auto';
    $isSolid = false;
    
    if ($headerStyle === 'solid') {
        $isSolid = true;
    } else if ($headerStyle === 'transparent') {
        $isSolid = false;
    } else {
        // auto
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $isHomepage = ($uri === '/' || $uri === '/index.php' || empty($uri));
        $hasSlider = !empty($options['homepage_slider']);
        
        $isSolid = (!$isHomepage) || ($isHomepage && !$hasSlider);
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
                if (!empty($headerMenuSlug)):
                    try {
                        $headerMenu = \App\Core\Application::getInstance()->menus()->getResolvedTree($headerMenuSlug);
                        
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
                                    if ($hasChildren) echo ' <i class="fas fa-chevron-right" style="font-size: 0.7em; float: right; margin-top: 0.25rem;"></i>';
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
                        
                    } catch (\Exception $e) {
                        echo '<!-- Error loading header menu: ' . htmlspecialchars($e->getMessage()) . ' -->';
                    }
                else:
                ?>
                    <a href="/">Beranda</a>
                    <a href="#">Profil <i class="fas fa-chevron-down" style="font-size: 0.7em;"></i></a>
                    <a href="#">Kesiswaan <i class="fas fa-chevron-down" style="font-size: 0.7em;"></i></a>
                    <a href="#">Terkini</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="footer-grid">
            <div class="footer-col">
                <?php if (!empty($global['footer_1'])): ?>
                    <?= $global['footer_1'] ?>
                <?php else: ?>
                    <h3>Hubungi Kami</h3>
                    <p>pesantrendarussalambogor@gmail.com</p>
                    <p>021 1234567</p>
                    
                    <h3 style="margin-top: 2rem;">Alamat</h3>
                    <p>Kampung Cibeureum, Jl. Cibeureum, Petir, Dramaga,<br>Kabupaten Bogor, Jawa Barat 16680</p>
                    
                    <div class="footer-buttons">
                        <a href="#" class="footer-btn">Alamat</a>
                        <a href="#" class="footer-btn">Email</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="footer-col footer-center">
                <?php if (!empty($global['footer_2'])): ?>
                    <?= $global['footer_2'] ?>
                <?php else: ?>
                    <i class="fas fa-mosque" style="font-size: 5rem; color: var(--gold); margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0;">PONDOK PESANTREN</h3>
                    <h3>DARUSSALAM BOGOR</h3>
                <?php endif; ?>
            </div>
            
            <div class="footer-col">
                <?php if (!empty($global['footer_3'])): ?>
                    <?= $global['footer_3'] ?>
                <?php else: ?>
                    <p style="text-align: justify; font-size: 0.85rem;">
                        Pondok Pesantren Darussalam Bogor menyelenggarakan kegiatan belajar-mengajar 
                        sistem asrama (Islamic Boarding School) berupaya membekali para santrinya 
                        dengan pengetahuan agama dan umum, serta program pembinaan khusus dengan 
                        nilai-nilai kepesantrenan.
                    </p>
                    
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                    
                    <?php
                    $footerMenuSlug = $options['menu_footer'] ?? '';
                    if (!empty($footerMenuSlug)):
                    ?>
                        <div style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.75rem;">
                        <?php
                            try {
                                $footerMenu = \App\Core\Application::getInstance()->menus()->getResolvedTree($footerMenuSlug);
                                foreach ($footerMenu as $item): 
                                    $target = !empty($item['open_new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
                        ?>
                                    <a href="<?= htmlspecialchars($item['url']) ?>"<?= $target ?> style="font-size: 0.85rem; color: #fff; text-decoration: underline;"><?= htmlspecialchars($item['label']) ?></a>
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
                &copy; <?= date('Y') ?> <?= htmlspecialchars($site['site_name'] ?? 'Pondok Pesantren') ?>. All rights reserved.
            <?php endif; ?>
        </div>
    </div>

</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
                
                // Cek apakah menu keluar dari batas kanan layar
                if (rect.right > (window.innerWidth || document.documentElement.clientWidth)) {
                    submenu.classList.add('open-left');
                }
            }
        });
    });
});
</script>
<?php if ($isSticky): ?>
<script>
window.addEventListener('scroll', function() {
    const header = document.getElementById('mainHeader');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});
</script>
<?php endif; ?>
</html>
