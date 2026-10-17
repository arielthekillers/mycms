<section class="homepage-hero">
    <h1>Selamat Datang di Pesantren CMS</h1>
    <p>Portal informasi, kegiatan, dan publikasi konten pesantren.</p>
</section>

<?php if (!empty($recentArticles)): ?>
<section class="homepage-articles">
    <h2>Artikel Terbaru</h2>
    <ul class="article-list">
        <?php foreach ($recentArticles as $article): ?>
            <li>
                <a href="/artikel/<?= htmlspecialchars($article['slug']) ?>">
                    <?= htmlspecialchars($article['title']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>
