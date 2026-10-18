<?php
/**
 * Static Page Template - Theme Lumière
 * High-end editorial layout for institutional pages, programs, and documents.
 */

$app = \App\Core\Application::getInstance();

// 1. Featured Image with Caption & Alt
$featuredImg = null;
$featuredImgCaption = null;
$featuredImgAlt = $page['title'] ?? 'Halaman';
if (!empty($page['featured_media_id'])) {
    try {
        $media = $app->db->selectOne("SELECT disk_path, caption, alt_text FROM media WHERE id = ?", [$page['featured_media_id']]);
        if ($media) {
            $featuredImg = '/uploads/' . $media['disk_path'];
            $featuredImgCaption = !empty($media['caption']) ? $media['caption'] : null;
            if (!empty($media['alt_text'])) {
                $featuredImgAlt = $media['alt_text'];
            }
        }
    } catch (\Exception $e) {}
}

// 2. Reading Time & Dates
$wordCount = str_word_count(strip_tags($page['content'] ?? ''));
$readTime = max(1, (int) ceil($wordCount / 200));

$updatedDate = !empty($page['updated_at']) ? date('d F Y', strtotime($page['updated_at'])) : date('d F Y', strtotime($page['created_at'] ?? 'now'));
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'];

// 3. Other Pages for Context Navigation
$otherPages = [];
try {
    $otherPages = $app->db->select(
        "SELECT id, title, slug FROM pages WHERE status = 'published' AND id != :id ORDER BY title ASC LIMIT 3",
        ['id' => $page['id'] ?? 0]
    );
} catch (\Exception $e) {}
?>

<!-- Reading Progress Bar -->
<div class="reading-progress-track">
    <div class="reading-progress-bar" id="readingProgressBar"></div>
</div>

