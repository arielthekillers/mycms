<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Auth\Session;
use App\Core\View;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WidgetController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\PermissionMiddleware;

require dirname(__DIR__) . '/vendor/autoload.php';

if (!file_exists(dirname(__DIR__) . '/.env')) {
    require dirname(__DIR__) . '/resources/installer/install.php';
    exit;
}

$app = Application::bootstrap(dirname(__DIR__));

/** Renders an admin panel page: shared sidebar/topbar shell + a page-specific content partial + JS. */
function renderAdminPage(string $page, string $title): string
{
    return View::render(dirname(__DIR__) . '/resources/admin/layout.php', ['page' => $page, 'title' => $title]);
}

/**
 * Wraps a public GET handler with full-page caching. Only 200 responses are
 * cached (never 404s or redirects) — see Application's global invalidation
 * middleware for how the cache gets cleared on writes.
 */
function cachedPage(Application $app, int $ttl, callable $handler): callable
{
    return function ($request, $response) use ($app, $ttl, $handler) {
        if (isset($_GET['tp']) && $_GET['tp'] === '1') {
            return $handler($request, $response);
        }

        $key = 'fullpage:' . $request->uri();
        $cached = $app->cache->get($key);

        if ($cached !== null) {
            return $response->status($cached['status'])->html($cached['body']);
        }

        $result = $handler($request, $response);

        if ($result->getStatus() === 200) {
            $app->cache->set($key, ['status' => 200, 'body' => $result->getBody()], $ttl);
        }

        return $result;
    };
}

$pageCacheTtl = (int) ($_ENV['CACHE_TTL'] ?? 300);

$app->router->get('/', cachedPage($app, $pageCacheTtl, function ($request, $response) use ($app) {
    $recentArticles = $app->db->select(
        "SELECT title, slug FROM articles WHERE status = 'published' ORDER BY published_at DESC LIMIT 5"
    );

    return $response->html($app->theme()->render('homepage', [
        'recentArticles' => $recentArticles,
        'meta' => [
            'title' => 'Beranda - Pesantren CMS',
            'description' => 'Situs resmi Pesantren CMS.',
        ],
    ]));
}));

// --- Auth ---
$app->router->get('/admin/login', function ($request, $response) {
    if (Session::has('auth_user_id')) {
        return $response->redirect('/admin/panel');
    }

    return $response->html(View::render(dirname(__DIR__) . '/resources/admin/pages/login.php'));
});

$app->router->post('/admin/login', function ($request, $response) use ($app) {
    $ok = $app->auth->attempt((string) $request->input('identifier'), (string) $request->input('password'));

    return $ok
        ? $response->redirect('/admin/panel')
        : $response->status(401)->html(View::render(dirname(__DIR__) . '/resources/admin/pages/login.php', [
            'error' => 'Username/email atau kata sandi salah.',
        ]));
});

$app->router->post('/admin/logout', function ($request, $response) use ($app) {
    $app->auth->logout();

    return $response->redirect('/admin/login');
});

// --- Admin Panel (server-rendered shell + vanilla JS calling the JSON APIs below) ---
$authOnly = [new AuthMiddleware($app->auth)];

$app->router->get('/admin', fn ($req, $res) => $res->redirect('/admin/panel'));
$app->router->get('/admin/', fn ($req, $res) => $res->redirect('/admin/panel'));

