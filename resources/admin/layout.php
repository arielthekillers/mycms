<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Admin') ?> — Pesantren CMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars(\App\Core\Auth\Session::csrfToken()) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/admin/admin.css">
</head>
<body>
    <?php
    $appInstance = \App\Core\Application::getInstance();
    $activePlugins = [];
    try {
        $discovered = $appInstance->plugins->discover();
        foreach ($discovered as $p) {
            if (!empty($p['is_active']) && !empty($p['admin_url'])) {
                $activePlugins[] = $p;
            }
        }
    } catch (\Throwable $e) {
        // Fallback
    }
    ?>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a href="/admin/panel" class="admin-brand" title="Dashboard Admin">
                <div class="brand-logo-icon">
                    <i class="fas fa-mosque"></i>
                </div>
                <div class="brand-text-wrap">
                    <span class="brand-title">Pesantren CMS</span>
                    <span class="brand-tag">Administrator</span>
                </div>
            </a>
            <nav id="admin-nav">
                <!-- Group 1: UTAMA -->
                <div class="nav-section-title">UTAMA</div>
                <a href="/admin/panel" data-page="dashboard">
                    <i class="nav-icon fas fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/panel/statistics" data-page="statistics">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <span>Statistik</span>
                </a>
                
                <div class="nav-divider"></div>
                
                <!-- Group 2: KONTEN -->
                <div class="nav-section-title">KONTEN</div>
                <a href="/admin/panel/articles" data-page="articles" data-permission="articles.view">
                    <i class="nav-icon fas fa-newspaper"></i>
                    <span>Artikel</span>
                </a>
                <a href="/admin/panel/pages" data-page="pages" data-permission="pages.view">
                    <i class="nav-icon fas fa-file-lines"></i>
                    <span>Halaman</span>
                </a>
                <a href="/admin/panel/media" data-page="media" data-permission="media.view">
                    <i class="nav-icon fas fa-images"></i>
                    <span>Media</span>
                </a>
                <a href="/admin/panel/comments" data-page="comments">
                    <i class="nav-icon fas fa-comments"></i>
                    <span>Komentar</span>
                </a>
                
                <div class="nav-divider"></div>
                
                <!-- Group 3: TAMPILAN -->
                <div class="nav-section-title">TAMPILAN</div>
                <a href="/admin/panel/themes" data-page="themes" data-permission="theme.manage">
                    <i class="nav-icon fas fa-palette"></i>
                    <span>Tema</span>
                </a>
                <a href="/admin/panel/menus" data-page="menus" data-permission="menus.manage">
                    <i class="nav-icon fas fa-bars-staggered"></i>
                    <span>Menu</span>
                </a>
                <a href="/admin/panel/widgets" data-page="widgets" data-permission="theme.manage">
                    <i class="nav-icon fas fa-cubes"></i>
                    <span>Widget</span>
                </a>
                
                <!-- Group 4: BLOK KHUSUS PLUGIN -->
                <div class="nav-divider nav-divider-highlight"></div>
                
                <div class="nav-section-title nav-plugin-header">
                    <span>PLUGIN</span>
                    <?php if (!empty($activePlugins)): ?>
                        <span class="nav-badge-pill"><?= count($activePlugins) ?> Aktif</span>
                    <?php endif; ?>
                </div>
                
                <a href="/admin/panel/plugins" data-page="plugins">
                    <i class="nav-icon fas fa-puzzle-piece"></i>
                    <span>Sistem Plugin</span>
                </a>
                
                <?php if (!empty($activePlugins)): ?>
                    <div class="nav-plugin-submenu">
                        <?php foreach ($activePlugins as $plugin): ?>
                            <a href="<?= htmlspecialchars($plugin['admin_url']) ?>" class="nav-plugin-link" data-page="<?= htmlspecialchars(basename($plugin['admin_url'])) ?>">
                                <i class="nav-icon <?= htmlspecialchars($plugin['icon'] ?? 'fas fa-puzzle-piece') ?>"></i>
                                <span><?= htmlspecialchars($plugin['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="nav-divider nav-divider-highlight"></div>
                
                <!-- Group 5: SISTEM -->
                <div class="nav-section-title">SISTEM</div>
                <a href="/admin/panel/users" data-page="users" data-permission="users.manage">
                    <i class="nav-icon fas fa-users-gear"></i>
                    <span>Pengguna</span>
                </a>
                <a href="/admin/panel/settings" data-page="settings" data-permission="settings.manage">
                    <i class="nav-icon fas fa-gear"></i>
                    <span>Pengaturan</span>
                </a>
            </nav>
        </aside>

        <div class="sidebar-overlay" id="sidebar-overlay"></div>
        <div class="admin-main">
            <header class="admin-topbar">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button type="button" class="btn-icon mobile-menu-toggle" id="mobile-menu-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1><?= htmlspecialchars($title ?? 'Admin') ?></h1>
                </div>
                <div class="admin-user">
                    <a href="/" target="_blank" rel="noopener noreferrer" class="btn-view-site" title="Buka Halaman Depan Website (Tab Baru)">
                        <i class="fas fa-globe"></i>
                        <span class="btn-view-site-text">Lihat Website</span>
                        <i class="fas fa-arrow-up-right-from-square" style="font-size: 0.7rem; opacity: 0.85;"></i>
                    </a>
                    <div class="topbar-divider"></div>
                    <a href="/admin/panel/profile" id="admin-current-user" class="admin-current-user-info" style="text-decoration:none; color:inherit;"></a>
                    <form method="post" action="/admin/logout" style="display:inline" title="Keluar">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Core\Auth\Session::csrfToken()) ?>">
                        <button type="submit" class="btn-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </button>
                    </form>
                </div>
            </header>

            <main class="admin-content">
                <div id="admin-alert" class="admin-alert" hidden></div>
                <?php 
                if (isset($content)) {
                    echo $content;
                } else {
                    include __DIR__ . "/pages/{$page}.php"; 
                }
                ?>
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    <script src="/assets/admin/admin.js"></script>
    <?php if (file_exists(__DIR__ . "/../../public/assets/admin/pages/{$page}.js")): ?>
        <script src="/assets/admin/pages/<?= htmlspecialchars($page) ?>.js"></script>
    <?php endif; ?>
</body>
</html>