<div class="page-template-wrapper">
    <!-- Editorial Page Banner Header -->
    <header class="page-banner-header">
        <div class="page-banner-container">
            <div class="page-header-topline">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <a href="/"><i class="fas fa-home"></i> Beranda</a>
                    <span class="sep"><i class="fas fa-chevron-right"></i></span>
                    <span class="current"><?= htmlspecialchars($page['title'] ?? 'Halaman') ?></span>
                </nav>
            </div>

            <h1 class="page-main-title"><?= htmlspecialchars($page['title'] ?? 'Halaman') ?></h1>

            <!-- Meta & Reading Controls Bar -->
            <div class="page-meta-bar">
                <div class="page-meta-item" title="Terakhir Diperbarui">
                    <i class="far fa-calendar-check meta-icon"></i>
                    <span>Diperbarui <?= $updatedDate ?></span>
                </div>
                <span class="page-meta-dot">&bull;</span>
                <div class="page-meta-item" title="Estimasi Waktu Baca">
                    <i class="far fa-clock meta-icon"></i>
                    <span><?= $readTime ?> Menit Baca</span>
                </div>

                <div class="page-tools-group">
                    <div class="font-sizer-widget" title="Atur Ukuran Teks">
                        <span class="sizer-label"><i class="fas fa-font"></i></span>
                        <button type="button" class="btn-text-size" id="btnPageDecreaseFont" title="Perkecil Font">A-</button>
                        <button type="button" class="btn-text-size active" id="btnPageResetFont" title="Ukuran Font Normal">A</button>
                        <button type="button" class="btn-text-size" id="btnPageIncreaseFont" title="Perbesar Font">A+</button>
                    </div>
                    <button type="button" class="btn-print-page" onclick="window.print()" title="Cetak Halaman Ini">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Floating Quick Interaction Dock -->
    <aside class="page-flying-dock" aria-label="Aksi Halaman">
        <div class="dock-inner">
            <span class="dock-brand-label">BAGIKAN</span>
            <a href="https://api.whatsapp.com/send?text=<?= urlencode(($page['title'] ?? 'Halaman') . ' ' . $currentUrl) ?>" target="_blank" rel="noopener" class="dock-btn share-wa" title="Bagikan ke WhatsApp" aria-label="Bagikan ke WhatsApp">
                <i class="fab fa-whatsapp"></i>
                <span class="dock-tooltip">WhatsApp</span>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="dock-btn share-fb" title="Bagikan ke Facebook" aria-label="Bagikan ke Facebook">
                <i class="fab fa-facebook-f"></i>
                <span class="dock-tooltip">Facebook</span>
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= urlencode(($page['title'] ?? 'Halaman')) ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="dock-btn share-tw" title="Bagikan ke X" aria-label="Bagikan ke X">
                <i class="fab fa-x-twitter"></i>
                <span class="dock-tooltip">X</span>
            </a>
            <a href="https://t.me/share/url?url=<?= urlencode($currentUrl) ?>&text=<?= urlencode(($page['title'] ?? 'Halaman')) ?>" target="_blank" rel="noopener" class="dock-btn share-tg" title="Bagikan ke Telegram" aria-label="Bagikan ke Telegram">
                <i class="fab fa-telegram-plane"></i>
                <span class="dock-tooltip">Telegram</span>
            </a>
            <button type="button" class="dock-btn share-copy" id="btnCopyPageUrl" onclick="copyPageUrl()" title="Salin Tautan" aria-label="Salin Tautan">
                <i class="fas fa-link"></i>
                <span class="dock-tooltip">Salin Tautan</span>
            </button>
            <div class="dock-divider"></div>
            <button type="button" class="dock-btn dock-btn-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Kembali ke Atas" aria-label="Kembali ke Atas">
                <i class="fas fa-chevron-up"></i>
                <span class="dock-tooltip">Ke Atas</span>
            </button>
        </div>
    </aside>

    <!-- Page Body Container -->
    <div class="page-content-wrapper">
        <article class="page-card">
            <?php if ($featuredImg): ?>
                <figure class="page-featured-frame">
                    <div class="page-featured-canvas">
                        <img src="<?= htmlspecialchars($featuredImg) ?>" alt="<?= htmlspecialchars($featuredImgAlt) ?>" class="page-featured-img">
                    </div>
                    <?php if ($featuredImgCaption): ?>
                        <figcaption class="page-featured-caption">
                            <i class="fas fa-camera-retro"></i>
                            <span><?= htmlspecialchars($featuredImgCaption) ?></span>
                        </figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>

            <!-- Rich Content Typography -->
            <div class="page-rich-content" id="pageRichContent">
                <?php
                $renderedPageContent = $page['content'] ?? '<p>Konten halaman belum diisi.</p>';
                if (!empty($app->events)) {
                    $renderedPageContent = $app->events->dispatch('content.render', $renderedPageContent);
                }
                ?>
                <?= $renderedPageContent ?>
            </div>



            <!-- Other Important Pages Navigation -->
            <?php if (!empty($otherPages)): ?>
                <div class="other-pages-section">
                    <h4 class="other-pages-heading">
                        <i class="fas fa-bookmark"></i> Halaman Terkait Lainnya
                    </h4>
                    <div class="other-pages-grid">
                        <?php foreach ($otherPages as $op): ?>
                            <a href="/halaman/<?= htmlspecialchars($op['slug']) ?>" class="other-page-card">
                                <span class="op-badge"><i class="fas fa-file-alt"></i> Halaman</span>
                                <h5 class="op-title"><?= htmlspecialchars($op['title']) ?></h5>
                                <span class="op-link">Buka Halaman <i class="fas fa-arrow-right"></i></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="page-card-footer">
                <a href="/" class="btn-page-home"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
            </div>
        </article>
    </div>
</div>

<!-- Toast Feedback for Copy Link -->
<div id="pageCopiedToast" class="page-copied-toast">
    <div class="toast-icon"><i class="fas fa-check"></i></div>
    <div class="toast-body">
        <strong>Tautan Tersalin!</strong>
        <span>URL halaman berhasil disalin ke clipboard Anda.</span>
    </div>
