<?php
/**
 * Generic archive: rendered for both a category archive (/kategori/{slug})
 * and a module archive (/agenda, /prestasi, dst). Whichever data was passed
 * determines the heading and list source — no separate template per module.
 */
$isModuleArchive = isset($module, $items);
$heading = $isModuleArchive ? $module['label_plural'] : ($category['name'] ?? 'Arsip');
$list = $isModuleArchive ? $items : ($articles ?? []);
$baseRoute = $isModuleArchive ? rtrim($module['base_route'], '/') : '/artikel';
?>
<section class="archive">
    <h1><?= htmlspecialchars($heading) ?></h1>

    <?php if (empty($list)): ?>
        <p class="archive-empty">Belum ada konten.</p>
    <?php else: ?>
        <ul class="archive-list">
            <?php foreach ($list as $entry): ?>
                <li>
                    <a href="<?= $baseRoute ?>/<?= htmlspecialchars($entry['slug']) ?>">
                        <?= htmlspecialchars($entry['title']) ?>
                    </a>
                    <?php if (!empty($entry['excerpt'])): ?>
                        <p class="archive-excerpt"><?= htmlspecialchars($entry['excerpt']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