$app->router->get('/admin/panel', fn ($req, $res) => $res->html(renderAdminPage('dashboard', 'Dashboard')), $authOnly);
$app->router->get('/admin/panel/statistics', fn ($req, $res) => $res->html(renderAdminPage('statistics', 'Statistik')), $authOnly);
$app->router->get('/admin/panel/articles', fn ($req, $res) => $res->html(renderAdminPage('articles', 'Artikel')), $authOnly);
$app->router->get('/admin/panel/pages', fn ($req, $res) => $res->html(renderAdminPage('pages', 'Halaman')), $authOnly);
$app->router->get('/admin/panel/media', fn ($req, $res) => $res->html(renderAdminPage('media', 'Media')), $authOnly);
$app->router->get('/admin/panel/comments', fn ($req, $res) => $res->html(renderAdminPage('comments', 'Komentar')), $authOnly);
$app->router->get('/admin/panel/menus', fn ($req, $res) => $res->html(renderAdminPage('menus', 'Menu')), $authOnly);
$app->router->get('/admin/panel/plugins', fn ($req, $res) => $res->html(renderAdminPage('plugins', 'Sistem Plugin')), $authOnly);
$app->router->get('/admin/panel/widgets', fn ($req, $res) => $res->html(renderAdminPage('widgets', 'Widget')), $authOnly);
$app->router->get('/admin/panel/themes', fn ($req, $res) => $res->html(renderAdminPage('themes', 'Tema')), $authOnly);
$app->router->get('/admin/panel/theme-options', fn ($req, $res) => $res->html(renderAdminPage('theme-options', 'Opsi Tema')), $authOnly);
$app->router->get('/admin/panel/settings', fn ($req, $res) => $res->html(renderAdminPage('settings', 'Pengaturan')), $authOnly);
$app->router->get('/admin/panel/users', fn ($req, $res) => $res->html(renderAdminPage('users', 'Pengguna')), $authOnly);
$app->router->get('/admin/panel/profile', fn ($req, $res) => $res->html(renderAdminPage('profile', 'Profil Saya')), $authOnly);

// --- User & Profile API ---
$userController = new UserController($app->db, $app->auth, $app->rbac);
$app->router->get('/admin/api/users', [$userController, 'index'], [new AuthMiddleware($app->auth)]);
$app->router->get('/admin/api/users/check', [$userController, 'check'], [new AuthMiddleware($app->auth)]);
$app->router->post('/admin/api/users', [$userController, 'store'], [new AuthMiddleware($app->auth)]);
$app->router->put('/admin/api/users/{id}', [$userController, 'update'], [new AuthMiddleware($app->auth)]);
$app->router->delete('/admin/api/users/{id}', [$userController, 'destroy'], [new AuthMiddleware($app->auth)]);

$app->router->get('/admin/api/roles', [$userController, 'roles'], [new AuthMiddleware($app->auth)]);
$app->router->get('/admin/api/profile', [$userController, 'showProfile'], [new AuthMiddleware($app->auth)]);
$app->router->put('/admin/api/profile', [$userController, 'updateProfile'], [new AuthMiddleware($app->auth)]);

// --- Settings Manager ---
$settingController = new SettingController($app->db);

$app->router->get('/admin/api/settings', [$settingController, 'index'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'settings.manage'),
]);

$app->router->put('/admin/api/settings', [$settingController, 'update'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'settings.manage'),
]);

// --- Plugins Manager ---
$pluginController = new PluginController($app->plugins);

$app->router->get('/admin/api/plugins', [$pluginController, 'index'], [
    new AuthMiddleware($app->auth),
]);

$app->router->post('/admin/api/plugins/upload', [$pluginController, 'upload'], [
    new AuthMiddleware($app->auth),
]);

$app->router->post('/admin/api/plugins/{directory}/toggle', [$pluginController, 'toggle'], [
    new AuthMiddleware($app->auth),
]);

$app->router->delete('/admin/api/plugins/{directory}', [$pluginController, 'destroy'], [
    new AuthMiddleware($app->auth),
]);

// --- Themes Manager ---
$themeController = new ThemeController($app->themeManager());

