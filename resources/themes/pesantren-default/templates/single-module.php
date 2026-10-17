<?php
/** @var array $module content_modules row */
/** @var array $item module_contents row */
$meta = json_decode($item['meta'] ?? '[]', true) ?: [];
?>
<article class="single-module single-module-<?= htmlspecialchars($module['key_name']) ?>">
    <p class="module-breadcrumb">
        <a href="<?= htmlspecialchars($module['base_route']) ?>"><?= htmlspecialchars($module['label_plural']) ?></a>
    </p>

    <h1><?= htmlspecialchars($item['title']) ?></h1>

    <?php if ($meta !== []): ?>
        <dl class="module-meta">
            <?php foreach ($meta as $key => $value): ?>
                <dt><?= htmlspecialchars((string) $key) ?></dt>
                <dd><?= htmlspecialchars((string) $value) ?></dd>
            <?php endforeach; ?>
        </dl>
    <?php endif; ?>

    <div class="module-content">
        <?= $item['content'] ?? '' ?>
    </div>
</article>
