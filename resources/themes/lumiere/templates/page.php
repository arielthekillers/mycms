<?php
/**
 * Static Page Template - Theme Lumiere
 */
$app = \App\Core\Application::getInstance();

$featuredImg = null;
if (!empty($page['featured_media_id'])) {
    try {
        $media = $app->db->selectOne("SELECT disk_path FROM media WHERE id = ?", [$page['featured_media_id']]);
        if ($media) {
            $featuredImg = '/uploads/' . $media['disk_path'];
        }
    } catch (\Exception $e) {}
}
?>

<div class="page-template-wrapper">
    <!-- Page Header Banner -->
    <header class="page-banner-header">
        <div class="page-banner-container">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <a href="/"><i class="fas fa-home"></i> Beranda</a>
                <span>&rsaquo;</span>
                <span class="current">Halaman</span>
            </nav>
            <h1 class="page-main-title"><?= htmlspecialchars($page['title'] ?? 'Halaman') ?></h1>
            <div class="page-title-decoration"></div>
        </div>
    </header>

    <!-- Page Body Container -->
    <div class="page-content-wrapper">
        <article class="page-card">
            <?php if ($featuredImg): ?>
                <div class="page-featured-img">
                    <img src="<?= htmlspecialchars($featuredImg) ?>" alt="<?= htmlspecialchars($page['title']) ?>">
                </div>
            <?php endif; ?>

            <div class="page-rich-content">
                <?php
                $renderedPageContent = $page['content'] ?? '<p>Konten halaman belum diisi.</p>';
                if (!empty($app->events)) {
                    $renderedPageContent = $app->events->dispatch('content.render', $renderedPageContent);
                }
                ?>
                <?= $renderedPageContent ?>
            </div>

            <div class="page-card-footer">
                <a href="/" class="btn-page-home"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
            </div>
        </article>
    </div>
</div>

<style>
    .page-template-wrapper {
        background-color: var(--bg-light, #f8fafc);
        min-height: 85vh;
        padding-bottom: 5rem;
    }
    .page-banner-header {
        background: linear-gradient(135deg, var(--secondary-color, #0f172a) 0%, #1e293b 100%);
        color: white;
        padding: 4.5rem 5% 5.5rem;
        text-align: center;
        position: relative;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .page-banner-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 10%, rgba(27, 178, 166, 0.22) 0%, transparent 75%);
        pointer-events: none;
    }
    .page-banner-container {
        max-width: 900px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    .page-breadcrumb {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
    }
    .page-breadcrumb a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        transition: color 0.2s;
    }
    .page-breadcrumb a:hover {
        color: var(--gold);
    }
    .page-breadcrumb .current {
        color: var(--gold);
        font-weight: 500;
    }
    .page-main-title {
        font-size: 2.75rem;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.02em;
        margin-bottom: 1rem;
        text-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .page-title-decoration {
        width: 60px;
        height: 3px;
        background: var(--gold);
        margin: 0 auto;
        border-radius: 3px;
    }

    .page-content-wrapper {
        max-width: 980px;
        margin: -3rem auto 0;
        padding: 0 5%;
        position: relative;
        z-index: 5;
    }
    .page-card {
        background: white;
        border-radius: 24px;
        padding: 4rem;
        box-shadow: 0 10px 35px rgba(0,0,0,0.06);
        border: 1px solid #edf2f7;
    }
    .page-featured-img {
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 3rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .page-featured-img img {
        width: 100%;
        max-height: 480px;
        object-fit: cover;
        display: block;
    }

    /* Rich Content Typography */
    .page-rich-content {
        font-size: 1.15rem;
        line-height: 1.9;
        color: #334155;
    }
    .page-rich-content p {
        margin-bottom: 1.75rem;
    }
    .page-rich-content h2 {
        font-size: 1.85rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 2.5rem 0 1rem;
        letter-spacing: -0.01em;
    }
    .page-rich-content h3 {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 2rem 0 0.85rem;
    }
    .page-rich-content blockquote {
        border-left: 4px solid var(--teal);
        background: #f8fafc;
        padding: 1.5rem 2rem;
        margin: 2rem 0;
        border-radius: 0 16px 16px 0;
        font-style: italic;
        color: #475569;
    }
    .page-rich-content ul, .page-rich-content ol {
        margin: 1.5rem 0 2rem 2rem;
    }
    .page-rich-content li {
        margin-bottom: 0.6rem;
    }
    .page-rich-content img {
        max-width: 100%;
        height: auto;
        border-radius: 14px;
        margin: 2rem auto;
        display: block;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .page-rich-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
    }
    .page-rich-content th, .page-rich-content td {
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        text-align: left;
    }
    .page-rich-content th {
        background: #f8fafc;
        font-weight: 700;
    }

    .page-card-footer {
        margin-top: 3.5rem;
        padding-top: 2rem;
        border-top: 1px solid #f1f5f9;
        text-align: center;
    }
    .btn-page-home {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        background: transparent;
        color: var(--teal);
        border: 2px solid var(--teal);
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .btn-page-home:hover {
        background: var(--teal);
        color: white;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .page-main-title { font-size: 2rem; }
        .page-banner-header { padding: 3.5rem 5% 4.5rem; }
        .page-content-wrapper { margin-top: -2rem; padding: 0 4%; }
        .page-card { padding: 2.25rem 1.5rem; border-radius: 18px; }
    }
</style>
