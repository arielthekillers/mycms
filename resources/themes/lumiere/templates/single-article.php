<?php
/**
 * Single Article Template - Theme Lumière
 * Ultra-premium editorial reading layout with interactive comments, social sharing, and related posts.
 */

$app = \App\Core\Application::getInstance();

// 1. Author Resolution with Avatar
$authorName = $article['author_name'] ?? 'Redaksi';
$authorAvatar = null;
$authorEmail = null;
$authorId = $article['author_id'] ?? null;
if (!empty($authorId)) {
    try {
        $u = $app->db->selectOne(
            "SELECT u.id, u.name, u.email, u.avatar_media_id, m.disk_path as avatar_path 
             FROM users u 
             LEFT JOIN media m ON u.avatar_media_id = m.id 
             WHERE u.id = ?",
            [$authorId]
        );
        if ($u) {
            $authorName = $u['name'] ?: $authorName;
            $authorEmail = $u['email'];
            if (!empty($u['avatar_path'])) {
                $authorAvatar = '/uploads/' . $u['avatar_path'];
            }
        }
    } catch (\Exception $e) {}
}

// 2. Featured Image with Caption & Alt
$featuredImg = null;
$featuredImgCaption = null;
$featuredImgAlt = $article['title'];
if (!empty($article['featured_media_id'])) {
    try {
        $media = $app->db->selectOne("SELECT disk_path, caption, alt_text FROM media WHERE id = ?", [$article['featured_media_id']]);
        if ($media) {
            $featuredImg = '/uploads/' . $media['disk_path'];
            $featuredImgCaption = !empty($media['caption']) ? $media['caption'] : null;
            if (!empty($media['alt_text'])) {
                $featuredImgAlt = $media['alt_text'];
            }
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

// 6. Navigation: Previous & Next Articles
$prevArticle = null;
$nextArticle = null;
try {
    $pubAt = $article['published_at'] ?? date('Y-m-d H:i:s');
    $prevArticle = $app->db->selectOne(
        "SELECT id, title, slug, published_at FROM articles WHERE status = 'published' AND published_at < :pub_at ORDER BY published_at DESC LIMIT 1",
        ['pub_at' => $pubAt]
    );
    $nextArticle = $app->db->selectOne(
        "SELECT id, title, slug, published_at FROM articles WHERE status = 'published' AND published_at > :pub_at ORDER BY published_at ASC LIMIT 1",
        ['pub_at' => $pubAt]
    );
} catch (\Exception $e) {}

// 7. Related Articles with Thumbnails
$relatedArticles = [];
try {
    $relatedArticles = $app->db->select(
        "SELECT a.id, a.title, a.slug, a.published_at, a.excerpt, m.disk_path as thumbnail_path 
         FROM articles a
         LEFT JOIN media m ON a.featured_media_id = m.id
         WHERE a.status = 'published' AND a.id != :id 
         ORDER BY a.published_at DESC LIMIT 3",
        ['id' => $article['id']]
    );
} catch (\Exception $e) {}

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'];
$publishedDate = !empty($article['published_at']) ? date('d F Y', strtotime($article['published_at'])) : date('d F Y', strtotime($article['created_at'] ?? 'now'));
?>

<!-- Reading Progress Bar -->
<div class="reading-progress-track">
    <div class="reading-progress-bar" id="readingProgressBar"></div>
</div>

<div class="article-page-wrapper">
    <!-- Editorial Header Section -->
    <header class="single-article-header">
        <div class="single-article-header-container">
            <div class="article-header-topline">
                <nav class="article-breadcrumb" aria-label="Breadcrumb">
                    <a href="/"><i class="fas fa-home"></i> Beranda</a>
                    <span class="sep"><i class="fas fa-chevron-right"></i></span>
                    <?php if ($primaryCategory): ?>
                        <a href="/kategori/<?= htmlspecialchars($primaryCategory['slug']) ?>"><?= htmlspecialchars($primaryCategory['name']) ?></a>
                        <span class="sep"><i class="fas fa-chevron-right"></i></span>
                    <?php endif; ?>
                    <span class="current">Artikel</span>
                </nav>

                <?php if ($primaryCategory): ?>
                    <a href="/kategori/<?= htmlspecialchars($primaryCategory['slug']) ?>" class="category-badge-pill">
                        <span class="cat-dot"></span>
                        <?= htmlspecialchars($primaryCategory['name']) ?>
                    </a>
                <?php endif; ?>
            </div>

            <h1 class="single-article-title">
                <?= htmlspecialchars($article['title']) ?>
            </h1>

            <?php if (!empty($article['excerpt'])): ?>
                <p class="article-lead-excerpt">
                    <?= htmlspecialchars($article['excerpt']) ?>
                </p>
            <?php endif; ?>

            <!-- Refined Byline Meta Bar -->
            <div class="article-byline-bar">
                <div class="byline-author-group">
                    <?php if ($authorAvatar): ?>
                        <img src="<?= htmlspecialchars($authorAvatar) ?>" alt="<?= htmlspecialchars($authorName) ?>" class="byline-avatar-img">
                    <?php else: ?>
                        <div class="byline-avatar-placeholder">
                            <?= strtoupper(mb_substr($authorName, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="byline-author-text">
                        <span class="byline-by">Ditulis oleh</span>
                        <strong class="byline-name"><?= htmlspecialchars($authorName) ?></strong>
                    </div>
                </div>

                <div class="byline-meta-divider"></div>

                <div class="byline-meta-details">
                    <span class="byline-item" title="Tanggal Publikasi">
                        <i class="far fa-calendar-alt byline-icon"></i>
                        <span><?= $publishedDate ?></span>
                    </span>
                    <span class="byline-dot">&bull;</span>
                    <span class="byline-item" title="Estimasi Waktu Baca">
                        <i class="far fa-clock byline-icon"></i>
                        <span><?= $readTime ?> Menit Baca</span>
                    </span>
                    <span class="byline-dot">&bull;</span>
                    <a href="#comments" class="byline-item byline-comments-link" title="Menuju Komentar">
                        <i class="far fa-comments byline-icon"></i>
                        <span><?= count($comments) ?> Komentar</span>
                    </a>
                </div>

                <div class="byline-tools">
                    <div class="font-sizer-widget" title="Atur Ukuran Font Teks">
                        <span class="sizer-label"><i class="fas fa-font"></i></span>
                        <button type="button" class="btn-text-size" id="btnDecreaseFont" title="Perkecil Font">A-</button>
                        <button type="button" class="btn-text-size active" id="btnResetFont" title="Ukuran Font Normal">A</button>
                        <button type="button" class="btn-text-size" id="btnIncreaseFont" title="Perbesar Font">A+</button>
                    </div>
                    <button type="button" class="btn-print-article" onclick="window.print()" title="Cetak Artikel">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Floating Quick Interaction Dock -->
    <aside class="article-flying-dock" aria-label="Aksi Berbagi Artikel">
        <div class="dock-inner">
            <span class="dock-brand-label">BAGIKAN</span>
            <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $currentUrl) ?>" target="_blank" rel="noopener" class="dock-btn share-wa" title="Bagikan ke WhatsApp" aria-label="Bagikan ke WhatsApp">
                <i class="fab fa-whatsapp"></i>
                <span class="dock-tooltip">WhatsApp</span>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="dock-btn share-fb" title="Bagikan ke Facebook" aria-label="Bagikan ke Facebook">
                <i class="fab fa-facebook-f"></i>
                <span class="dock-tooltip">Facebook</span>
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['title']) ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="dock-btn share-tw" title="Bagikan ke X" aria-label="Bagikan ke X">
                <i class="fab fa-x-twitter"></i>
                <span class="dock-tooltip">X</span>
            </a>
            <a href="https://t.me/share/url?url=<?= urlencode($currentUrl) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" class="dock-btn share-tg" title="Bagikan ke Telegram" aria-label="Bagikan ke Telegram">
                <i class="fab fa-telegram-plane"></i>
                <span class="dock-tooltip">Telegram</span>
            </a>
            <button type="button" class="dock-btn share-copy" id="btnCopyLink" onclick="copyArticleUrl()" title="Salin Tautan" aria-label="Salin Tautan">
                <i class="fas fa-link"></i>
                <span class="dock-tooltip">Salin Link</span>
            </button>
            <div class="dock-divider"></div>
            <button type="button" class="dock-btn dock-btn-like" id="btnLikeDock" onclick="toggleArticleLike()" title="Sukai Artikel Ini" aria-label="Sukai Artikel">
                <i class="fas fa-heart"></i>
                <span class="dock-like-badge" id="dockLikeBadge">0</span>
                <span class="dock-tooltip">Suka</span>
            </button>
            <a href="#comments" class="dock-btn dock-btn-comments" title="Lihat Komentar" aria-label="Komentar">
                <i class="far fa-comment-dots"></i>
                <span class="dock-comment-badge"><?= count($comments) ?></span>
                <span class="dock-tooltip">Komentar</span>
            </a>
        </div>
    </aside>

    <!-- Main Container -->
    <div class="single-article-container">
        <!-- Featured Image (Unobstructed & Clean with Figcaption) -->
        <?php if ($featuredImg): ?>
            <figure class="featured-media-frame">
                <div class="featured-image-canvas">
                    <img src="<?= htmlspecialchars($featuredImg) ?>" alt="<?= htmlspecialchars($featuredImgAlt) ?>" class="featured-image">
                </div>
                <?php if ($featuredImgCaption): ?>
                    <figcaption class="featured-image-caption">
                        <i class="fas fa-camera-retro"></i>
                        <span><?= htmlspecialchars($featuredImgCaption) ?></span>
                    </figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>

        <!-- Article Body Card -->
        <article class="article-body-wrapper">
            <!-- Article Content with Editorial Typography -->
            <div class="article-content" id="articleBodyContent">
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
                    <span class="tags-title"><i class="fas fa-tags"></i> Topik Terkait:</span>
                    <div class="tags-list">
                        <?php foreach ($tags as $tag): ?>
                            <span class="tag-pill">#<?= htmlspecialchars($tag['name'] ?? $tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Article Interactive Appreciation Box (Heart / Like) -->
            <div class="article-appreciation-box">
                <div class="appreciation-card">
                    <div class="appreciation-left">
                        <button type="button" class="btn-like-pill" id="btnArticleLikeBottom" onclick="toggleArticleLike()">
                            <i class="fas fa-heart like-heart-icon"></i>
                            <span class="like-btn-text">Suka Artikel Ini</span>
                            <span class="like-counter-pill" id="bottomLikeCounter">0</span>
                        </button>
                    </div>
                    <div class="appreciation-right">
                        <span class="appreciation-hint"><i class="far fa-smile-beam"></i> Beri apresiasi jika warta ini memberi manfaat dan wawasan bagi Anda.</span>
                    </div>
                </div>
            </div>


            <!-- Author Bio Card -->
            <div class="author-bio-card">
                <div class="author-bio-avatar-wrap">
                    <?php if ($authorAvatar): ?>
                        <img src="<?= htmlspecialchars($authorAvatar) ?>" alt="<?= htmlspecialchars($authorName) ?>" class="author-bio-img">
                    <?php else: ?>
                        <div class="author-bio-initial">
                            <?= strtoupper(mb_substr($authorName, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="author-bio-info">
                    <div class="author-bio-top">
                        <h4 class="author-title"><?= htmlspecialchars($authorName) ?></h4>
                        <span class="author-badge"><i class="fas fa-check-circle"></i> Penulis Terverifikasi</span>
                    </div>
                    <p class="author-role">Redaksi & Kontributor Pondok Pesantren Darussalam</p>
                    <p class="author-desc">Menyajikan informasi edukatif, warta kegiatan santri, dan artikel keislaman terpercaya untuk mencerdaskan umat dan masyarakat.</p>
                </div>
            </div>

            <!-- Prev / Next Article Navigation -->
            <?php if ($prevArticle || $nextArticle): ?>
                <nav class="article-navigation-grid" aria-label="Navigasi Warta">
                    <?php if ($prevArticle): ?>
                        <a href="/artikel/<?= htmlspecialchars($prevArticle['slug']) ?>" class="article-nav-card nav-prev">
                            <span class="nav-direction"><i class="fas fa-arrow-left"></i> Artikel Sebelumnya</span>
                            <h4 class="nav-article-title"><?= htmlspecialchars($prevArticle['title']) ?></h4>
                            <span class="nav-date"><i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($prevArticle['published_at'])) ?></span>
                        </a>
                    <?php else: ?>
                        <div class="article-nav-card nav-empty"></div>
                    <?php endif; ?>

                    <?php if ($nextArticle): ?>
                        <a href="/artikel/<?= htmlspecialchars($nextArticle['slug']) ?>" class="article-nav-card nav-next">
                            <span class="nav-direction">Artikel Selanjutnya <i class="fas fa-arrow-right"></i></span>
                            <h4 class="nav-article-title"><?= htmlspecialchars($nextArticle['title']) ?></h4>
                            <span class="nav-date"><i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($nextArticle['published_at'])) ?></span>
                        </a>
                    <?php else: ?>
                        <div class="article-nav-card nav-empty"></div>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

            <!-- Comments Section -->
            <section class="comments-section" id="comments">
                <div class="section-header-row">
                    <div class="comments-header-badge">
                        <i class="far fa-comments"></i>
                        <span>Diskusi Pembaca</span>
                    </div>
                    <h3 class="comments-heading">
                        Komentar Pembaca <span class="comment-count-badge"><?= count($comments) ?></span>
                    </h3>
                </div>

                <!-- Comments List -->
                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <div class="no-comments-box">
                            <div class="no-comments-icon">
                                <i class="far fa-comment-dots"></i>
                            </div>
                            <h5>Belum ada komentar untuk warta ini</h5>
                            <p>Jadilah yang pertama menuangkan pandangan dan tanggapan berharga Anda di bawah ini!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comm): ?>
                            <div class="comment-item">
                                <div class="comment-avatar">
                                    <?= strtoupper(mb_substr($comm['author_name'], 0, 1)) ?>
                                </div>
                                <div class="comment-content-box">
                                    <div class="comment-header">
                                        <div class="comment-author-wrap">
                                            <strong class="comment-author"><?= htmlspecialchars($comm['author_name']) ?></strong>
                                            <span class="comment-user-badge"><i class="fas fa-user-check"></i> Pembaca</span>
                                        </div>
                                        <span class="comment-date">
                                            <i class="far fa-clock"></i> <?= date('d M Y, H:i', strtotime($comm['created_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="comment-text">
                                        <?= nl2br(htmlspecialchars($comm['content'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Comment Form Container -->
                <div class="comment-form-container">
                    <div class="comment-form-head">
                        <div class="cf-icon">
                            <i class="fas fa-pen-nib"></i>
                        </div>
                        <div>
                            <h4 class="form-title">Tinggalkan Komentar</h4>
                            <p class="form-subtitle">Alamat email Anda dijaga kerahasiaannya. Komentar akan dimoderasi sebelum dipublikasikan secara resmi.</p>
                        </div>
                    </div>

                    <div id="commentAlert" class="comment-alert" style="display: none;"></div>

                    <form id="publicCommentForm" class="comment-form">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="author_name"><i class="fas fa-user"></i> Nama Lengkap <span class="req">*</span></label>
                                <input type="text" id="author_name" name="author_name" required placeholder="Contoh: Ahmad Fauzi">
                            </div>
                            <div class="form-group">
                                <label for="author_email"><i class="fas fa-envelope"></i> Alamat Email <span class="req">*</span></label>
                                <input type="email" id="author_email" name="author_email" required placeholder="email@contoh.com">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment_content"><i class="fas fa-comment-alt"></i> Komentar / Tanggapan Anda <span class="req">*</span></label>
                            <textarea id="comment_content" name="content" rows="4" required placeholder="Tuliskan pandangan atau tanggapan Anda dengan bahasa yang santun..."></textarea>
                        </div>
                        <div class="form-submit-row">
                            <button type="submit" id="btnSubmitComment" class="btn-submit-comment">
                                <i class="fas fa-paper-plane"></i> Kirim Komentar
                            </button>
                            <span class="form-security-note"><i class="fas fa-shield-alt"></i> Dilindungi filter anti-spam otomatis</span>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Related Articles Section (Warta Terkait) -->
            <?php if (!empty($relatedArticles)): ?>
                <section class="related-articles-section">
                    <div class="section-badge-header">
                        <span class="badge-accent"><i class="fas fa-newspaper"></i> Warta Rekomendasi</span>
                        <h3 class="related-heading">Baca Warta Lainnya</h3>
                    </div>
                    <div class="related-grid">
                        <?php foreach ($relatedArticles as $rel): ?>
                            <a href="/artikel/<?= htmlspecialchars($rel['slug']) ?>" class="related-card">
                                <div class="related-card-media">
                                    <?php if (!empty($rel['thumbnail_path'])): ?>
                                        <img src="/uploads/<?= htmlspecialchars($rel['thumbnail_path']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" class="related-card-img" loading="lazy">
                                    <?php else: ?>
                                        <div class="related-card-placeholder">
                                            <i class="fas fa-book-open"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="related-card-content">
                                    <span class="rel-date">
                                        <i class="far fa-calendar-alt"></i> <?= !empty($rel['published_at']) ? date('d M Y', strtotime($rel['published_at'])) : '' ?>
                                    </span>
                                    <h4 class="rel-title"><?= htmlspecialchars($rel['title']) ?></h4>
                                    <span class="rel-link">Baca Selengkapnya <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <div class="back-home-bottom">
                <a href="/" class="btn-back-home"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
            </div>

        </article>
    </div>
</div>

<!-- Toast Feedback for Copy Link -->
<div id="linkCopiedToast" class="link-copied-toast">
    <div class="toast-icon"><i class="fas fa-check"></i></div>
    <div class="toast-body">
        <strong>Tautan Tersalin!</strong>
        <span>URL artikel berhasil disalin ke clipboard Anda.</span>
    </div>
</div>

<style>
    /* ========================================================
       Theme Lumière - High-End Editorial Single Article Styling
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

    .article-page-wrapper {
        background-color: var(--bg-light, #f8fafc);
        min-height: 85vh;
        padding-bottom: 4rem;
        position: relative;
    }

    /* --------------------------------------------------------
       Editorial Header & Title Section
       -------------------------------------------------------- */
    .single-article-header {
        background: radial-gradient(circle at 50% 0%, rgba(27, 178, 166, 0.2) 0%, transparent 60%),
                    linear-gradient(180deg, var(--secondary-color, #0f172a) 0%, #162032 100%);
        color: white;
        padding: 4rem 1.5rem 3.5rem;
        position: relative;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
    }
    .single-article-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 15%, rgba(244, 180, 26, 0.12) 0%, transparent 65%);
        pointer-events: none;
    }
    .single-article-header-container {
        max-width: 880px;
        margin: 0 auto;
        position: relative;
        z-index: 3;
        width: 100%;
        box-sizing: border-box;
    }
    .article-header-topline {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .article-breadcrumb {
        font-size: 0.82rem;
        color: rgba(255, 255, 255, 0.7);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .article-breadcrumb a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: color 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .article-breadcrumb a:hover {
        color: var(--gold, #f4b41a);
    }
    .article-breadcrumb .sep {
        font-size: 0.65rem;
        opacity: 0.45;
    }
    .article-breadcrumb .current {
        color: var(--gold, #f4b41a);
        font-weight: 500;
    }
    .category-badge-pill {
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
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        transition: all 0.25s ease;
    }
    .category-badge-pill .cat-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--gold, #f4b41a);
        box-shadow: 0 0 6px var(--gold, #f4b41a);
    }
    .category-badge-pill:hover {
        background: var(--gold, #f4b41a);
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(244, 180, 26, 0.35);
    }

    .single-article-title {
        font-family: 'Playfair Display', Georgia, 'Times New Roman', serif;
        font-size: clamp(2rem, 3.8vw, 2.85rem);
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.015em;
        margin: 0 0 1.25rem;
        color: #ffffff;
        text-shadow: 0 2px 14px rgba(0, 0, 0, 0.35);
    }
    .article-lead-excerpt {
        font-size: 1.15rem;
        line-height: 1.6;
        color: rgba(241, 245, 249, 0.85);
        max-width: 760px;
        margin: 0 auto 1.5rem;
        font-style: italic;
    }

    /* Byline Meta Bar */
    .article-byline-bar {
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
    .byline-author-group {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
    }
    .byline-avatar-img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--gold, #f4b41a);
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    }
    .byline-avatar-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--teal, #1bb2a6) 0%, #0d9488 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.88rem;
        border: 2px solid var(--gold, #f4b41a);
    }
    .byline-author-text {
        text-align: left;
        line-height: 1.25;
    }
    .byline-by {
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.6);
        display: block;
    }
    .byline-name {
        color: #ffffff;
        font-weight: 600;
    }
    .byline-meta-divider {
        width: 1px;
        height: 22px;
        background: rgba(255, 255, 255, 0.18);
    }
    .byline-meta-details {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }
    .byline-item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .byline-comments-link:hover {
        color: var(--gold, #f4b41a);
    }
    .byline-icon {
        color: var(--gold, #f4b41a);
        font-size: 0.85rem;
    }
    .byline-dot {
        color: rgba(255, 255, 255, 0.35);
        font-size: 0.75rem;
    }

    /* Byline Tools (Font Sizer & Print) */
    .byline-tools {
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
    .btn-print-article {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.85rem;
        cursor: pointer;
        padding: 0.3rem 0.5rem;
        border-radius: 50%;
        transition: all 0.2s;
    }
    .btn-print-article:hover {
        color: var(--gold, #f4b41a);
        background: rgba(255, 255, 255, 0.12);
    }

    /* --------------------------------------------------------
       Floating Quick Action Dock
       -------------------------------------------------------- */
    .article-flying-dock {
        position: fixed;
        left: max(1.25rem, calc(50% - 450px - 85px));
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
    .dock-btn-like {
        background: #f1f5f9;
        color: #64748b;
        box-shadow: none;
        border: 1px solid #e2e8f0;
    }
    .dock-btn-like:hover, .dock-btn-like.liked {
        background: #ef4444;
        color: white;
        border-color: #ef4444;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
    }
    .dock-btn-like.liked i {
        animation: heartBeat 0.4s ease-in-out;
    }
    .dock-like-badge, .dock-comment-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: var(--secondary-color, #0f172a);
        color: white;
        font-size: 0.62rem;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 3px;
        border: 2px solid white;
    }
    .dock-btn-comments {
        background: #f1f5f9;
        color: #64748b;
        box-shadow: none;
        border: 1px solid #e2e8f0;
    }
    .dock-btn-comments:hover {
        background: var(--teal, #1bb2a6);
        color: white;
        border-color: var(--teal, #1bb2a6);
        box-shadow: 0 4px 14px rgba(27, 178, 166, 0.35);
    }

    /* Tooltip */
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
       Article Main Container & Featured Image
       -------------------------------------------------------- */
    .single-article-container {
        max-width: 880px;
        margin: 2rem auto 0;
        padding: 0 1.25rem;
        position: relative;
        z-index: 5;
    }

    .featured-media-frame {
        margin: 0 0 1.75rem;
        border-radius: 22px;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 14px 40px -10px rgba(15, 23, 42, 0.08);
        transition: box-shadow 0.3s ease;
    }
    .featured-media-frame:hover {
        box-shadow: 0 20px 48px -10px rgba(15, 23, 42, 0.12);
    }
    .featured-image-canvas {
        overflow: hidden;
        max-height: 540px;
        background: #0f172a;
    }
    .featured-image {
        width: 100%;
        height: auto;
        max-height: 540px;
        object-fit: cover;
        display: block;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .featured-media-frame:hover .featured-image {
        transform: scale(1.015);
    }
    .featured-image-caption {
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
    .featured-image-caption i {
        color: var(--teal, #1bb2a6);
        font-size: 0.85rem;
    }

    /* --------------------------------------------------------
       Article Body Content
       -------------------------------------------------------- */
    .article-body-wrapper {
        background: white;
        border-radius: 24px;
        padding: 3rem 3.5rem;
        box-shadow: 0 12px 40px -10px rgba(15, 23, 42, 0.05);
        border: 1px solid #edf2f7;
    }

    .article-content {
        font-size: 1.1rem;
        line-height: 1.85;
        color: #334155;
        word-break: break-word;
        transition: font-size 0.2s ease;
    }
    .article-content.font-sm {
        font-size: 0.98rem;
        line-height: 1.75;
    }
    .article-content.font-lg {
        font-size: 1.22rem;
        line-height: 1.95;
    }

    /* Editorial Drop Cap on First Paragraph */
    .article-content > p:first-of-type::first-letter {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 3.6rem;
        float: left;
        line-height: 0.82;
        margin: 0.15rem 0.65rem 0 0;
        font-weight: 700;
        color: var(--teal, #1bb2a6);
        text-shadow: 1px 1px 2px rgba(27, 178, 166, 0.2);
    }
    .article-content > p:first-of-type {
        font-size: 1.18rem;
        line-height: 1.85;
        color: #1e293b;
        font-weight: 450;
    }

    .article-content p {
        margin-bottom: 1.35rem;
    }
    .article-content h2 {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: #0f172a;
        margin: 2.25rem 0 0.85rem;
        letter-spacing: -0.015em;
        line-height: 1.3;
        position: relative;
        padding-left: 0.85rem;
        border-left: 4px solid var(--teal, #1bb2a6);
    }
    .article-content h3 {
        font-size: 1.38rem;
        font-weight: 700;
        color: #0f172a;
        margin: 1.75rem 0 0.65rem;
        line-height: 1.35;
    }
    .article-content h4 {
        font-size: 1.15rem;
        font-weight: 600;
        color: #1e293b;
        margin: 1.35rem 0 0.5rem;
    }
    .article-content a {
        color: var(--teal, #1bb2a6);
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.2s;
    }
    .article-content a:hover {
        color: var(--teal-dark, #0d9488);
    }
    .article-content strong, .article-content b {
        color: #0f172a;
        font-weight: 600;
    }
    .article-content blockquote {
        position: relative;
        border-left: 4px solid var(--gold, #f4b41a);
        background: linear-gradient(135deg, rgba(244, 180, 26, 0.05) 0%, rgba(27, 178, 166, 0.04) 100%);
        padding: 1.25rem 1.75rem;
        margin: 1.75rem 0;
        border-radius: 0 16px 16px 0;
        font-style: italic;
        color: #334155;
        font-size: 1.08rem;
        line-height: 1.75;
    }
    .article-content blockquote::before {
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
    .article-content ul, .article-content ol {
        margin: 1.15rem 0 1.35rem 1.75rem;
    }
    .article-content li {
        margin-bottom: 0.5rem;
        padding-left: 0.25rem;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 14px;
        margin: 1.5rem auto;
        display: block;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid #edf2f7;
    }
    .article-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.75rem 0;
        font-size: 0.95rem;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .article-content th, .article-content td {
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        text-align: left;
    }
    .article-content th {
        background: #f8fafc;
        font-weight: 700;
        color: #0f172a;
    }
    .article-content tr:nth-child(even) td {
        background: #fafbfc;
    }

    /* --------------------------------------------------------
       Tags Section
       -------------------------------------------------------- */
    .article-tags-wrapper {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin: 2rem 0 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
        flex-wrap: wrap;
    }
    .tags-title {
        font-weight: 700;
        font-size: 0.92rem;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .tags-title i {
        color: var(--teal, #1bb2a6);
    }
    .tags-list {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .tag-pill {
        background: #f1f5f9;
        color: var(--teal-dark, #0d9488);
        padding: 0.35rem 0.9rem;
        border-radius: 50px;
        font-size: 0.84rem;
        font-weight: 500;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .tag-pill:hover {
        background: var(--teal, #1bb2a6);
        color: white;
        border-color: var(--teal, #1bb2a6);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(27, 178, 166, 0.25);
    }

    /* --------------------------------------------------------
       Article Appreciation / Like Section
       -------------------------------------------------------- */
    .article-appreciation-box {
        margin: 1.75rem 0;
    }
    .appreciation-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1.25rem 1.75rem;
        flex-wrap: wrap;
    }
    .btn-like-pill {
        background: white;
        border: 2px solid #fecaca;
        color: #e11d48;
        padding: 0.65rem 1.45rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.92rem;
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(225, 29, 72, 0.1);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-like-pill:hover, .btn-like-pill.liked {
        background: #e11d48;
        color: white;
        border-color: #e11d48;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(225, 29, 72, 0.3);
    }
    .btn-like-pill.liked .like-heart-icon {
        animation: heartBeat 0.45s ease-in-out;
    }
    .like-counter-pill {
        background: rgba(225, 29, 72, 0.12);
        color: #e11d48;
        padding: 0.15rem 0.55rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 800;
        transition: all 0.25s;
    }
    .btn-like-pill.liked .like-counter-pill {
        background: white;
        color: #e11d48;
    }
    .appreciation-hint {
        font-size: 0.85rem;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-style: italic;
    }

    @keyframes heartBeat {
        0% { transform: scale(1); }
        30% { transform: scale(1.35); }
        60% { transform: scale(0.9); }
        100% { transform: scale(1); }
    }

    /* --------------------------------------------------------
       Horizontal Share Strip
       -------------------------------------------------------- */


    /* --------------------------------------------------------
       Author Bio Card
       -------------------------------------------------------- */
    .author-bio-card {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1.5rem 2rem;
        margin: 2.25rem 0;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    }
    .author-bio-avatar-wrap {
        flex-shrink: 0;
    }
    .author-bio-img {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    }
    .author-bio-initial {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--teal, #1bb2a6) 0%, #0d9488 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        box-shadow: 0 6px 18px rgba(27, 178, 166, 0.25);
        border: 3px solid white;
    }
    .author-bio-info {
        flex: 1;
    }
    .author-bio-top {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
        margin-bottom: 0.25rem;
    }
    .author-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .author-badge {
        font-size: 0.72rem;
        background: rgba(27, 178, 166, 0.12);
        color: var(--teal-dark, #0d9488);
        padding: 0.15rem 0.55rem;
        border-radius: 20px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .author-role {
        font-size: 0.84rem;
        color: var(--teal, #1bb2a6);
        font-weight: 600;
        margin-bottom: 0.4rem;
    }
    .author-desc {
        font-size: 0.88rem;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    /* --------------------------------------------------------
       Article Navigation (Previous / Next)
       -------------------------------------------------------- */
    .article-navigation-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin: 2.25rem 0;
        padding-top: 2rem;
        border-top: 1px solid #edf2f7;
    }
    .article-nav-card {
        display: flex;
        flex-direction: column;
        padding: 1.25rem 1.5rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .article-nav-card:hover {
        background: white;
        border-color: var(--teal, #1bb2a6);
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(27, 178, 166, 0.12);
    }
    .article-nav-card.nav-next {
        text-align: right;
    }
    .nav-direction {
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--teal, #1bb2a6);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.4rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .nav-next .nav-direction {
        justify-content: flex-end;
    }
    .nav-article-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.4;
        margin-bottom: 0.4rem;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .nav-date {
        font-size: 0.76rem;
        color: #94a3b8;
    }

    /* --------------------------------------------------------
       Comments Section
       -------------------------------------------------------- */
    .comments-section {
        margin: 2.75rem 0 1.5rem;
        padding-top: 2.25rem;
        border-top: 2px solid #f1f5f9;
    }
    .section-header-row {
        margin-bottom: 1.5rem;
    }
    .comments-header-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--teal, #1bb2a6);
        background: rgba(27, 178, 166, 0.1);
        padding: 0.2rem 0.65rem;
        border-radius: 30px;
        margin-bottom: 0.4rem;
    }
    .comments-heading {
        font-size: 1.4rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .comment-count-badge {
        font-size: 0.85rem;
        background: #0f172a;
        color: white;
        padding: 0.15rem 0.65rem;
        border-radius: 50px;
        font-weight: 700;
    }
    .comments-list {
        display: flex;
        flex-direction: column;
        gap: 1.15rem;
        margin-bottom: 2.5rem;
    }
    .no-comments-box {
        text-align: center;
        padding: 2.75rem 1.5rem;
        background: #f8fafc;
        border-radius: 18px;
        border: 1px dashed #cbd5e1;
        color: #64748b;
    }
    .no-comments-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: white;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-bottom: 0.85rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .no-comments-box h5 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.35rem;
    }
    .no-comments-box p {
        font-size: 0.88rem;
        margin: 0;
        max-width: 480px;
        margin: 0 auto;
    }

    .comment-item {
        display: flex;
        gap: 1.15rem;
        padding: 1.35rem 1.5rem;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .comment-item:hover {
        background: white;
        box-shadow: 0 6px 20px rgba(0,0,0,0.04);
    }
    .comment-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--teal, #1bb2a6) 0%, #0d9488 100%);
        color: white;
        font-weight: 700;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(27, 178, 166, 0.25);
    }
    .comment-content-box {
        flex: 1;
    }
    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .comment-author-wrap {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .comment-author {
        font-size: 0.98rem;
        color: #0f172a;
        font-weight: 700;
    }
    .comment-user-badge {
        font-size: 0.68rem;
        background: #e2e8f0;
        color: #475569;
        padding: 0.1rem 0.45rem;
        border-radius: 12px;
        font-weight: 600;
    }
    .comment-date {
        font-size: 0.78rem;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .comment-text {
        font-size: 0.94rem;
        color: #475569;
        line-height: 1.65;
    }

    /* --------------------------------------------------------
       Comment Form Container
       -------------------------------------------------------- */
    .comment-form-container {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2.25rem 2.5rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.03);
    }
    .comment-form-head {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }
    .cf-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(27, 178, 166, 0.12);
        color: var(--teal, #1bb2a6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .form-title {
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        color: #0f172a;
    }
    .form-subtitle {
        font-size: 0.86rem;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }
    .comment-alert {
        padding: 0.95rem 1.25rem;
        border-radius: 12px;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.65rem;
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
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.45rem;
        color: #1e293b;
    }
    .form-group label i {
        color: var(--teal, #1bb2a6);
        margin-right: 0.3rem;
        font-size: 0.82rem;
    }
    .form-group label .req {
        color: #ef4444;
    }
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        font-family: inherit;
        font-size: 0.92rem;
        background: white;
        color: #0f172a;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }
    .form-group input:focus, .form-group textarea:focus {
        border-color: var(--teal, #1bb2a6);
        outline: none;
        box-shadow: 0 0 0 4px rgba(27, 178, 166, 0.15);
    }
    .form-submit-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }
    .btn-submit-comment {
        background: linear-gradient(135deg, var(--teal, #1bb2a6) 0%, #0d9488 100%);
        color: white;
        border: none;
        padding: 0.85rem 2rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.92rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        box-shadow: 0 6px 20px rgba(27, 178, 166, 0.28);
        transition: all 0.25s ease;
    }
    .btn-submit-comment:hover {
        background: linear-gradient(135deg, #149c91 0%, #0f766e 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(27, 178, 166, 0.35);
    }
    .form-security-note {
        font-size: 0.78rem;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* --------------------------------------------------------
       Related Articles Section
       -------------------------------------------------------- */
    .related-articles-section {
        margin-top: 3rem;
        padding-top: 2.25rem;
        border-top: 1px solid #edf2f7;
    }
    .section-badge-header {
        margin-bottom: 1.5rem;
    }
    .badge-accent {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--teal, #1bb2a6);
        background: rgba(27, 178, 166, 0.1);
        padding: 0.2rem 0.65rem;
        border-radius: 30px;
        margin-bottom: 0.4rem;
    }
    .related-heading {
        font-size: 1.4rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .related-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.35rem;
    }
    .related-card {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        overflow: hidden;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    }
    .related-card:hover {
        border-color: var(--teal, #1bb2a6);
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    }
    .related-card-media {
        width: 100%;
        height: 160px;
        overflow: hidden;
        background: #0f172a;
        position: relative;
    }
    .related-card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.5s ease;
    }
    .related-card:hover .related-card-img {
        transform: scale(1.08);
    }
    .related-card-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: rgba(255, 255, 255, 0.35);
        font-size: 2.5rem;
    }
    .related-card-content {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .rel-date {
        font-size: 0.75rem;
        color: var(--teal, #1bb2a6);
        font-weight: 600;
        margin-bottom: 0.4rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .rel-title {
        font-size: 0.98rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.45;
        margin-bottom: 0.85rem;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .rel-link {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--teal, #1bb2a6);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: transform 0.2s;
    }
    .related-card:hover .rel-link {
        color: var(--teal-dark, #0d9488);
        transform: translateX(3px);
    }

    .back-home-bottom {
        margin-top: 2.75rem;
        text-align: center;
    }
    .btn-back-home {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        border: 2px solid var(--teal, #1bb2a6);
        color: var(--teal, #1bb2a6);
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.25s ease;
    }
    .btn-back-home:hover {
        background: var(--teal, #1bb2a6);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(27, 178, 166, 0.22);
    }

    /* --------------------------------------------------------
       Toast Notification
       -------------------------------------------------------- */
    .link-copied-toast {
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
    .link-copied-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
    .toast-icon {
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
    .toast-body {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }
    .toast-body strong {
        font-size: 0.88rem;
        color: #ffffff;
    }
    .toast-body span {
        font-size: 0.76rem;
        color: rgba(255, 255, 255, 0.7);
    }

    /* --------------------------------------------------------
       Responsive Styling
       -------------------------------------------------------- */
    @media (max-width: 1100px) {
        .single-article-title { font-size: 2.2rem; }
        .article-body-wrapper { padding: 2.25rem 2rem; }
        .related-grid { grid-template-columns: repeat(2, 1fr); }

        /* Floating Dock Bottom on Tablets & Mobile */
        .article-flying-dock {
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
        .dock-btn-like, .dock-btn-comments {
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
        .single-article-header { padding: 3rem 1.25rem 2.5rem; }
        .single-article-title { font-size: 1.65rem; }
        .article-byline-bar { padding: 0.5rem 0.85rem; border-radius: 20px; gap: 0.75rem; }
        .byline-meta-divider { display: none; }
        .byline-tools { border-left: none; padding-left: 0; width: 100%; justify-content: center; margin-top: 0.35rem; }
        .single-article-container { margin-top: 1.25rem; padding: 0 1rem; }
        .featured-media-frame { border-radius: 16px; margin-bottom: 1.25rem; }
        .article-body-wrapper { padding: 1.75rem 1.35rem; border-radius: 18px; }
        .article-content { font-size: 1.02rem; line-height: 1.8; }
        .article-content > p:first-of-type::first-letter { font-size: 2.8rem; }
        .article-navigation-grid { grid-template-columns: 1fr; }
        .article-nav-card.nav-next { text-align: left; }
        .nav-next .nav-direction { justify-content: flex-start; }
        .related-grid { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
        .comment-form-container { padding: 1.5rem 1.25rem; }
        .author-bio-card { flex-direction: column; text-align: center; padding: 1.35rem; }
        .author-bio-top { justify-content: center; }

        .appreciation-card { flex-direction: column; text-align: center; }
    }

    /* Print Optimization */
    @media print {
        .reading-progress-track, .article-flying-dock,
        .article-appreciation-box, .comments-section, .related-articles-section,
        .back-home-bottom, .byline-tools, #backToTop, .site-header, .site-footer {
            display: none !important;
        }
        .single-article-header {
            background: white !important;
            color: black !important;
            padding: 1rem 0 !important;
        }
        .single-article-title { color: black !important; text-shadow: none !important; }
        .article-byline-bar { color: #333 !important; background: none !important; border: none !important; }
        .article-body-wrapper { border: none !important; box-shadow: none !important; padding: 0 !important; }
    }
</style>

<script>
// --- Single Article Script Interactions ---
const CURRENT_ARTICLE_ID = <?= json_encode((int) $article['id']) ?>;

function copyArticleUrl() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        showToastNotification();
    }).catch(function() {
        // Fallback for older browsers
        const tempInput = document.createElement('input');
        tempInput.value = window.location.href;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showToastNotification();
    });
}

function showToastNotification() {
    const toast = document.getElementById('linkCopiedToast');
    if (toast) {
        toast.classList.add('show');
        setTimeout(function() {
            toast.classList.remove('show');
        }, 3200);
    }
}

// Like Article Logic (Stored in localStorage with real-time UI count)
function getLikeStorageKey() {
    return 'article_like_' + CURRENT_ARTICLE_ID;
}

function initLikeState() {
    const key = getLikeStorageKey();
    const isLiked = localStorage.getItem(key) === 'true';
    let baseCount = parseInt(localStorage.getItem(key + '_count') || '14', 10);
    
    if (isLiked) {
        document.getElementById('btnLikeDock')?.classList.add('liked');
        document.getElementById('btnArticleLikeBottom')?.classList.add('liked');
    }

    updateLikeCounters(baseCount);
}

function updateLikeCounters(count) {
    const dockBadge = document.getElementById('dockLikeBadge');
    const bottomCounter = document.getElementById('bottomLikeCounter');
    if (dockBadge) dockBadge.textContent = count;
    if (bottomCounter) bottomCounter.textContent = count;
}

function toggleArticleLike() {
    const key = getLikeStorageKey();
    const isLiked = localStorage.getItem(key) === 'true';
    let count = parseInt(localStorage.getItem(key + '_count') || '14', 10);

    const btnDock = document.getElementById('btnLikeDock');
    const btnBottom = document.getElementById('btnArticleLikeBottom');

    if (isLiked) {
        localStorage.setItem(key, 'false');
        count = Math.max(0, count - 1);
        localStorage.setItem(key + '_count', count);
        btnDock?.classList.remove('liked');
        btnBottom?.classList.remove('liked');
    } else {
        localStorage.setItem(key, 'true');
        count = count + 1;
        localStorage.setItem(key + '_count', count);
        btnDock?.classList.add('liked');
        btnBottom?.classList.add('liked');
    }

    updateLikeCounters(count);
}

document.addEventListener('DOMContentLoaded', function() {
    initLikeState();

    // 1. Reading Progress Bar Tracker
    const progressBar = document.getElementById('readingProgressBar');
    const articleContainer = document.querySelector('.article-body-wrapper');

    function updateReadingProgress() {
        if (!progressBar || !articleContainer) return;
        
        const rect = articleContainer.getBoundingClientRect();
        const winHeight = window.innerHeight;
        const totalHeight = rect.height;
        const currentScrolled = winHeight - rect.top;

        let percent = (currentScrolled / (totalHeight + winHeight * 0.4)) * 100;
        percent = Math.max(0, Math.min(100, percent));
        progressBar.style.width = percent + '%';
    }

    window.addEventListener('scroll', updateReadingProgress, { passive: true });
    window.addEventListener('resize', updateReadingProgress);
    updateReadingProgress();

    // 2. Font Size Adjustment
    const content = document.getElementById('articleBodyContent');
    const btnDecrease = document.getElementById('btnDecreaseFont');
    const btnReset = document.getElementById('btnResetFont');
    const btnIncrease = document.getElementById('btnIncreaseFont');
    const fontButtons = [btnDecrease, btnReset, btnIncrease];

    function setFontSize(mode) {
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
        localStorage.setItem('lumiere_font_mode', mode);
    }

    btnDecrease?.addEventListener('click', () => setFontSize('sm'));
    btnReset?.addEventListener('click', () => setFontSize('normal'));
    btnIncrease?.addEventListener('click', () => setFontSize('lg'));

    const savedFont = localStorage.getItem('lumiere_font_mode');
    if (savedFont) setFontSize(savedFont);

    // 3. Comment Form Submission AJAX
    const commentForm = document.getElementById('publicCommentForm');
    const commentAlert = document.getElementById('commentAlert');
    const btnSubmit = document.getElementById('btnSubmitComment');

    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim Komentar...';
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
                    commentAlert.innerHTML = '<i class="fas fa-check-circle"></i> Terima kasih! Komentar Anda berhasil dikirim dan akan tampil setelah disetujui oleh moderator.';
                    commentAlert.style.display = 'flex';
                    commentForm.reset();
                } else {
                    commentAlert.className = 'comment-alert error';
                    commentAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (result.data.error || 'Terjadi kesalahan saat mengirim komentar.');
                    commentAlert.style.display = 'flex';
                }
            })
            .catch(function(err) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Komentar';
                commentAlert.className = 'comment-alert error';
                commentAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i> Gagal menghubungi server. Silakan coba kembali sesaat lagi.';
                commentAlert.style.display = 'flex';
            });
        });
    }
});
</script>
