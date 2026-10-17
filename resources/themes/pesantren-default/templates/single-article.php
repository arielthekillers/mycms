<?php /** @var array $article */ ?>
<article class="single-article">
    <h1><?= htmlspecialchars($article['title']) ?></h1>

    <?php if (!empty($article['categories'])): ?>
        <div class="article-categories">
            <?php foreach ($article['categories'] as $category): ?>
                <a href="/kategori/<?= htmlspecialchars($category['slug']) ?>" class="category-tag">
                    <?= htmlspecialchars($category['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="article-content">
        <?= $article['content'] ?? '' ?>
    </div>
</article>
