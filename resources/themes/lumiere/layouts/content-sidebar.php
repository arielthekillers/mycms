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
            --gold: #f4b41a;
            --gold-hover: #d99b0c;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-color: #ffffff;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --radius-md: 12px;
            --radius-lg: 20px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        img { max-width: 100%; height: auto; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            line-height: 1.7;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Header */
        .site-header {
            width: 100%;
            z-index: 900;
            background: var(--secondary-color, #0f172a);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            transition: all 0.35s ease;
        }
        .header-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
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
        }
        .header-logo img { height: 44px; max-width: 160px; object-fit: contain; }
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { text-decoration: none; color: white; font-weight: 500; font-size: 0.95rem; transition: color 0.25s; }
        .nav-links a:hover { color: var(--gold); }

        /* Dropdowns */
        .nav-links .nav-item { position: relative; display: flex; align-items: center; height: 100%; }
        .nav-links .sub-menu {
            position: absolute; top: 100%; left: 0;
            background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            min-width: 220px; padding: 0.6rem 0; border-radius: var(--radius-md);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); opacity: 0; visibility: hidden;
            transition: all 0.25s ease; z-index: 1000;
        }
        .nav-links .nav-item:hover > .sub-menu { opacity: 1; visibility: visible; }
        .nav-links .sub-menu a {
            color: rgba(255, 255, 255, 0.9); display: block; padding: 0.7rem 1.4rem;
            font-size: 0.88rem; transition: background 0.2s, color 0.2s;
        }
        .nav-links .sub-menu a:hover { background: rgba(255, 255, 255, 0.1); color: var(--gold); }

        .mobile-toggle-btn { display: none; background: transparent; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
        
        /* Layout Grid */
        .lumiere-layout-container {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 3rem;
            max-width: 1240px;
            margin: 0 auto;
            padding: 3.5rem 5%;
            width: 100%;
            flex: 1;
        }
        .site-main-column { min-width: 0; }
        .site-sidebar-column { position: relative; }
        .sidebar-sticky-wrap { position: sticky; top: 6.5rem; display: flex; flex-direction: column; gap: 2rem; }

        /* Sidebar Widgets */
        .site-sidebar-column .widget {
            background: white;
            border-radius: 18px;
            padding: 1.75rem 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
            border: 1px solid #edf2f7;
        }
        .site-sidebar-column .widget-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 1.25rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        .site-sidebar-column .widget-title::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 32px; height: 2px;
            background: var(--gold); border-radius: 2px;
        }
        .site-sidebar-column .widget ul { list-style: none; padding: 0; margin: 0; }
        .site-sidebar-column .widget li {
            padding: 0.65rem 0; border-bottom: 1px solid #f1f5f9;
        }
        .site-sidebar-column .widget li:last-child { border-bottom: none; }
        .site-sidebar-column .widget a {
            color: var(--text-main); text-decoration: none; font-size: 0.92rem; font-weight: 500;
            transition: color 0.2s;
        }
        .site-sidebar-column .widget a:hover { color: var(--teal); }

        /* Footer */
        .site-footer {
            background-color: var(--teal); color: white; padding: 4rem 5% 2rem; margin-top: auto;
        }
        .footer-grid {
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 3rem; max-width: 1240px; margin: 0 auto;
        }
        .footer-col h3 { font-size: 1.25rem; margin-bottom: 1.25rem; font-weight: 600; }
        .site-footer-bottom {
            background: #0a0f1c; color: rgba(255,255,255,0.7); padding: 1.5rem 5%; text-align: center; font-size: 0.88rem;
        }

        @media (max-width: 992px) {
            .nav-links { display: none; }
            .mobile-toggle-btn { display: block; }
            .lumiere-layout-container { grid-template-columns: 1fr; gap: 2.5rem; padding: 2rem 5%; }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
        }
    </style>
</head>
<body>
    <header class="site-header" id="mainHeader">
        <div class="header-container">
            <a href="/" class="header-logo">
                <?php if (!empty($site['site_logo'])): ?>
                    <img src="<?= htmlspecialchars($site['site_logo']) ?>" alt="Logo">
                <?php else: ?>
                    <i class="fas fa-mosque" style="font-size: 2rem; color: var(--gold);"></i>
                <?php endif; ?>
                <span><?= htmlspecialchars($site['site_name'] ?? 'Pondok Pesantren') ?></span>
            </a>
            <nav class="nav-links">
                <a href="/">Beranda</a>
                <a href="/halaman/profil">Profil</a>
                <a href="/kategori/kegiatan-santri">Kegiatan</a>
                <a href="/admin/login">Login</a>
            </nav>
        </div>
    </header>

    <div class="lumiere-layout-container">
        <main class="site-main-column">
            <?= $content ?>
        </main>

        <aside class="site-sidebar-column">
            <div class="sidebar-sticky-wrap">
                <?= $areas['sidebar-main'] ?? '' ?>
            </div>
        </aside>
    </div>

    <?php 
    $hasFooterWidgets = !empty($global['footer_1']) || !empty($global['footer_2']) || !empty($global['footer_3']);
    ?>
    <?php if ($hasFooterWidgets): ?>
    <footer class="site-footer">
        <div class="footer-grid">
            <div class="footer-col">
                <?= $global['footer_1'] ?? '' ?>
            </div>
            <div class="footer-col" style="text-align: center;">
                <?= $global['footer_2'] ?? '' ?>
            </div>
            <div class="footer-col">
                <?= $global['footer_3'] ?? '' ?>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <div class="site-footer-bottom">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($site['site_name'] ?? 'Pondok Pesantren') ?>. Seluruh hak cipta dilindungi.
    </div>
</body>
</html>
