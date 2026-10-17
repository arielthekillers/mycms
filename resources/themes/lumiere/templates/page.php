<article class="page-container">
    <header style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; font-weight: 700; letter-spacing: -0.025em; line-height: 1.2; margin-bottom: 0.5rem; color: var(--brand);">
            <?= htmlspecialchars($page['title'] ?? 'Halaman Tidak Ditemukan') ?>
        </h1>
    </header>

    <div class="page-content" style="font-size: 1.125rem; color: #334155;">
        <?= $page['content'] ?? '<p>Konten halaman kosong.</p>' ?>
    </div>
</article>

<style>
    .page-content p {
        margin-bottom: 1.5rem;
    }
    .page-content h2, .page-content h3 {
        margin-top: 2rem;
        color: var(--text-main);
    }
    .page-content img {
        max-width: 100%;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }
</style>
