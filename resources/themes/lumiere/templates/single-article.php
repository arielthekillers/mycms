<?php
/**
 * Single Article Template - Theme Lumiere
 * High-end editorial reading layout with interactive comments, social sharing, and related posts.
 */

$app = \App\Core\Application::getInstance();

// 1. Author Resolution
$authorName = $article['author_name'] ?? 'Redaksi';
if (empty($article['author_name']) && !empty($article['author_id'])) {
    try {
        $u = $app->db->selectOne("SELECT name FROM users WHERE id = ?", [$article['author_id']]);
        if ($u) $authorName = $u['name'];
    } catch (\Exception $e) {}
}

// 2. Featured Image
$featuredImg = null;
if (!empty($article['featured_media_id'])) {
    try {
        $media = $app->db->selectOne("SELECT disk_path FROM media WHERE id = ?", [$article['featured_media_id']]);
        if ($media) {
            $featuredImg = '/uploads/' . $media['disk_path'];
        }
    } catch (\Exception $e) {}
}

// 3. Category & Tags
$categories = $article['categories'] ?? [];
$primaryCategory = !empty($categories) ? $categories[0] : null;
$tags = $article['tags'] ?? [];

// 4. Reading Time
$wordCount = str_word_count(strip_tags($article['content'] ?? ''));
$readTime = max(1, (int) ceil($wordCount / 200));

// 5. Approved Comments
$comments = [];
try {
    $comments = $app->db->select(
        "SELECT * FROM comments WHERE article_id = :id AND status = 'approved' ORDER BY created_at ASC",
        ['id' => $article['id']]
    );
} catch (\Exception $e) {}

// 6. Related Articles
$relatedArticles = [];
try {
    $relatedArticles = $app->db->select(
        "SELECT id, title, slug, published_at FROM articles WHERE status = 'published' AND id != :id ORDER BY published_at DESC LIMIT 3",
        ['id' => $article['id']]
    );
} catch (\Exception $e) {}

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'];
$publishedDate = !empty($article['published_at']) ? date('d F Y', strtotime($article['published_at'])) : date('d F Y', strtotime($article['created_at'] ?? 'now'));
?>

