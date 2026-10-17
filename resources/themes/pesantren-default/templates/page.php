<?php /** @var array $page */ ?>
<article class="single-page">
    <h1><?= htmlspecialchars($page['title']) ?></h1>

    <div class="page-content">
        <?= $page['content'] ?? '' ?>
    </div>
</article>
