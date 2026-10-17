<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Admin') ?> — Pesantren CMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars(\App\Core\Auth\Session::csrfToken()) ?>">
    <link rel="stylesheet" href="/assets/admin/admin.css">
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">Pesantren CMS</div>
            <nav id="admin-nav">
                <a href="/admin/panel" data-page="dashboard">Dashboard</a>
                <a href="/admin/panel/statistics" data-page="statistics">Statistik</a>
                
                <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 0.5rem 1rem;"></div>
                
                <a href="/admin/panel/articles" data-page="articles" data-permission="articles.view">Artikel</a>
                <a href="/admin/panel/media" data-page="media" data-permission="media.view">Media</a>
                <a href="/admin/panel/pages" data-page="pages" data-permission="pages.view">Halaman</a>
                <a href="/admin/panel/comments" data-page="comments">Komentar</a>
                
                <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 0.5rem 1rem;"></div>
                
                <a href="/admin/panel/themes" data-page="themes" data-permission="theme.manage">Tema</a>
                <a href="/admin/panel/widgets" data-page="widgets" data-permission="theme.manage">Widget</a>
                <a href="/admin/panel/menus" data-page="menus" data-permission="menus.manage">Menu</a>
                
                <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 0.5rem 1rem;"></div>
                
                <a href="/admin/panel/users" data-page="users" data-permission="users.manage">Pengguna</a>
                <a href="/admin/panel/plugins" data-page="plugins">Sistem Plugin</a>
                <a href="/admin/panel/settings" data-page="settings" data-permission="settings.manage">Pengaturan</a>
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