<div class="article-page-wrapper">
    <!-- Header Hero / Meta Section with Featured Image Background -->
    <header class="single-article-header <?= $featuredImg ? 'has-featured-bg' : '' ?>">
        <?php if ($featuredImg): ?>
            <div class="article-header-bg" aria-hidden="true">
                <img src="<?= htmlspecialchars($featuredImg) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-header-bg-img">
                <div class="article-header-overlay"></div>
            </div>
        <?php endif; ?>

        <div class="single-article-header-container">
            <nav class="article-breadcrumb" aria-label="Breadcrumb">
                <a href="/"><i class="fas fa-home"></i> Beranda</a>
                <span>&rsaquo;</span>
                <?php if ($primaryCategory): ?>
                    <a href="/kategori/<?= htmlspecialchars($primaryCategory['slug']) ?>"><?= htmlspecialchars($primaryCategory['name']) ?></a>
                    <span>&rsaquo;</span>
                <?php endif; ?>
                <span class="current">Artikel</span>
            </nav>

            <?php if ($primaryCategory): ?>
                <a href="/kategori/<?= htmlspecialchars($primaryCategory['slug']) ?>" class="category-pill">
                    <?= htmlspecialchars($primaryCategory['name']) ?>
                </a>
            <?php endif; ?>

            <h1 class="single-article-title">
                <?= htmlspecialchars($article['title']) ?>
            </h1>

            <div class="article-meta-bar">
                <div class="meta-item author-meta">
                    <div class="author-avatar-badge">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <span class="meta-label">Ditulis oleh</span>
                        <strong class="author-name"><?= htmlspecialchars($authorName) ?></strong>
                    </div>
                </div>
                <div class="meta-divider"></div>
                <div class="meta-item">
                    <i class="far fa-calendar-alt meta-icon"></i>
                    <span><?= $publishedDate ?></span>
                </div>
                <div class="meta-divider"></div>
                <div class="meta-item">
                    <i class="far fa-clock meta-icon"></i>
                    <span><?= $readTime ?> Menit Baca</span>
                </div>
                <div class="meta-divider"></div>
                <div class="meta-item">
                    <i class="far fa-comments meta-icon"></i>
                    <span><?= count($comments) ?> Komentar</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="single-article-container">

        <!-- Content Body -->
        <div class="article-main-layout">
            <!-- Share Floating Bar (Desktop) -->
            <aside class="article-share-aside">
                <div class="share-sticky-box">
                    <span class="share-label">BAGIKAN</span>
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $currentUrl) ?>" target="_blank" rel="noopener" class="share-btn share-wa" aria-label="Share ke WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="share-btn share-fb" aria-label="Share ke Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['title']) ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="share-btn share-tw" aria-label="Share ke X / Twitter">
                        <i class="fab fa-x-twitter"></i>
                    </a>
                    <button type="button" class="share-btn share-copy" id="btnCopyLink" title="Salin Tautan" aria-label="Salin Tautan">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </aside>

            <!-- Article Body -->
            <article class="article-body-wrapper">
                <div class="article-content">
                    <?php
                    $renderedArticleContent = $article['content'] ?? '<p>Konten artikel kosong.</p>';
                    if (!empty($app->events)) {
                        $renderedArticleContent = $app->events->dispatch('article.before_render', $renderedArticleContent);
                        $renderedArticleContent = $app->events->dispatch('content.render', $renderedArticleContent);
                    }
                    ?>
                    <?= $renderedArticleContent ?>
                </div>

                <!-- Tags Section -->
                <?php if (!empty($tags)): ?>
                    <div class="article-tags-wrapper">
                        <span class="tags-title"><i class="fas fa-tags"></i> Topik:</span>
                        <div class="tags-list">
                            <?php foreach ($tags as $tag): ?>
                                <span class="tag-pill">#<?= htmlspecialchars($tag['name'] ?? $tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Mobile Share Section -->
                <div class="mobile-share-bar">
                    <span class="share-title">Bagikan tulisan ini:</span>
                    <div class="mobile-share-buttons">
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $currentUrl) ?>" target="_blank" rel="noopener" class="share-btn share-wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="share-btn share-fb"><i class="fab fa-facebook-f"></i> Facebook</a>
                        <button type="button" class="share-btn share-copy" onclick="copyArticleUrl()"><i class="fas fa-link"></i> Salin Link</button>
                    </div>
                </div>

                <!-- Author Bio Box -->
                <div class="author-bio-card">
                    <div class="author-bio-avatar">
                        <i class="fas fa-feather-alt"></i>
                    </div>
                    <div class="author-bio-info">
                        <h4><?= htmlspecialchars($authorName) ?></h4>
                        <p class="author-role">Penulis & Redaksi Pondok Pesantren Darussalam</p>
                        <p class="author-desc">Menyajikan informasi edukatif, warta kegiatan santri, dan artikel keislaman terpercaya untuk masyarakat.</p>
                    </div>
                </div>

                <!-- Comments Section -->
                <section class="comments-section" id="comments">
                    <div class="section-header-row">
                        <h3 class="comments-heading">
                            <i class="far fa-comments"></i> Komentar Pembaca (<?= count($comments) ?>)
                        </h3>
                    </div>

                    <!-- Comments List -->
                    <div class="comments-list">
                        <?php if (empty($comments)): ?>
                            <div class="no-comments-box">
                                <i class="far fa-comment-dots"></i>
                                <p>Belum ada komentar untuk artikel ini. Jadilah yang pertama berkomentar!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $comm): ?>
                                <div class="comment-item">
                                    <div class="comment-avatar">
                                        <?= strtoupper(mb_substr($comm['author_name'], 0, 1)) ?>
                                    </div>
                                    <div class="comment-content-box">
                                        <div class="comment-header">
                                            <strong class="comment-author"><?= htmlspecialchars($comm['author_name']) ?></strong>
                                            <span class="comment-date"><?= date('d M Y, H:i', strtotime($comm['created_at'])) ?></span>
                                        </div>
                                        <div class="comment-text">
                                            <?= nl2br(htmlspecialchars($comm['content'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Add Comment Form -->
                    <div class="comment-form-container">
                        <h4 class="form-title"><i class="fas fa-pen-nib"></i> Tinggalkan Komentar</h4>
                        <p class="form-subtitle">Alamat email Anda tidak akan dipublikasikan. Komentar akan dimoderasi sebelum tampil.</p>

                        <div id="commentAlert" class="comment-alert" style="display: none;"></div>

                        <form id="publicCommentForm" class="comment-form">
                            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="author_name">Nama Lengkap *</label>
                                    <input type="text" id="author_name" name="author_name" required placeholder="Nama Anda">
                                </div>
                                <div class="form-group">
                                    <label for="author_email">Alamat Email *</label>
                                    <input type="email" id="author_email" name="author_email" required placeholder="email@contoh.com">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comment_content">Komentar Anda *</label>
                                <textarea id="comment_content" name="content" rows="4" required placeholder="Tuliskan pandangan atau tanggapan Anda..."></textarea>
                            </div>
                            <button type="submit" id="btnSubmitComment" class="btn-submit-comment">
                                <i class="fas fa-paper-plane"></i> Kirim Komentar
                            </button>
                        </form>
                    </div>
                </section>

                <!-- Related Articles Section -->
                <?php if (!empty($relatedArticles)): ?>
                    <section class="related-articles-section">
                        <h3 class="related-heading"><i class="fas fa-book-reader"></i> Baca Warta Lainnya</h3>
                        <div class="related-grid">
                            <?php foreach ($relatedArticles as $rel): ?>
                                <a href="/artikel/<?= htmlspecialchars($rel['slug']) ?>" class="related-card">
                                    <span class="rel-date"><i class="far fa-calendar-alt"></i> <?= !empty($rel['published_at']) ? date('d M Y', strtotime($rel['published_at'])) : '' ?></span>
                                    <h4 class="rel-title"><?= htmlspecialchars($rel['title']) ?></h4>
                                    <span class="rel-link">Baca Selengkapnya &rarr;</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <div class="back-home-bottom">
                    <a href="/" class="btn-back-home">&larr; Kembali ke Beranda</a>
                </div>

            </article>
        </div>
    </div>
</div>

<!-- Toast Feedback for Copy Link -->
<div id="linkCopiedToast" class="link-copied-toast">
    <i class="fas fa-check-circle"></i> Tautan artikel berhasil disalin ke clipboard!
</div>

<style>
    .article-page-wrapper {
        background-color: var(--bg-light, #f8fafc);
        min-height: 85vh;
        padding-bottom: 5rem;
    }
    
    /* Header & Meta */
    .single-article-header {
        background: linear-gradient(135deg, var(--secondary-color, #0f172a) 0%, #1e293b 100%);
        color: white;
        padding: 5.5rem 5% 5rem;
        position: relative;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        min-height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .single-article-header:not(.has-featured-bg)::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 10%, rgba(27, 178, 166, 0.25) 0%, transparent 75%);
        pointer-events: none;
    }
    .article-header-bg {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 1100px;
        height: 100%;
        overflow: hidden;
        z-index: 1;
        -webkit-mask-image: linear-gradient(to right, transparent 0%, black 18%, black 82%, transparent 100%);
        mask-image: linear-gradient(to right, transparent 0%, black 18%, black 82%, transparent 100%);
    }
    .article-header-bg-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        transform: scale(1.02);
        filter: brightness(0.88);
    }
    .article-header-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        background: 
            /* Gradasi vertikal lembut untuk kontras teks judul */
            linear-gradient(180deg, 
                rgba(15, 23, 42, 0.65) 0%, 
                rgba(15, 23, 42, 0.25) 45%, 
                rgba(15, 23, 42, 0.92) 100%
            ),
            /* Gradasi horizontal di sisi gambar */
            linear-gradient(90deg, 
                rgba(15, 23, 42, 0.75) 0%, 
                transparent 25%, 
                transparent 75%, 
                rgba(15, 23, 42, 0.75) 100%
            );
    }
    .single-article-header-container {
        max-width: 920px;
        margin: 0 auto;
        position: relative;
        z-index: 3;
        width: 100%;
    }
    .article-breadcrumb {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }
    .article-breadcrumb a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        transition: color 0.2s;
    }
    .article-breadcrumb a:hover {
        color: var(--gold);
    }
    .article-breadcrumb .current {
        color: var(--gold);
        font-weight: 500;
    }
    .category-pill {
        display: inline-block;
        background: rgba(27, 178, 166, 0.25);
        color: var(--gold);
        border: 1px solid rgba(244, 180, 26, 0.4);
        padding: 0.35rem 1.15rem;
        border-radius: 50px;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .category-pill:hover {
        background: var(--gold);
        color: #0f172a;
        transform: translateY(-2px);
    }
    .single-article-title {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.025em;
        margin-bottom: 2rem;
        color: #ffffff;
        text-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .article-meta-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.25rem;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.85);
        flex-wrap: wrap;
    }
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .author-meta {
        gap: 0.75rem;
        text-align: left;
    }
    .author-avatar-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        color: var(--gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }
    .meta-label {
        display: block;
        font-size: 0.72rem;
        text-transform: uppercase;
        opacity: 0.7;
        letter-spacing: 0.04em;
    }
    .author-name {
        color: white;
        font-weight: 600;
    }
    .meta-icon {
        color: var(--gold);
    }
    .meta-divider {
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.4);
    }

    /* Container & Layout */
    .single-article-container {
        max-width: 1100px;
        margin: 2.75rem auto 0;
        padding: 0 5%;
        position: relative;
        z-index: 5;
    }
    .article-main-layout {
        display: grid;
        grid-template-columns: 60px minmax(0, 1fr);
        gap: 3.5rem;
        align-items: start;
    }
    .article-share-aside {
        position: relative;
    }
    .share-sticky-box {
        position: sticky;
        top: 6rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.85rem;
    }
    .share-label {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        margin-bottom: 0.5rem;
    }
    .share-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: white;
        font-size: 1.1rem;
        border: none;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .share-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(0,0,0,0.18);
    }
    .share-wa { background: #25d366; }
    .share-fb { background: #1877f2; }
    .share-tw { background: #0f1419; }
    .share-copy { background: #64748b; }

    /* Article Body Typography */
    .article-body-wrapper {
        min-width: 0;
        max-width: 100%;
        background: white;
        border-radius: 24px;
        padding: 3.5rem 4rem;
        box-shadow: 0 10px 35px rgba(0,0,0,0.05);
        border: 1px solid #edf2f7;
    }
    .article-content {
        font-size: 1.15rem;
        line-height: 1.9;
        color: #334155;
    }
    .article-content p {
        margin-bottom: 1.75rem;
    }
    .article-content h2 {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-main);
        margin: 2.75rem 0 1.25rem;
        letter-spacing: -0.02em;
        line-height: 1.3;
    }
    .article-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 2.25rem 0 1rem;
    }
    .article-content blockquote {
        border-left: 4px solid var(--gold);
        background: #fdfbf7;
        padding: 1.5rem 2rem;
        margin: 2.25rem 0;
        border-radius: 0 16px 16px 0;
        font-style: italic;
        color: #475569;
        font-size: 1.15rem;
    }
    .article-content ul, .article-content ol {
        margin: 1.5rem 0 2rem 2rem;
    }
    .article-content li {
        margin-bottom: 0.6rem;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 14px;
        margin: 2rem auto;
        display: block;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .article-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
    }
    .article-content th, .article-content td {
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        text-align: left;
    }
    .article-content th {
        background: #f8fafc;
        font-weight: 700;
    }

    /* Tags */
    .article-tags-wrapper {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin: 3rem 0;
        padding-top: 2rem;
        border-top: 1px solid #f1f5f9;
        flex-wrap: wrap;
    }
    .tags-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-main);
    }
    .tag-pill {
        background: #f1f5f9;
        color: var(--teal);
        padding: 0.35rem 0.9rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Mobile Share Bar */
    .mobile-share-bar {
        display: none;
        margin: 2.5rem 0;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 16px;
        text-align: center;
    }
    .mobile-share-bar .share-title {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        color: var(--text-main);
    }
    .mobile-share-buttons {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .mobile-share-buttons .share-btn {
        width: auto;
        border-radius: 50px;
        padding: 0.5rem 1.25rem;
        font-size: 0.88rem;
        gap: 0.5rem;
    }

    /* Author Bio Card */
    .author-bio-card {
        display: flex;
        gap: 1.75rem;
        align-items: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2rem 2.5rem;
        margin: 3.5rem 0;
    }
    .author-bio-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: var(--teal);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(27, 178, 166, 0.25);
    }
    .author-bio-info h4 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .author-role {
        font-size: 0.85rem;
        color: var(--teal);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .author-desc {
        font-size: 0.92rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin: 0;
    }

    /* Comments Section */
    .comments-section {
        margin: 4rem 0 2rem;
        padding-top: 3rem;
        border-top: 2px solid #f1f5f9;
    }
    .comments-heading {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 2rem;
    }
    .comments-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    .no-comments-box {
        text-align: center;
        padding: 3rem 1.5rem;
        background: #f8fafc;
        border-radius: 16px;
        color: var(--text-muted);
    }
    .no-comments-box i {
        font-size: 2.5rem;
        color: #cbd5e1;
        margin-bottom: 0.75rem;
    }
    .comment-item {
        display: flex;
        gap: 1.25rem;
        padding: 1.5rem;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }
    .comment-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--teal);
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .comment-content-box {
        flex: 1;
    }
    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .comment-author {
        font-size: 0.95rem;
        color: var(--text-main);
    }
    .comment-date {
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    .comment-text {
        font-size: 0.95rem;
        color: #475569;
        line-height: 1.6;
    }

    /* Comment Form */
    .comment-form-container {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2.5rem;
    }
    .form-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: var(--text-main);
    }
    .form-subtitle {
        font-size: 0.88rem;
        color: var(--text-muted);
        margin-bottom: 1.75rem;
    }
    .comment-alert {
        padding: 1rem 1.25rem;
        border-radius: 12px;
        font-size: 0.92rem;
        margin-bottom: 1.5rem;
    }
    .comment-alert.success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    .comment-alert.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-group label {
        display: block;
        font-size: 0.88rem;
        font-weight: 600;
        margin-bottom: 0.4rem;
        color: var(--text-main);
    }
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-family: inherit;
        font-size: 0.92rem;
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-group input:focus, .form-group textarea:focus {
        border-color: var(--teal);
        outline: none;
        box-shadow: 0 0 0 3px rgba(27, 178, 166, 0.15);
    }
    .btn-submit-comment {
        background: var(--teal);
        color: white;
        border: none;
        padding: 0.85rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.92rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.25s ease;
    }
    .btn-submit-comment:hover {
        background: var(--teal-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(27, 178, 166, 0.25);
    }

    /* Related Section */
    .related-articles-section {
        margin-top: 4rem;
        padding-top: 3rem;
        border-top: 1px solid #edf2f7;
    }
    .related-heading {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 1.75rem;
        color: var(--text-main);
    }
    .related-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
    .related-card {
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .related-card:hover {
        background: white;
        border-color: var(--teal);
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }
    .rel-date {
        font-size: 0.8rem;
        color: var(--teal);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .rel-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.4;
        margin-bottom: 1rem;
        flex: 1;
    }
    .rel-link {
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--teal);
    }

    .back-home-bottom {
        margin-top: 3.5rem;
        text-align: center;
    }
    .btn-back-home {
        display: inline-block;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        border: 2px solid var(--teal);
        color: var(--teal);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.92rem;
        transition: all 0.25s ease;
    }
    .btn-back-home:hover {
        background: var(--teal);
        color: white;
    }

    /* Toast Notification */
    .link-copied-toast {
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #0f172a;
        color: white;
        padding: 0.85rem 1.75rem;
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.92rem;
        font-weight: 500;
        z-index: 1000;
        opacity: 0;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .link-copied-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    .link-copied-toast i {
        color: #10b981;
        font-size: 1.1rem;
    }

    /* Responsiveness */
    @media (max-width: 992px) {
        .single-article-title { font-size: 2.4rem; }
        .article-main-layout { grid-template-columns: 1fr; gap: 0; }
        .article-share-aside { display: none; }
        .mobile-share-bar { display: block; }
        .related-grid { grid-template-columns: 1fr; }
        .article-body-wrapper { padding: 2.5rem 2rem; }
    }
    @media (max-width: 640px) {
        .single-article-title { font-size: 1.85rem; }
        .single-article-header { padding: 3.5rem 1.25rem 3.5rem; min-height: 320px; }
        .single-article-container { margin-top: 1.75rem; padding: 0 4%; }
        .article-body-wrapper { padding: 2rem 1.25rem; }
        .form-grid { grid-template-columns: 1fr; }
        .author-bio-card { flex-direction: column; text-align: center; }
    }
</style>

<script>
function copyArticleUrl() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        const toast = document.getElementById('linkCopiedToast');
        if (toast) {
            toast.classList.add('show');
            setTimeout(function() {
                toast.classList.remove('show');
            }, 3000);
        }
    }).catch(function() {
        alert('Tautan disalin: ' + window.location.href);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const btnCopy = document.getElementById('btnCopyLink');
    if (btnCopy) {
        btnCopy.addEventListener('click', copyArticleUrl);
    }

    // Comment Form Submission AJAX
    const commentForm = document.getElementById('publicCommentForm');
    const commentAlert = document.getElementById('commentAlert');
    const btnSubmit = document.getElementById('btnSubmitComment');

    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            commentAlert.style.display = 'none';

            const payload = {
                article_id: commentForm.elements['article_id'].value,
                author_name: commentForm.elements['author_name'].value,
                author_email: commentForm.elements['author_email'].value,
                content: commentForm.elements['content'].value
            };

            fetch('/api/comments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { status: res.status, data: data };
                });
            })
            .then(function(result) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Komentar';

                if (result.status >= 200 && result.status < 300) {
                    commentAlert.className = 'comment-alert success';
                    commentAlert.innerHTML = '<i class="fas fa-check-circle"></i> Terima kasih! Komentar Anda berhasil dikirim dan akan tampil setelah disetujui moderator.';
                    commentAlert.style.display = 'block';
                    commentForm.reset();
                } else {
                    commentAlert.className = 'comment-alert error';
                    commentAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (result.data.error || 'Terjadi kesalahan saat mengirim komentar.');
                    commentAlert.style.display = 'block';
                }
            })
            .catch(function(err) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Komentar';
                commentAlert.className = 'comment-alert error';
                commentAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i> Gagal menghubungi server. Silakan coba kembali.';
                commentAlert.style.display = 'block';
            });
        });
    }
});
</script>
