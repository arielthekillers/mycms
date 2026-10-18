<?php
/**
 * Generic archive template for Lumiere theme.
 * Rendered for Category archives (/kategori/{slug}) and module archives.
 */
$isModuleArchive = isset($module, $items);
$heading = $isModuleArchive ? ($module['label_plural'] ?? 'Arsip') : ($category['name'] ?? 'Arsip Kategori');
$categoryDesc = $category['description'] ?? null;
$list = $isModuleArchive ? ($items ?? []) : ($articles ?? []);
$baseRoute = $isModuleArchive ? rtrim($module['base_route'] ?? '/artikel', '/') : '/artikel';
?>

<div class="archive-page-wrapper">
    <!-- Archive Hero Header -->
    <header class="archive-header">
        <div class="archive-header-container">
            <nav class="archive-breadcrumb" aria-label="Breadcrumb">
                <a href="/"><i class="fas fa-home"></i> Beranda</a>
                <span>&rsaquo;</span>
                <span class="current"><?= htmlspecialchars($heading) ?></span>
            </nav>
            <div class="archive-title-badge">
                <i class="far fa-folder-open"></i> Kategori
            </div>
            <h1 class="archive-title"><?= htmlspecialchars($heading) ?></h1>
            <?php if (!empty($categoryDesc)): ?>
                <p class="archive-subtitle"><?= htmlspecialchars($categoryDesc) ?></p>
            <?php else: ?>
                <p class="archive-subtitle">Menampilkan kumpulan arsip publikasi dan warta seputar <strong><?= htmlspecialchars($heading) ?></strong>.</p>
            <?php endif; ?>
            <div class="archive-count">
                <i class="fas fa-file-alt"></i> <?= count($list) ?> Artikel Ditemukan
            </div>
        </div>
    </header>

    <!-- Archive Content List -->
    <main class="archive-content-container">
        <?php if (empty($list)): ?>
            <div class="archive-empty-card">
                <div class="empty-icon">
                    <i class="far fa-folder-open"></i>
                </div>
                <h3>Belum Ada Konten</h3>
                <p>Saat ini belum ada artikel yang dipublikasikan dalam kategori ini.</p>
                <a href="/" class="btn-return-home"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
            </div>
        <?php else: ?>
            <div class="archive-grid">
                <?php foreach ($list as $index => $entry): 
                    $fallbackImages = [
                        'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&q=80&w=800',
                        'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800',
                        'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800'
                    ];
                    $thumb = $fallbackImages[$index % 3];
                    $dateStr = !empty($entry['published_at']) ? date('d M Y', strtotime($entry['published_at'])) : date('d M Y', strtotime($entry['created_at'] ?? 'now'));
                    $excerpt = !empty($entry['excerpt']) ? $entry['excerpt'] : mb_substr(strip_tags($entry['content'] ?? ''), 0, 140) . '...';
                ?>
                    <article class="archive-card">
                        <a href="<?= $baseRoute ?>/<?= htmlspecialchars($entry['slug']) ?>" class="card-img-link" tabindex="-1">
                            <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($entry['title']) ?>" loading="lazy">
                            <span class="card-badge"><?= htmlspecialchars($heading) ?></span>
                        </a>
                        <div class="card-body">
                            <div class="card-meta">
                                <span><i class="far fa-calendar-alt"></i> <?= $dateStr ?></span>
                            </div>
                            <h2 class="card-title">
                                <a href="<?= $baseRoute ?>/<?= htmlspecialchars($entry['slug']) ?>">
                                    <?= htmlspecialchars($entry['title']) ?>
                                </a>
                            </h2>
                            <p class="card-excerpt">
                                <?= htmlspecialchars($excerpt) ?>
                            </p>
                            <div class="card-footer">
                                <a href="<?= $baseRoute ?>/<?= htmlspecialchars($entry['slug']) ?>" class="card-read-more">
                                    Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
    .archive-page-wrapper {
        background-color: var(--bg-light, #f8fafc);
        min-height: 80vh;
        padding-bottom: 5rem;
    }
    .archive-header {
        background: linear-gradient(135deg, var(--secondary-color, #0f172a) 0%, #1e293b 100%);
        color: white;
        padding: 4rem 5% 5rem;
        position: relative;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .archive-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 0%, rgba(27, 178, 166, 0.25) 0%, transparent 70%);
        pointer-events: none;
    }
    .archive-header-container {
        max-width: 900px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    .archive-breadcrumb {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
    }
    .archive-breadcrumb a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        transition: color 0.2s;
    }
    .archive-breadcrumb a:hover {
        color: var(--gold);
    }
    .archive-breadcrumb .current {
        color: var(--gold);
        font-weight: 500;
    }
    .archive-title-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.35rem 1rem;
        border-radius: 50px;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: 1rem;
    }
    .archive-title {
        font-size: 2.75rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 0.75rem;
        line-height: 1.2;
    }
    .archive-subtitle {
        font-size: 1.05rem;
        opacity: 0.88;
        max-width: 650px;
        margin: 0 auto 1.5rem;
        line-height: 1.6;
    }
    .archive-count {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.08);
        padding: 0.4rem 1.2rem;
        border-radius: 50px;
        font-size: 0.88rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
    }

    .archive-content-container {
        max-width: 1240px;
        margin: -2.5rem auto 0;
        padding: 0 5%;
        position: relative;
        z-index: 5;
    }
    .archive-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }
    .archive-card {
        background: white;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        border: 1px solid #edf2f7;
        display: flex;
        flex-direction: column;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .archive-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        border-color: var(--teal);
    }
    .card-img-link {
        position: relative;
        display: block;
        overflow: hidden;
        height: 220px;
    }
    .card-img-link img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .archive-card:hover .card-img-link img {
        transform: scale(1.08);
    }
    .card-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: white;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 50px;
        letter-spacing: 0.02em;
    }
    .card-body {
        padding: 1.75rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .card-meta {
        font-size: 0.84rem;
        color: var(--teal);
        font-weight: 600;
        margin-bottom: 0.6rem;
    }
    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 0.85rem;
    }
    .card-title a {
        color: var(--text-main);
        text-decoration: none;
        transition: color 0.2s;
    }
    .archive-card:hover .card-title a {
        color: var(--teal);
    }
    .card-excerpt {
        font-size: 0.92rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .card-footer {
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }
    .card-read-more {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--teal);
        font-weight: 600;
        font-size: 0.88rem;
        text-decoration: none;
        transition: gap 0.2s;
    }
    .archive-card:hover .card-read-more {
        gap: 0.7rem;
    }

    /* Empty Card */
    .archive-empty-card {
        background: white;
        border-radius: 24px;
        padding: 5rem 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        max-width: 600px;
        margin: 2rem auto;
    }
    .empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        margin: 0 auto 1.5rem;
    }
    .archive-empty-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }
    .archive-empty-card p {
        color: var(--text-muted);
        margin-bottom: 2rem;
        font-size: 1rem;
    }
    .btn-return-home {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        background: var(--teal);
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.25s ease;
    }
    .btn-return-home:hover {
        background: var(--teal-dark);
        transform: translateY(-2px);
    }

    @media (max-width: 992px) {
        .archive-grid { grid-template-columns: repeat(2, 1fr); }
        .archive-title { font-size: 2.25rem; }
    }
    @media (max-width: 640px) {
        .archive-grid { grid-template-columns: 1fr; }
        .archive-title { font-size: 1.85rem; }
        .archive-header { padding: 3rem 5% 4rem; }
    }
</style>
