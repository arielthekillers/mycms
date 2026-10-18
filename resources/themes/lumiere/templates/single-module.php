<?php
/**
 * Generic Module Detail Template - Theme Lumiere
 */
$meta = json_decode($item['meta'] ?? '[]', true) ?: [];
$moduleLabel = $module['label_plural'] ?? 'Modul';
$baseRoute = $module['base_route'] ?? '/';
?>

<div class="module-page-wrapper">
    <header class="module-header">
        <div class="module-header-container">
            <nav class="module-breadcrumb">
                <a href="/"><i class="fas fa-home"></i> Beranda</a>
                <span>&rsaquo;</span>
                <a href="<?= htmlspecialchars($baseRoute) ?>"><?= htmlspecialchars($moduleLabel) ?></a>
                <span>&rsaquo;</span>
                <span class="current">Detail</span>
            </nav>
            <h1 class="module-title"><?= htmlspecialchars($item['title'] ?? 'Detail') ?></h1>
        </div>
    </header>

    <div class="module-body-container">
        <article class="module-card">
            <?php if (!empty($meta)): ?>
                <div class="module-meta-grid">
                    <?php foreach ($meta as $key => $val): ?>
                        <div class="meta-spec-item">
                            <span class="spec-label"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $key))) ?></span>
                            <span class="spec-value"><?= htmlspecialchars((string) $val) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="module-rich-content">
                <?= $item['content'] ?? '' ?>
            </div>

            <div class="module-footer">
                <a href="<?= htmlspecialchars($baseRoute) ?>" class="btn-back-module">
                    <i class="fas fa-arrow-left"></i> Kembali ke <?= htmlspecialchars($moduleLabel) ?>
                </a>
            </div>
        </article>
    </div>
</div>

<style>
    .module-page-wrapper {
        background: var(--bg-light, #f8fafc);
        min-height: 80vh;
        padding-bottom: 5rem;
    }
    .module-header {
        background: linear-gradient(135deg, var(--secondary-color, #0f172a) 0%, #1e293b 100%);
        color: white;
        padding: 4.5rem 5% 5.5rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .module-header-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .module-breadcrumb {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
    }
    .module-breadcrumb a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
    }
    .module-breadcrumb a:hover {
        color: var(--gold);
    }
    .module-breadcrumb .current {
        color: var(--gold);
    }
    .module-title {
        font-size: 2.75rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.25;
    }
    .module-body-container {
        max-width: 960px;
        margin: -3rem auto 0;
        padding: 0 5%;
        position: relative;
        z-index: 5;
    }
    .module-card {
        background: white;
        border-radius: 24px;
        padding: 3.5rem 4rem;
        box-shadow: 0 10px 35px rgba(0,0,0,0.06);
        border: 1px solid #edf2f7;
    }
    .module-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem 2rem;
        margin-bottom: 2.5rem;
    }
    .meta-spec-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .spec-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
    }
    .spec-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
    }
    .module-rich-content {
        font-size: 1.15rem;
        line-height: 1.9;
        color: #334155;
    }
    .module-rich-content p {
        margin-bottom: 1.75rem;
    }
    .module-footer {
        margin-top: 3.5rem;
        padding-top: 2rem;
        border-top: 1px solid #f1f5f9;
        text-align: center;
    }
    .btn-back-module {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        border: 2px solid var(--teal);
        color: var(--teal);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.25s;
    }
    .btn-back-module:hover {
        background: var(--teal);
        color: white;
    }
    @media (max-width: 768px) {
        .module-title { font-size: 2rem; }
        .module-card { padding: 2.25rem 1.5rem; }
    }
</style>
