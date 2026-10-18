<?php
/**
 * Homepage Template - Theme Lumiere
 * Fully dynamic layout supporting real featured images, theme options, widget areas, and smooth interactions.
 */

$app = \App\Core\Application::getInstance();


// 2. Resolve Dynamic Category Link for "Lihat Seluruh Berita"
$allNewsUrl = '/kategori/kegiatan-santri';
try {
    $firstCat = $app->db->selectOne("SELECT slug FROM categories ORDER BY id ASC LIMIT 1");
    if (!empty($firstCat['slug'])) {
        $allNewsUrl = '/kategori/' . htmlspecialchars($firstCat['slug']);
    }
} catch (\Exception $e) {}

// 3. Resolve Real Articles with Featured Images & Excerpts
$articlesList = [];
try {
    $articlesList = $app->db->select("
        SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.published_at, a.created_at, m.disk_path as media_path
        FROM articles a
        LEFT JOIN media m ON a.featured_media_id = m.id
        WHERE a.status = 'published'
        ORDER BY a.published_at DESC
        LIMIT 3
    ");
} catch (\Exception $e) {
    $articlesList = $recentArticles ?? [];
}
?>

<style>
    
    /* Tentang Section */
    .tentang-section {
        padding: 6rem 5%;
        max-width: 1240px;
        margin: 0 auto;
        text-align: center;
    }
    .section-title {
        color: var(--teal);
        font-size: 2.25rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
    }
    .section-title::after {
        content: '';
        display: block;
        width: 70px;
        height: 3.5px;
        background: linear-gradient(90deg, var(--teal), var(--gold));
        margin: 0.75rem auto 0;
        border-radius: 4px;
    }
    .section-subtitle {
        color: var(--text-muted);
        font-size: 1.05rem;
        max-width: 820px;
        margin: 0 auto 3.5rem;
        line-height: 1.8;
    }
    .tentang-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.75rem;
    }
    .t-card {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        height: 270px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease;
        text-decoration: none;
        display: block;
    }
    .t-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    .t-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .t-card:hover img {
        transform: scale(1.08);
    }
    .t-card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(15, 23, 42, 0.85) 100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        padding: 1.75rem;
        transition: background 0.3s ease;
    }
    .t-card:hover .t-card-overlay {
        background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(18, 130, 121, 0.9) 100%);
    }
    .t-card-overlay h4 {
        color: white;
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.01em;
        text-shadow: 0 2px 4px rgba(0,0,0,0.4);
    }
    .t-card-overlay span {
        color: var(--gold);
        font-size: 0.82rem;
        font-weight: 500;
        margin-top: 0.35rem;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    .t-card:hover .t-card-overlay span {
        opacity: 1;
        transform: translateY(0);
    }

    /* Profil Section */
    .profil-section {
        background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
        padding: 6rem 5%;
        color: white;
        text-align: center;
        border-radius: 36px;
        margin: 0 3%;
        box-shadow: 0 20px 50px rgba(18, 130, 121, 0.25);
        position: relative;
        overflow: hidden;
    }
    .profil-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    .profil-section .section-title {
        color: white;
    }
    .profil-section .section-title::after {
        background: var(--gold);
    }
    .profil-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 3rem 2.5rem;
        max-width: 1100px;
        margin: 4rem auto 0;
    }
    .profil-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: transform 0.3s ease, background 0.3s ease;
    }
    .profil-item:hover {
        transform: translateY(-6px);
        background: rgba(255, 255, 255, 0.15);
    }
    .profil-item i {
        font-size: 2.5rem;
        margin-bottom: 1.25rem;
        color: var(--gold);
        background: rgba(255, 255, 255, 0.15);
        width: 76px;
        height: 76px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .profil-item h4 {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 0.6rem;
    }
    .profil-item p {
        font-size: 0.92rem;
        opacity: 0.92;
        line-height: 1.6;
    }
    .profil-bottom {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2.5rem;
        max-width: 780px;
        margin: 2.5rem auto 0;
    }

    /* Berita Section */
    .berita-section {
        padding: 6rem 5%;
        max-width: 1240px;
        margin: 0 auto;
        text-align: center;
    }
    .berita-grid {
        display: grid;
        grid-template-columns: 1fr 1.35fr 1fr;
        gap: 2rem;
        margin: 3.5rem auto 0;
        align-items: stretch;
    }
    .berita-card {
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        background: white;
        text-align: left;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        border: 1px solid #edf2f7;
    }
    .berita-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        border-color: var(--teal);
    }
    .berita-card.main-news {
        transform: scale(1.03);
        z-index: 2;
        box-shadow: 0 18px 45px rgba(0,0,0,0.12);
        border-color: rgba(27, 178, 166, 0.3);
    }
    .berita-card.main-news:hover {
        transform: scale(1.03) translateY(-8px);
    }
    .berita-img-wrapper {
        position: relative;
        overflow: hidden;
    }
    .berita-card img {
        width: 100%;
        height: 210px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .main-news img {
        height: 290px;
    }
    .berita-card:hover img {
        transform: scale(1.06);
    }
    .berita-content {
        padding: 1.75rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .main-news .berita-content {
        padding: 2.25rem;
    }
    .berita-meta {
        font-size: 0.84rem;
        color: var(--teal);
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .berita-title {
        font-weight: 700;
        font-size: 1.15rem;
        line-height: 1.4;
        margin-bottom: 0.85rem;
        color: var(--text-main);
        transition: color 0.2s;
    }
    .berita-card:hover .berita-title {
        color: var(--teal);
    }
    .main-news .berita-title {
        font-size: 1.45rem;
    }
    .berita-desc {
        font-size: 0.92rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex: 1;
    }
    .berita-read-more {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--teal);
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-top: auto;
    }
    .berita-read-more i {
        transition: transform 0.2s;
    }
    .berita-card:hover .berita-read-more i {
        transform: translateX(4px);
    }
    
    .btn-all-news {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.85rem 2.25rem;
        border-radius: 50px;
        background: transparent;
        color: var(--teal);
        border: 2px solid var(--teal);
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .btn-all-news:hover {
        background: var(--teal);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(27, 178, 166, 0.25);
    }

    @media (max-width: 992px) {
        .hero-text h2 { font-size: 2.75rem; }
        .tentang-cards { grid-template-columns: repeat(2, 1fr); }
        .profil-grid { grid-template-columns: repeat(2, 1fr); gap: 2rem; }
        .berita-grid { grid-template-columns: 1fr; }
        .berita-card.main-news { transform: none; }
        .berita-card.main-news:hover { transform: translateY(-8px); }
        .hero-content { flex-direction: column; align-items: flex-start; gap: 2rem; }
    }
    @media (max-width: 768px) {
        .tentang-cards { grid-template-columns: 1fr; }
        .profil-grid, .profil-bottom { grid-template-columns: 1fr; }
        .profil-section { margin: 0; border-radius: 0; }
    }
</style>

<!-- Hero Section / Slot (Diisi lewat Pengelola Widget) -->
<?php if (!empty($global['homepage_hero'])): ?>
    <section class="hero-custom-widget">
        <?= $global['homepage_hero'] ?>
    </section>
<?php endif; ?>

<!-- Tentang Section -->
<section class="tentang-section" id="tentang">
    <?php if (!empty($global['homepage_tentang'])): ?>
        <?= $global['homepage_tentang'] ?>
    <?php else: ?>
        <h2 class="section-title">Tentang Pesantren</h2>
        <p class="section-subtitle">
            Berdirinya Pondok Pesantren Darussalam Bogor merupakan perwujudan dari niat amal ibadah, rasa cinta, dan itibar terhadap Pendiri Pondok Modern Gontor K.H. Ahmad Sahal, K.H. Imam Zarkasyi, dan K.H. Zainuddin Fananie. Sejak didirikan pada tahun ajaran 1992-1993, Darussalam terus teguh menjaga nilai dan falsafah kepesantrenan.
        </p>
        
        <div class="tentang-cards">
            <a href="#profil" class="t-card">
                <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800" alt="Visi Misi">
                <div class="t-card-overlay">
                    <h4>Visi & Misi</h4>
                    <span>Pelajari Nilai Pokok &rarr;</span>
                </div>
            </a>
            <a href="#profil" class="t-card">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800" alt="Panca Jiwa">
                <div class="t-card-overlay">
                    <h4>Panca Jiwa</h4>
                    <span>Falsafah Hidup Santri &rarr;</span>
                </div>
            </a>
            <a href="#profil" class="t-card">
                <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&q=80&w=800" alt="Moto">
                <div class="t-card-overlay">
                    <h4>Moto Lembaga</h4>
                    <span>Prinsip Perjuangan &rarr;</span>
                </div>
            </a>
            <a href="#profil" class="t-card">
                <img src="https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&q=80&w=800" alt="Panca Jangka">
                <div class="t-card-overlay">
                    <h4>Panca Jangka</h4>
                    <span>Rencana Masa Depan &rarr;</span>
                </div>
            </a>
        </div>
    <?php endif; ?>
</section>

<!-- Profil Section -->
<section class="profil-section" id="profil">
    <?php if (!empty($global['homepage_profil'])): ?>
        <?= $global['homepage_profil'] ?>
    <?php else: ?>
        <h2 class="section-title">Profil Singkat Darussalam</h2>
        
        <div class="profil-grid">
            <div class="profil-item">
                <i class="fas fa-university"></i>
                <h4>Didirikan pada tahun 1992</h4>
                <p>Tiga dekade lebih mendedikasikan diri membina santri dengan pendidikan Islam berbasis ilmu dan akhlak mulia.</p>
            </div>
            <div class="profil-item">
                <i class="fas fa-users"></i>
                <h4>Lebih dari 700 santri aktif</h4>
                <p>Membina ratusan santri dari berbagai penjuru tanah air dalam lingkungan asrama yang asri dan kondusif.</p>
            </div>
            <div class="profil-item">
                <i class="fas fa-book-open"></i>
                <h4>Kurikulum Unggulan</h4>
                <p>Memadukan kurikulum keislaman, Bahasa Arab, Bahasa Inggris, dan sains teknologi terkini secara seimbang.</p>
            </div>
        </div>
        
        <div class="profil-bottom">
            <div class="profil-item">
                <i class="fas fa-laptop-house"></i>
                <h4>Sarana & Prasarana Lengkap</h4>
                <p>Dilengkapi masjid megah, asrama representatif, laboratorium komputer & bahasa, serta area olahraga terpadu.</p>
            </div>
            <div class="profil-item">
                <i class="fas fa-certificate"></i>
                <h4>Segudang Prestasi</h4>
                <p>Raihan prestasi membanggakan santri di tingkat regional maupun nasional di bidang akademik dan kepesantrenan.</p>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- Berita Section -->
<?php
$beritaMode = $options['homepage_berita_mode'] ?? 'latest';
$beritaPageId = $options['homepage_berita_page'] ?? '';
?>

<section class="berita-section" id="berita">
    <?php if (!empty($global['homepage_berita'])): ?>
        <!-- Menggunakan Area Widget Berita yang dikonfigurasi di Admin -->
        <h2 class="section-title">Kabar & Informasi Terkini</h2>
        <p class="section-subtitle">Ikuti perkembangan terbaru, kegiatan santri, dan pengumuman resmi dari lembaga.</p>
        <div class="berita-custom-widget-area">
            <?= $global['homepage_berita'] ?>
        </div>
    <?php elseif ($beritaMode === 'static' && !empty($beritaPageId)): ?>
        <?php
        $pageData = null;
        try {
            $pageData = \App\Core\Application::getInstance()->db->selectOne(
                "SELECT title, content FROM pages WHERE id = :id AND status = 'published'", 
                ['id' => $beritaPageId]
            );
        } catch (\Exception $e) {}
        ?>
        <?php if ($pageData): ?>
            <h2 class="section-title"><?= htmlspecialchars($pageData['title']) ?></h2>
            <div style="max-width: 900px; margin: 2rem auto; text-align: left; font-size: 1.05rem; line-height: 1.8; color: #475569;">
                <?= $pageData['content'] ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <h2 class="section-title">Kabar & Informasi Terkini</h2>
        <p class="section-subtitle">Ikuti perkembangan terbaru, kegiatan santri, dan pengumuman resmi dari lembaga.</p>
        
        <div class="berita-grid">
            <?php if (!empty($articlesList) && count($articlesList) > 0): ?>
                <?php 
                $displayArticles = array_slice($articlesList, 0, 3);
                foreach ($displayArticles as $index => $art): 
                    $isMain = ($index === 1 || count($displayArticles) === 1) ? 'main-news' : '';
                    
                    // Prioritaskan gambar featured media asli dari database
                    $thumb = !empty($art['media_path']) ? '/uploads/' . $art['media_path'] : null;
                    if (!$thumb) {
                        $fallbacks = [
                            'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&q=80&w=800',
                            'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=1200',
                            'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800'
                        ];
                        $thumb = $fallbacks[$index % 3];
                    }

                    // Format tanggal artikel asli
                    $dateRaw = !empty($art['published_at']) ? $art['published_at'] : ($art['created_at'] ?? 'now');
                    $artDate = date('d M Y', strtotime($dateRaw));

                    // Cuplikan artikel asli
                    $excerpt = !empty($art['excerpt']) 
                        ? $art['excerpt'] 
                        : (mb_substr(strip_tags($art['content'] ?? ''), 0, 120) . '...');
                    if (trim(strip_tags($excerpt)) === '...') {
                        $excerpt = 'Klik untuk membaca artikel warta selengkapnya.';
                    }
                ?>
                <a href="/artikel/<?= htmlspecialchars($art['slug']) ?>" class="berita-card <?= $isMain ?>" style="text-decoration: none; color: inherit;">
                    <div class="berita-img-wrapper">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($art['title']) ?>" loading="lazy">
                    </div>
                    <div class="berita-content">
                        <div class="berita-meta">
                            <i class="far fa-calendar-alt"></i> <?= $artDate ?>
                        </div>
                        <div class="berita-title"><?= htmlspecialchars($art['title']) ?></div>
                        <p class="berita-desc" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($excerpt) ?>
                        </p>
                        <div class="berita-read-more">
                            Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 4rem 1rem; background: var(--bg-light); border-radius: 20px;">
                    <i class="far fa-newspaper" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                    <p style="font-size: 1.1rem; font-weight: 500;">Belum ada artikel yang diterbitkan.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($articlesList) && count($articlesList) > 0): ?>
            <div style="margin-top: 3.5rem;">
                <a href="<?= $allNewsUrl ?>" class="btn-all-news">
                    Lihat Seluruh Berita <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