$app->router->get('/admin/api/themes', [$themeController, 'index'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->post('/admin/api/themes/upload', [$themeController, 'upload'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->post('/admin/api/themes/{directory}/activate', [$themeController, 'activate'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->delete('/admin/api/themes/{directory}', [$themeController, 'destroy'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

// --- Theme Options ---
$themeOptionsController = new \App\Http\Controllers\ThemeOptionsController(new \App\Theme\ThemeOptionsManager($app->db), $app->themeManager()->getActiveThemeKey());

$app->router->get('/admin/theme-options', [$themeOptionsController, 'index'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->put('/admin/theme-options', [$themeOptionsController, 'update'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

// --- Media Manager ---
$mediaController = new MediaController($app->media(), $app->db);

$app->router->get('/admin/media', [$mediaController, 'index'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'media.view'),
]);

$app->router->post('/admin/media', [$mediaController, 'store'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'media.upload'),
]);

$app->router->get('/admin/media/{id}/usages', [$mediaController, 'usages'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'media.view'),
]);

$app->router->delete('/admin/media/{id}', [$mediaController, 'destroy'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'media.delete'),
]);

// --- Content Manager: Articles ---
$articleController = new ArticleController($app->articles(), $app->auth, $app->theme());

$app->router->get('/admin/articles', [$articleController, 'index'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.view'),
]);

$app->router->get('/admin/authors', [$articleController, 'authors'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.view'),
]);

$app->router->get('/admin/articles/{id}', [$articleController, 'show'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.view'),
]);

$app->router->post('/admin/articles', [$articleController, 'store'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.create'),
]);

$app->router->put('/admin/articles/{id}', [$articleController, 'update'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.edit'),
]);

$app->router->delete('/admin/articles/{id}', [$articleController, 'destroy'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.delete'),
]);

$app->router->get('/admin/articles/{id}/revisions', [$articleController, 'revisions'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.view'),
]);

$app->router->post('/admin/articles/{id}/restore-revision', [$articleController, 'restoreRevision'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.edit'),
]);

// --- Content Manager: Categories ---
$categoryController = new CategoryController($app->categories());

$app->router->get('/admin/categories', [$categoryController, 'index'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.view'),
]);

$app->router->post('/admin/categories', [$categoryController, 'store'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.edit'),
]);

$app->router->delete('/admin/categories/{id}', [$categoryController, 'destroy'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.edit'),
]);

$app->router->post('/admin/articles/{id}/publish', [$articleController, 'publish'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.publish'),
]);

$app->router->post('/admin/articles/{id}/unpublish', [$articleController, 'unpublish'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.publish'),
]);

$app->router->post('/admin/articles/{id}/restore', [$articleController, 'restore'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'articles.delete'),
]);

// Public read — no auth, published-only, resolved by slug (foundation for the Menu Target Resolver).
$app->router->get('/artikel/{slug}', cachedPage($app, $pageCacheTtl, [$articleController, 'showPublic']));

// --- Content Manager: Static Pages ---
$pageController = new PageController($app->pages());

$app->router->get('/admin/pages', [$pageController, 'index'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'pages.view'),
]);

$app->router->get('/admin/pages/{id}', [$pageController, 'show'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'pages.view'),
]);

$app->router->post('/admin/pages', [$pageController, 'store'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'pages.create'),
]);

$app->router->put('/admin/pages/{id}', [$pageController, 'update'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'pages.edit'),
]);

$app->router->delete('/admin/pages/{id}', [$pageController, 'destroy'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'pages.delete'),
]);

// --- Menu Manager (Target Provider) ---
$menuController = new MenuController($app->menus());

$app->router->get('/admin/menus', [$menuController, 'index'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

$app->router->get('/admin/menus/{menuId}/items', [$menuController, 'items'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

$app->router->get('/admin/menu-target-search', [$menuController, 'searchTargets'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

$app->router->post('/admin/menus', [$menuController, 'store'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

$app->router->delete('/admin/menus/{menuId}', [$menuController, 'deleteMenu'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

$app->router->post('/admin/menus/{menuId}/items', [$menuController, 'addItem'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

$app->router->put('/admin/menus/{menuId}/reorder', [$menuController, 'reorderItems'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

$app->router->put('/admin/menu-items/{itemId}', [$menuController, 'updateItem'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

$app->router->delete('/admin/menu-items/{itemId}', [$menuController, 'deleteItem'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'menus.manage'),
]);

// Public — resolved menu tree (ready for the theme to render), e.g. GET /menus/main-nav
$app->router->get('/menus/{slug}', [$menuController, 'showTree']);



// --- Widget Manager (widget instances + drag-drop reorder) ---
$widgetController = new WidgetController($app->widgets());

$app->router->get('/admin/widgets', [$widgetController, 'types'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->get('/admin/widget-areas', [$widgetController, 'areas'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->get('/admin/widget-areas/{area}/instances', [$widgetController, 'instances'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->post('/admin/widget-areas/{area}/instances', [$widgetController, 'store'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->put('/admin/widget-instances/{id}', [$widgetController, 'update'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

$app->router->delete('/admin/widget-instances/{id}', [$widgetController, 'destroy'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

// Drag-drop reorder: body {"order": [id1, id2, id3]} in new top-to-bottom order.
$app->router->post('/admin/widget-areas/{area}/reorder', [$widgetController, 'reorder'], [
    new AuthMiddleware($app->auth),
    new PermissionMiddleware($app->rbac, $app->auth, 'theme.manage'),
]);

// --- Current user + effective permissions, for the admin UI to decide what to show ---
$app->router->get('/admin/me', function ($request, $response) use ($app) {
    $user = $app->auth->user();

    if ($user === null) {
        return $response->status(401)->json(['message' => 'Unauthenticated.']);
    }

    if (!empty($user['avatar_media_id'])) {
        $media = $app->db->selectOne("SELECT disk_path FROM media WHERE id = ?", [$user['avatar_media_id']]);
        if ($media) {
            $user['avatar_url'] = '/uploads/' . $media['disk_path'];
        }
    }

    unset($user['password_hash'], $user['two_factor_secret'], $user['two_factor_recovery_codes']);

    return $response->json([
        'data' => [
            'user' => $user,
            'permissions' => $app->rbac->permissionKeysFor((int) $user['id']),
        ],
    ]);
}, [new AuthMiddleware($app->auth)]);

// --- Serve uploaded media publicly (storage/uploads is outside public/ on purpose) ---
$app->router->get('/uploads/{path*}', function ($request, $response) use ($app) {
    $relative = (string) $request->param('path');
    $fullPath = realpath($app->basePath . '/storage/uploads/' . $relative);
    $uploadsRoot = realpath($app->basePath . '/storage/uploads');

    // Guard against path traversal — resolved path must stay inside storage/uploads.
    if ($fullPath === false || $uploadsRoot === false || !str_starts_with($fullPath, $uploadsRoot)) {
        return $response->status(404)->html('404 Not Found');
    }

    if (!is_file($fullPath)) {
        return $response->status(404)->html('404 Not Found');
    }

    $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

    return $response
        ->header('Cache-Control', 'public, max-age=86400')
        ->raw((string) file_get_contents($fullPath), $mime);
});

$app->router->get('/api/themes/assets', function ($request, $response) use ($app) {
    $theme = (string) $request->query('theme');
    $file = (string) $request->query('file');
    
    if (empty($theme) || empty($file)) {
        return $response->status(404)->html('404 Not Found');
    }

    $relative = $theme . '/' . $file;
    $fullPath = realpath($app->basePath . '/resources/themes/' . $relative);
    $themesRoot = realpath($app->basePath . '/resources/themes');

    // Guard against path traversal — resolved path must stay inside resources/themes.
    if ($fullPath === false || $themesRoot === false || !str_starts_with($fullPath, $themesRoot)) {
        return $response->status(404)->html('404 Not Found');
    }

    if (!is_file($fullPath)) {
        return $response->status(404)->html('404 Not Found');
    }

    // Only allow specific asset types for security (images, css, js, fonts)
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $allowed = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'css', 'js', 'woff', 'woff2', 'ttf', 'eot'];
    
    if (!in_array($ext, $allowed)) {
        return $response->status(403)->html('403 Forbidden');
    }

    $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

    return $response
        ->header('Cache-Control', 'public, max-age=86400')
        ->raw((string) file_get_contents($fullPath), $mime);
});


// --- Comments API ---
$app->router->post('/admin/api/comments/{id}/status', function ($request, $response) use ($app) {
    $id = (int) $request->param('id');
    $input = json_decode((string) $request->body(), true);
    $status = $input['status'] ?? 'pending';

    $valid = ['pending', 'approved', 'spam', 'trash'];
    if (!in_array($status, $valid)) {
        return $response->status(400)->json(['error' => 'Invalid status']);
    }

    $app->db->execute("UPDATE comments SET status = ? WHERE id = ?", [$status, $id]);
    return $response->json(['success' => true]);
}, $authOnly);

$app->router->post('/admin/api/comments/{id}/reply', function ($request, $response) use ($app) {
    $parentId = (int) $request->param('id');
    $input = json_decode((string) $request->body(), true);
    
    if (empty(trim($input['content'] ?? ''))) {
        return $response->status(400)->json(['error' => 'Balasan tidak boleh kosong.']);
    }

    $parent = $app->db->selectOne("SELECT article_id FROM comments WHERE id = ?", [$parentId]);
    if (!$parent) {
        return $response->status(404)->json(['error' => 'Komentar induk tidak ditemukan.']);
    }

    $user = $app->auth->user();

    $app->db->execute(
        "INSERT INTO comments (article_id, parent_id, author_name, author_email, content, status) VALUES (?, ?, ?, ?, ?, 'approved')",
        [$parent['article_id'], $parentId, $user['name'] ?? 'Admin', $user['email'] ?? 'admin@pesantren.com', trim($input['content'])]
    );

    // Also auto-approve the parent comment if we are replying to it
    $app->db->execute("UPDATE comments SET status = 'approved' WHERE id = ?", [$parentId]);

    return $response->json(['success' => true]);
}, $authOnly);

$app->router->delete('/admin/api/comments/{id}', function ($request, $response) use ($app) {
    $id = (int) $request->param('id');
    $app->db->execute("DELETE FROM comments WHERE id = ?", [$id]);
    return $response->json(['success' => true]);
}, $authOnly);

$app->router->post('/api/comments', function ($request, $response) use ($app) {
    // Public endpoint to submit a comment
    $input = json_decode((string) $request->body(), true) ?? $_POST;
    
    $articleId = (int) ($input['article_id'] ?? 0);
    $name = trim($input['author_name'] ?? '');
    $email = trim($input['author_email'] ?? '');
    $content = trim($input['content'] ?? '');

    if (!$articleId || !$name || !$email || !$content) {
        return $response->status(400)->json(['error' => 'Semua kolom wajib diisi.']);
    }

    // --- ANTI-SPAM ENGINE ---
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // 1. Block headless bots
    if (empty($userAgent) || stripos($content, '<script>') !== false) {
        return $response->status(400)->json(['error' => 'Spam terdeteksi. Permintaan ditolak.']);
    }

    // 2. Link Counting (Spammers usually drop multiple links)
    $linkCount = substr_count(strtolower($content), 'http://') + substr_count(strtolower($content), 'https://') + substr_count(strtolower($content), 'href=');
    
    // 3. Keyword Blacklist
    $spamKeywords = ['casino', 'viagra', 'crypto', 'bitcoin', 'slot gacor', 'judi', 'togel'];
    $containsSpamWords = false;
    foreach ($spamKeywords as $word) {
        if (stripos($content, $word) !== false) {
            $containsSpamWords = true;
            break;
        }
    }

    // Set initial status
    $status = 'pending';
    if ($linkCount >= 2 || $containsSpamWords) {
        $status = 'spam'; // Auto-route to spam tab instead of blocking completely, so admin can review false-positives
    }

    // 4. Rate Limiting (1 comment per 2 minutes per IP)
    // Actually we don't have ip column in comments table, so we skip IP rate-limiting for now unless we add an IP column, 
    // or we check the most recent comment time. Let's just rely on the keyword/link filter for now.
    // ------------------------

    $app->db->execute(
        "INSERT INTO comments (article_id, author_name, author_email, content, status) VALUES (?, ?, ?, ?, ?)",
        [$articleId, $name, $email, $content, $status]
    );

    if ($status === 'spam') {
        // We still tell the bot it was successful so they don't try to bypass
        return $response->json(['success' => true, 'message' => 'Komentar berhasil dikirim dan menunggu moderasi.']);
    }

    return $response->json(['success' => true, 'message' => 'Komentar berhasil dikirim dan menunggu moderasi.']);
});

// --- Analytics Tracker API ---
$app->router->post('/api/track', function ($request, $response) use ($app) {
    $input = json_decode((string) $request->body(), true);
    if (!isset($input['path'])) {
        return $response->status(400)->json(['error' => 'Missing path']);
    }

    $path = $input['path'];
    
    // Support for Cloudflare and Proxies to get real IP
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    // If multiple IPs are in X-Forwarded-For, take the first one
    if (str_contains($ip, ',')) {
        $ip = trim(explode(',', $ip)[0]);
    }
    
    $ipHash = hash('sha256', $ip);
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);
    $today = date('Y-m-d');

    // Basic anti-spam/bot filter
    if (stripos($userAgent, 'bot') !== false || stripos($userAgent, 'spider') !== false) {
        return $response->json(['status' => 'ignored']);
    }

    // 1. Record raw hit (can be purged later)
    $app->db->execute(
        "INSERT INTO statistics_hits (path, ip_hash, user_agent) VALUES (?, ?, ?)",
        [$path, $ipHash, $userAgent]
    );

    // 2. Check if this IP is a unique visitor for this path today
    $isUnique = false;
    $hitCount = $app->db->selectOne(
        "SELECT COUNT(*) as count FROM statistics_hits WHERE path = ? AND ip_hash = ? AND DATE(created_at) = ?",
        [$path, $ipHash, $today]
    );
    // If it's 1, it's the very first hit from this IP today
    if ($hitCount && $hitCount['count'] == 1) {
        $isUnique = true;
    }

    // 3. Update Daily Aggregation
    $exists = $app->db->selectOne("SELECT id FROM statistics_daily WHERE date = ? AND path = ?", [$today, $path]);
    
    if ($exists) {
        $query = "UPDATE statistics_daily SET views = views + 1";
        if ($isUnique) {
            $query .= ", unique_visitors = unique_visitors + 1";
        }
        $query .= " WHERE date = ? AND path = ?";
        $app->db->execute($query, [$today, $path]);
    } else {
        $app->db->execute(
            "INSERT INTO statistics_daily (date, path, views, unique_visitors) VALUES (?, ?, 1, ?)",
            [$today, $path, $isUnique ? 1 : 0]
        );
    }

    return $response->json(['status' => 'tracked']);
});

// --- Fallback: resolves /halaman/{slug}, /kategori/{slug}, and any registered
// module's archive (/agenda) or detail (/agenda/{slug}) page — no route needed
// per module, content_modules.base_route drives it all.
$resolver = $app->publicContentResolver();
$theme = $app->theme();

$app->router->fallback(cachedPage($app, $pageCacheTtl, function ($request, $response) use ($resolver, $theme) {
    $result = $resolver->resolve($request->uri());

    if ($result === null) {
        return $response->status(404)->html($theme->render('404'));
    }

    $templateByType = [
        'page' => 'page',
        'category_archive' => 'archive',
    ];

    $templateKey = $templateByType[$result['type']] ?? '404';
    $data = $result['data'];
    $data['meta'] = [
        'title' => match ($result['type']) {
            'page' => !empty($data['page']['meta_title']) ? $data['page']['meta_title'] : $data['page']['title'],
            'category_archive' => $data['category']['name'],
            default => 'Pesantren CMS',
        },
        'description' => match ($result['type']) {
            'page' => !empty($data['page']['meta_description']) ? $data['page']['meta_description'] : mb_substr(strip_tags($data['page']['content'] ?? ''), 0, 150),
            default => '',
        },
    ];

    return $response->html($theme->render($templateKey, $data));
}));

$app->run();
