<article class="article-container">
    <header style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; font-weight: 800; letter-spacing: -0.05em; line-height: 1.1; margin-bottom: 1rem;">
            <?= htmlspecialchars($article['title'] ?? 'Judul Tidak Ditemukan') ?>
        </h1>
        <div style="color: var(--text-muted); font-size: 1rem; display: flex; justify-content: center; gap: 1rem; align-items: center;">
            <span>Ditulis oleh <?= htmlspecialchars($article['author_name'] ?? 'Admin') ?></span>
            <span>&bull;</span>
            <span><?= date('d M Y', strtotime($article['created_at'] ?? 'now')) ?></span>
        </div>
    </header>

    <div class="article-content" style="font-size: 1.125rem; color: #334155;">
        <?= $article['content'] ?? '<p>Konten kosong.</p>' ?>
    </div>
    
    <div style="margin-top: 4rem; text-align: center;">
        <a href="/" class="btn">&larr; Kembali ke Beranda</a>
    </div>
</article>

<style>
    /* Styling khusus konten artikel (agar rapi saat membaca) */
    .article-content p {
        margin-bottom: 1.5rem;
    }
    .article-content h2 {
        font-size: 1.8rem;
        margin-top: 2.5rem;
        color: var(--text-main);
    }
    .article-content img {
        max-width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
</style>
