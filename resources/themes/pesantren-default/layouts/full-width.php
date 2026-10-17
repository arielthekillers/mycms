<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($meta['title'] ?? 'Pesantren CMS') ?></title>
    <?php if (!empty($meta['description'])): ?>
    <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta['description']) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= htmlspecialchars($meta['title'] ?? 'Pesantren CMS') ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($meta['image'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($meta['image']) ?>">
    <?php endif; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $themeCss ?? '' ?>
    <style>
        body { font-family: var(--font-body); color: var(--secondary-color); margin: 0; }
        h1, h2, h3 { font-family: var(--font-heading); }
        a { color: var(--primary-color); }
        .site-header, .site-footer { padding: 1rem 2rem; background: #f8fafc; }
        .site-main-full { padding: 2rem; max-width: 900px; margin: 0 auto; }
    </style>
</head>
<body class="layout-full-width">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="site-main site-main-full">
        <?= $content ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