</div>

<style>
    /* ========================================================
       Theme Lumière - Premium Editorial Static Page Styling
       ======================================================== */

    /* Reading Progress Bar */
    .reading-progress-track {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        z-index: 9999;
        background: transparent;
        pointer-events: none;
    }
    .reading-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--teal, #1bb2a6) 0%, var(--gold, #f4b41a) 100%);
        box-shadow: 0 0 10px rgba(27, 178, 166, 0.6);
        transition: width 0.08s linear;
    }

    .page-template-wrapper {
        background-color: var(--bg-light, #f8fafc);
        min-height: 85vh;
        padding-bottom: 5rem;
        position: relative;
    }

    /* --------------------------------------------------------
       Page Header Banner
       -------------------------------------------------------- */
    .page-banner-header {
        background: radial-gradient(circle at 50% 0%, rgba(27, 178, 166, 0.22) 0%, transparent 60%),
                    linear-gradient(180deg, var(--secondary-color, #0f172a) 0%, #162032 100%);
        color: white;
        padding: 4.25rem 1.5rem 5rem;
        text-align: center;
        position: relative;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
    }
    .page-banner-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 15%, rgba(244, 180, 26, 0.12) 0%, transparent 65%);
        pointer-events: none;
    }
    .page-banner-container {
        max-width: 920px;
        margin: 0 auto;
        position: relative;
        z-index: 3;
        width: 100%;
        box-sizing: border-box;
    }
    .page-header-topline {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .page-breadcrumb {
        font-size: 0.84rem;
        color: rgba(255, 255, 255, 0.7);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .page-breadcrumb a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        transition: color 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .page-breadcrumb a:hover {
        color: var(--gold, #f4b41a);
    }
    .page-breadcrumb .sep {
        font-size: 0.65rem;
        opacity: 0.45;
    }
    .page-breadcrumb .current {
        color: var(--gold, #f4b41a);
        font-weight: 500;
    }
    .page-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        background: rgba(244, 180, 26, 0.16);
        color: var(--gold, #f4b41a);
        border: 1px solid rgba(244, 180, 26, 0.4);
        padding: 0.25rem 0.85rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    .page-badge-pill .badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--gold, #f4b41a);
        box-shadow: 0 0 6px var(--gold, #f4b41a);
    }

    .page-main-title {
        font-family: 'Playfair Display', Georgia, 'Times New Roman', serif;
        font-size: clamp(2.2rem, 4vw, 3rem);
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.015em;
        margin: 0 0 1.25rem;
        color: #ffffff;
        text-shadow: 0 2px 14px rgba(0, 0, 0, 0.35);
    }

    /* Meta & Tools Glass Bar */
    .page-meta-bar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 1.15rem;
        font-size: 0.84rem;
        color: rgba(255, 255, 255, 0.85);
        flex-wrap: wrap;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 0.55rem 1.35rem;
        border-radius: 60px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        max-width: 100%;
        box-sizing: border-box;
    }
    .page-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .meta-icon {
        color: var(--gold, #f4b41a);
        font-size: 0.88rem;
    }
    .page-meta-dot {
        color: rgba(255, 255, 255, 0.35);
        font-size: 0.75rem;
    }

    .page-tools-group {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-left: 1px solid rgba(255, 255, 255, 0.18);
        padding-left: 0.85rem;
    }
    .font-sizer-widget {
        display: inline-flex;
        align-items: center;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 30px;
        padding: 0.15rem 0.35rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        gap: 0.15rem;
    }
    .sizer-label {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.55);
        padding: 0 0.3rem 0 0.2rem;
    }
    .btn-text-size {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .btn-text-size:hover {
        color: white;
        background: rgba(255, 255, 255, 0.15);
    }
    .btn-text-size.active {
        color: var(--gold, #f4b41a);
        background: rgba(244, 180, 26, 0.2);
    }
    .btn-print-page {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.85rem;
        cursor: pointer;
        padding: 0.3rem 0.5rem;
        border-radius: 50%;
        transition: all 0.2s;
    }
    .btn-print-page:hover {
        color: var(--gold, #f4b41a);
        background: rgba(255, 255, 255, 0.12);
    }

    /* --------------------------------------------------------
       Floating Quick Action Dock
       -------------------------------------------------------- */
    .page-flying-dock {
        position: fixed;
        left: max(1.25rem, calc(50% - 470px - 85px));
        top: 50%;
        transform: translateY(-50%);
        z-index: 900;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .dock-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.55rem;
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        padding: 0.85rem 0.5rem;
        border-radius: 40px;
        box-shadow: 0 12px 36px rgba(15, 23, 42, 0.12), 0 2px 8px rgba(15, 23, 42, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.85);
    }
    .dock-brand-label {
        font-size: 0.55rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        color: #94a3b8;
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        margin-bottom: 0.25rem;
        padding: 0.2rem 0;
    }
    .dock-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: white;
        font-size: 1.05rem;
        border: none;
        cursor: pointer;
        position: relative;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .dock-btn:hover {
        transform: scale(1.15);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
    }
    .share-wa { background: #25d366; }
    .share-fb { background: #1877f2; }
    .share-tw { background: #0f1419; }
    .share-tg { background: #229ed9; }
    .share-copy { background: #475569; }

    .dock-divider {
        width: 22px;
        height: 1px;
        background: #e2e8f0;
        margin: 0.2rem 0;
    }
    .dock-btn-top {
        background: #f1f5f9;
        color: #64748b;
        box-shadow: none;
        border: 1px solid #e2e8f0;
    }
    .dock-btn-top:hover {
        background: var(--teal, #1bb2a6);
        color: white;
        border-color: var(--teal, #1bb2a6);
        box-shadow: 0 4px 14px rgba(27, 178, 166, 0.35);
    }
    .dock-tooltip {
        position: absolute;
        left: calc(100% + 12px);
        background: #0f172a;
        color: white;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 500;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transform: translateX(-6px);
        transition: all 0.2s ease;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    .dock-tooltip::before {
        content: '';
        position: absolute;
        right: 100%;
        top: 50%;
        transform: translateY(-50%);
        border: 4px solid transparent;
        border-right-color: #0f172a;
    }
    .dock-btn:hover .dock-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }

    /* --------------------------------------------------------
       Page Content Wrapper & Card
       -------------------------------------------------------- */
    .page-content-wrapper {
        max-width: 940px;
        margin: -2.5rem auto 0;
        padding: 0 1.25rem;
        position: relative;
        z-index: 5;
    }
    .page-card {
        background: white;
        border-radius: 24px;
        padding: 3.5rem 4rem;
        box-shadow: 0 14px 45px -10px rgba(15, 23, 42, 0.06);
        border: 1px solid #edf2f7;
    }

    /* Featured Image */
    .page-featured-frame {
        margin: 0 0 2.5rem;
        border-radius: 20px;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 12px 36px -10px rgba(15, 23, 42, 0.08);
    }
    .page-featured-canvas {
        overflow: hidden;
        max-height: 480px;
        background: #0f172a;
    }
    .page-featured-img {
        width: 100%;
        height: auto;
        max-height: 480px;
        object-fit: cover;
        display: block;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .page-featured-frame:hover .page-featured-img {
        transform: scale(1.02);
    }
    .page-featured-caption {
        padding: 0.85rem 1.35rem;
        font-size: 0.82rem;
        color: #64748b;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-style: italic;
    }
    .page-featured-caption i {
        color: var(--teal, #1bb2a6);
    }

    /* --------------------------------------------------------
       Rich Content Typography & List Polish
       -------------------------------------------------------- */
    .page-rich-content {
        font-size: 1.1rem;
        line-height: 1.85;
        color: #334155;
        word-break: break-word;
        transition: font-size 0.2s ease;
    }
    .page-rich-content.font-sm {
        font-size: 0.98rem;
        line-height: 1.75;
    }
    .page-rich-content.font-lg {
        font-size: 1.22rem;
        line-height: 1.95;
    }

    .page-rich-content > p:first-of-type {
        font-size: 1.18rem;
        line-height: 1.85;
        color: #1e293b;
        font-weight: 450;
        margin-bottom: 1.5rem;
    }
    .page-rich-content p {
        margin-bottom: 1.35rem;
    }

    .page-rich-content h2 {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: #0f172a;
        margin: 2.25rem 0 1rem;
        letter-spacing: -0.015em;
        line-height: 1.3;
        position: relative;
        padding-left: 0.85rem;
        border-left: 4px solid var(--teal, #1bb2a6);
    }
    .page-rich-content h3 {
        font-size: 1.38rem;
        font-weight: 700;
        color: #0f172a;
        margin: 1.75rem 0 0.75rem;
        line-height: 1.35;
    }
    .page-rich-content h4 {
        font-size: 1.15rem;
        font-weight: 600;
        color: #1e293b;
        margin: 1.35rem 0 0.5rem;
    }
    .page-rich-content strong, .page-rich-content b {
        color: #0f172a;
        font-weight: 700;
    }

    /* Structured Section Headings in Paragraphs (e.g. A. Program Intra Kurikuler) */
    .page-rich-content p > strong:only-child,
    .page-rich-content p:has(> strong:first-child:last-child) {
        font-size: 1.18rem;
        color: #0f172a;
        margin-top: 1.75rem;
        margin-bottom: 0.5rem;
        display: block;
        padding-bottom: 0.25rem;
        border-bottom: 1.5px solid #f1f5f9;
    }

    /* Enhanced Numbered Lists */
    .page-rich-content ol {
        counter-reset: custom-page-counter;
        list-style: none;
        padding-left: 0;
        margin: 1.25rem 0 1.75rem;
    }
    .page-rich-content ol > li {
        counter-increment: custom-page-counter;
        position: relative;
        padding-left: 2.75rem;
        margin-bottom: 0.85rem;
        line-height: 1.75;
    }
    .page-rich-content ol > li::before {
        content: counter(custom-page-counter);
        position: absolute;
        left: 0;
        top: 0.15rem;
        width: 26px;
        height: 26px;
        background: linear-gradient(135deg, rgba(27, 178, 166, 0.12) 0%, rgba(27, 178, 166, 0.22) 100%);
        color: var(--teal, #1bb2a6);
        font-weight: 700;
        font-size: 0.82rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(27, 178, 166, 0.3);
        box-shadow: 0 2px 6px rgba(27, 178, 166, 0.1);
    }

    /* Nested Unordered Lists */
    .page-rich-content ol ul,
    .page-rich-content ul {
        list-style: none;
        padding-left: 0.75rem;
        margin: 0.75rem 0 0.85rem;
    }
    .page-rich-content ol ul > li,
    .page-rich-content ul > li {
        position: relative;
        padding-left: 1.65rem;
        margin-bottom: 0.45rem;
        line-height: 1.65;
        color: #475569;
    }
    .page-rich-content ol ul > li::before,
    .page-rich-content ul > li::before {
        content: '';
        position: absolute;
        left: 0.35rem;
        top: 0.65rem;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--gold, #f4b41a);
        box-shadow: 0 0 4px rgba(244, 180, 26, 0.5);
    }

    /* Blockquotes */
    .page-rich-content blockquote {
        position: relative;
        border-left: 4px solid var(--gold, #f4b41a);
        background: linear-gradient(135deg, rgba(244, 180, 26, 0.05) 0%, rgba(27, 178, 166, 0.04) 100%);
        padding: 1.35rem 1.85rem;
        margin: 1.75rem 0;
        border-radius: 0 16px 16px 0;
        font-style: italic;
        color: #334155;
        font-size: 1.08rem;
        line-height: 1.75;
    }
    .page-rich-content blockquote::before {
        content: '\201C';
        font-family: Georgia, serif;
        font-size: 3rem;
        line-height: 1;
        position: absolute;
        top: 0.25rem;
        right: 1.25rem;
        color: rgba(244, 180, 26, 0.35);
        pointer-events: none;
    }

    /* Images inside Content */
    .page-rich-content img {
        max-width: 100%;
        height: auto;
        border-radius: 14px;
        margin: 1.75rem auto;
        display: block;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid #edf2f7;
    }

    /* Tables */
    .page-rich-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.85rem 0;
        font-size: 0.95rem;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .page-rich-content th, .page-rich-content td {
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        text-align: left;
    }
    .page-rich-content th {
        background: #f8fafc;
        font-weight: 700;
        color: #0f172a;
    }
    .page-rich-content tr:nth-child(even) td {
        background: #fafbfc;
    }


    /* --------------------------------------------------------
       Other Pages Grid
       -------------------------------------------------------- */
    .other-pages-section {
        margin: 2.25rem 0 1.5rem;
        padding: 1.5rem;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 18px;
    }
    .other-pages-heading {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .other-pages-heading i {
        color: var(--teal, #1bb2a6);
    }
    .other-pages-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    .other-page-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem 1.15rem;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        transition: all 0.25s ease;
    }
    .other-page-card:hover {
        border-color: var(--teal, #1bb2a6);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(27, 178, 166, 0.12);
    }
    .op-badge {
        font-size: 0.72rem;
        color: var(--teal, #1bb2a6);
        font-weight: 600;
        margin-bottom: 0.35rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .op-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        flex: 1;
    }
    .op-link {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--teal, #1bb2a6);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: transform 0.2s;
    }
    .other-page-card:hover .op-link {
        transform: translateX(3px);
    }

    /* --------------------------------------------------------
       Card Footer Button
       -------------------------------------------------------- */
    .page-card-footer {
        margin-top: 2.75rem;
        padding-top: 1.75rem;
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
        color: var(--teal, #1bb2a6);
        border: 2px solid var(--teal, #1bb2a6);
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .btn-page-home:hover {
        background: var(--teal, #1bb2a6);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(27, 178, 166, 0.22);
    }

    /* --------------------------------------------------------
       Toast Feedback
       -------------------------------------------------------- */
    .page-copied-toast {
        position: fixed;
        bottom: 2.25rem;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        color: white;
        padding: 0.85rem 1.65rem;
        border-radius: 50px;
        box-shadow: 0 14px 40px rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        gap: 0.85rem;
        z-index: 10000;
        opacity: 0;
        pointer-events: none;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .page-copied-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
    .page-copied-toast .toast-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #10b981;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .page-copied-toast .toast-body {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }
    .page-copied-toast .toast-body strong {
        font-size: 0.88rem;
        color: #ffffff;
    }
    .page-copied-toast .toast-body span {
        font-size: 0.76rem;
        color: rgba(255, 255, 255, 0.7);
    }

    /* --------------------------------------------------------
       Responsive Media Queries
       -------------------------------------------------------- */
    @media (max-width: 1100px) {
        .page-main-title { font-size: 2.35rem; }
        .page-card { padding: 2.75rem 2.5rem; }

        /* Floating Dock Bottom on Tablets & Mobile */
        .page-flying-dock {
            left: 50%;
            top: auto;
            bottom: 1.25rem;
            transform: translateX(-50%);
            flex-direction: row;
        }
        .dock-inner {
            flex-direction: row;
            padding: 0.45rem 0.85rem;
            border-radius: 50px;
            background: rgba(15, 23, 42, 0.94);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            gap: 0.65rem;
        }
        .dock-brand-label { display: none; }
        .dock-divider { width: 1px; height: 22px; margin: 0 0.15rem; background: rgba(255, 255, 255, 0.18); }
        .dock-btn { width: 38px; height: 38px; font-size: 0.95rem; }
        .dock-btn-top {
            background: rgba(255, 255, 255, 0.12);
            color: white;
            border-color: rgba(255, 255, 255, 0.18);
        }
        .dock-tooltip { display: none !important; }
        #backToTop {
            bottom: 4.8rem !important;
            right: 1.25rem !important;
        }
    }

    @media (max-width: 768px) {
        .page-banner-header { padding: 3.25rem 1.25rem 4rem; }
        .page-main-title { font-size: 1.75rem; }
        .page-meta-bar { padding: 0.5rem 0.85rem; border-radius: 20px; gap: 0.75rem; }
        .page-tools-group { border-left: none; padding-left: 0; width: 100%; justify-content: center; margin-top: 0.35rem; }
        .page-content-wrapper { margin-top: -2rem; padding: 0 1rem; }
        .page-card { padding: 2rem 1.35rem; border-radius: 18px; }
        .page-rich-content { font-size: 1.02rem; line-height: 1.8; }
        .page-rich-content ol > li { padding-left: 2.35rem; }
        .other-pages-grid { grid-template-columns: 1fr; }

    }

    /* Print Optimization */
    @media print {
        .reading-progress-track, .page-flying-dock,
        .other-pages-section, .page-card-footer, .page-tools-group,
        #backToTop, .site-header, .site-footer {
            display: none !important;
        }
        .page-banner-header {
            background: white !important;
            color: black !important;
            padding: 1rem 0 !important;
        }
        .page-main-title { color: black !important; text-shadow: none !important; }
        .page-meta-bar { color: #333 !important; background: none !important; border: none !important; }
        .page-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
    }
</style>

<script>
function copyPageUrl() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        showPageToast();
    }).catch(function() {
        const temp = document.createElement('input');
        temp.value = window.location.href;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        showPageToast();
    });
}

function showPageToast() {
    const toast = document.getElementById('pageCopiedToast');
    if (toast) {
        toast.classList.add('show');
        setTimeout(function() {
            toast.classList.remove('show');
        }, 3200);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Reading Progress Bar Tracker
    const progressBar = document.getElementById('readingProgressBar');
    const pageCard = document.querySelector('.page-card');

    function updatePageReadingProgress() {
        if (!progressBar || !pageCard) return;
        
        const rect = pageCard.getBoundingClientRect();
        const winHeight = window.innerHeight;
        const totalHeight = rect.height;
        const currentScrolled = winHeight - rect.top;

        let percent = (currentScrolled / (totalHeight + winHeight * 0.4)) * 100;
        percent = Math.max(0, Math.min(100, percent));
        progressBar.style.width = percent + '%';
    }

    window.addEventListener('scroll', updatePageReadingProgress, { passive: true });
    window.addEventListener('resize', updatePageReadingProgress);
    updatePageReadingProgress();

    // 2. Font Size Adjustment
    const content = document.getElementById('pageRichContent');
    const btnDecrease = document.getElementById('btnPageDecreaseFont');
    const btnReset = document.getElementById('btnPageResetFont');
    const btnIncrease = document.getElementById('btnPageIncreaseFont');
    const fontButtons = [btnDecrease, btnReset, btnIncrease];

    function setPageFontSize(mode) {
        if (!content) return;
        content.classList.remove('font-sm', 'font-lg');
        fontButtons.forEach(b => b?.classList.remove('active'));

        if (mode === 'sm') {
            content.classList.add('font-sm');
            btnDecrease?.classList.add('active');
        } else if (mode === 'lg') {
            content.classList.add('font-lg');
            btnIncrease?.classList.add('active');
        } else {
            btnReset?.classList.add('active');
        }
        localStorage.setItem('lumiere_page_font_mode', mode);
    }

    btnDecrease?.addEventListener('click', () => setPageFontSize('sm'));
    btnReset?.addEventListener('click', () => setPageFontSize('normal'));
    btnIncrease?.addEventListener('click', () => setPageFontSize('lg'));

    const savedFont = localStorage.getItem('lumiere_page_font_mode');
    if (savedFont) setPageFontSize(savedFont);
});
</script>
