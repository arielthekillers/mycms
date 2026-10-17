<style>
    /* Hero Section */
    .hero-section {
        position: relative;
        height: 100vh;
        min-height: 600px;
        background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&q=80&w=2070') center/cover no-repeat;
        display: flex;
        align-items: flex-end;
        padding-bottom: 5rem;
        color: white;
    }
    .hero-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 5%;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .hero-text h2 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        line-height: 1.2;
        max-width: 600px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    .hero-text p {
        font-size: 1.1rem;
        max-width: 500px;
        opacity: 0.9;
        margin-bottom: 2rem;
    }
    .hero-nav {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: color 0.3s;
    }
    .hero-nav:hover {
        color: var(--gold);
    }
    
    /* Tentang Section */
    .tentang-section {
        padding: 5rem 5%;
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
    }
    .section-title {
        color: var(--gold);
        font-size: 2rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
        position: relative;
        display: inline-block;
    }
    .section-title::after {
        content: '';
        display: block;
        width: 60px;
        height: 3px;
        background-color: var(--gold);
        margin: 0.5rem auto 0;
        border-radius: 2px;
    }
    .tentang-desc {
        max-width: 800px;
        margin: 0 auto 3rem;
        font-size: 1.05rem;
        color: #555;
    }
    .tentang-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }
    .t-card {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        height: 250px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    .t-card:hover {
        transform: translateY(-10px);
    }
    .t-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .t-card:hover img {
        transform: scale(1.1);
    }
    .t-card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 1.5rem;
    }
    .t-card-overlay h4 {
        color: white;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }

    /* Profil Section */
    .profil-section {
        background-color: var(--teal);
        padding: 5rem 5%;
        color: white;
        text-align: center;
        border-radius: 40px;
        margin: 0 2%;
    }
    .profil-section .section-title {
        color: white;
    }
    .profil-section .section-title::after {
        background-color: white;
    }
    .profil-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 3rem 2rem;
        max-width: 1000px;
        margin: 3rem auto 0;
    }
    .profil-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .profil-item i {
        font-size: 3rem;
        margin-bottom: 1rem;
        background: rgba(255,255,255,0.2);
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .profil-item h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .profil-item p {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .profil-bottom {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        max-width: 700px;
        margin: 3rem auto 0;
    }

    /* Berita Section */
    .berita-section {
        padding: 5rem 5%;
        text-align: center;
    }
    .berita-grid {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        gap: 1.5rem;
        max-width: 1200px;
        margin: 3rem auto 0;
        align-items: center;
    }
    .berita-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        background: white;
        text-align: left;
        transition: transform 0.3s;
    }
    .berita-card:hover {
        transform: translateY(-5px);
    }
    .berita-card.main-news {
        transform: scale(1.05);
        z-index: 2;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    .berita-card.main-news:hover {
        transform: scale(1.05) translateY(-5px);
    }
    .berita-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .main-news img {
        height: 300px;
    }
    .berita-content {
        padding: 1.5rem;
    }
    .main-news .berita-content {
        padding: 2rem;
        text-align: center;
    }
    .berita-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: var(--text-main);
    }
    .main-news .berita-title {
        font-size: 1.5rem;
    }
    .berita-desc {
        font-size: 0.9rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }
    .berita-date {
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--teal);
    }
    
    @media (max-width: 992px) {
        .tentang-cards { grid-template-columns: repeat(2, 1fr); }
        .profil-grid, .profil-bottom { grid-template-columns: repeat(2, 1fr); }
        .berita-grid { grid-template-columns: 1fr; }
        .berita-card.main-news { transform: none; }
        .hero-text h2 { font-size: 2.5rem; }
    }
    @media (max-width: 768px) {
        .tentang-cards { grid-template-columns: 1fr; }
        .profil-grid, .profil-bottom { grid-template-columns: 1fr; }
    }
</style>

<!-- Hero Section -->
<?php if (!empty($options['homepage_slider']) && function_exists('render_slider')): ?>
    <style>
    .splide-plugin { height: 100vh; min-height: 600px; }
    .splide__track, .splide__list, .splide__slide { height: 100%; }
    .splide__arrow { background: rgba(255,255,255,0.2); }
    .splide__arrow svg { fill: #fff; }
    .splide__pagination__page { background: rgba(255,255,255,0.5); }
    .splide__pagination__page.is-active { background: #fff; transform: scale(1.4); }
    </style>
    <?= render_slider($options['homepage_slider']) ?>
<?php endif; ?>

<!-- Tentang Section -->
<?php if (!empty($global['homepage_tentang'])): ?>
    <section class="tentang-section">
        <?= $global['homepage_tentang'] ?>
    </section>
<?php else: ?>
<section class="tentang-section">
    <h2 class="section-title">Tentang Pesantren</h2>
    <p class="tentang-desc">Berdirinya Pondok Pesantren Darussalam Bogor merupakan perwujudan dari niat amal ibadah, rasa cinta, dan itibar terhadap Pendiri Pondok Modern Gontor K.H. Ahmad Sahal, K.H. Imam Zarkasyi, dan K.H. Zainuddin Fananie, yang bercita-cita mendirikan seribu Gontor di Indonesia. Alhamdulillah dengan semangat cita-cita tersebut, sejak didirikan pada tahun ajaran 1992-1993 sampai sekarang, Pondok Pesantren Darussalam Bogor berjalan sesuai dengan nilai-nilai dan falsafah kehidupan Pondok Pesantren.</p>
    
    <div class="tentang-cards">
        <div class="t-card">
            <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800" alt="Visi Misi">
            <div class="t-card-overlay">
                <h4>Visi & Misi</h4>
            </div>
        </div>
        <div class="t-card">
            <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800" alt="Panca Jiwa">
            <div class="t-card-overlay">
                <h4>Panca Jiwa</h4>
            </div>
        </div>
        <div class="t-card">
            <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800" alt="Moto">
            <div class="t-card-overlay">
                <h4>Moto</h4>
            </div>
        </div>
        <div class="t-card">
            <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800" alt="Panca Jangka">
            <div class="t-card-overlay">
                <h4>Panca Jangka</h4>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Profil Section -->
<?php if (!empty($global['homepage_profil'])): ?>
    <?= $global['homepage_profil'] ?>
<?php else: ?>
<section class="profil-section">
    <h2 class="section-title" style="color:white;">Profil Singkat Darussalam</h2>
    
    <div class="profil-grid">
        <div class="profil-item">
            <i class="fas fa-university"></i>
            <h4>Didirikan pada tahun 1992</h4>
            <p>Berdiri sejak 1992, Darussalam Bogor membina santri dengan pendidikan Islam berbasis ilmu dan akhlak.</p>
        </div>
        <div class="profil-item">
            <i class="fas fa-users"></i>
            <h4>Lebih dari 700 santri aktif</h4>
            <p>Saat ini, Pondok Pesantren Darussalam Bogor membina lebih dari 700 santri dalam lingkungan pendidikan berasrama.</p>
        </div>
        <div class="profil-item">
            <i class="fas fa-book-open"></i>
            <h4>Kurikulum unggulan</h4>
            <p>Kurikulum satuan pendidikan mu'allimin (SPM) terdiri dari kelompok bidang studi keislaman, B. Arab, B. Inggris, dan umum.</p>
        </div>
    </div>
    
    <div class="profil-bottom">
        <div class="profil-item">
            <i class="fas fa-laptop-house"></i>
            <h4>Sarana & Prasarana Lengkap</h4>
            <p>Darussalam Bogor didukung sarana dan prasarana lengkap untuk menunjang kegiatan belajar, ibadah, dan kehidupan santri.</p>
        </div>
        <div class="profil-item">
            <i class="fas fa-certificate"></i>
            <h4>Segudang Prestasi</h4>
            <p>Prestasi santri di berbagai bidang akademik dan nonakademik.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Berita Section -->
<?php
$beritaMode = $options['homepage_berita_mode'] ?? 'latest';
$beritaPageId = $options['homepage_berita_page'] ?? '';
?>

<?php if ($beritaMode === 'static' && !empty($beritaPageId)): ?>
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
        <section class="berita-section">
            <h2 class="section-title"><?= htmlspecialchars($pageData['title']) ?></h2>
            <div style="max-width: 1200px; margin: 0 auto; padding: 0 5%; font-size: 1.05rem; line-height: 1.8;">
                <?= $pageData['content'] ?>
            </div>
        </section>
    <?php endif; ?>
<?php else: ?>
    <!-- Mode List Artikel Terbaru -->
    <section class="berita-section">
        <h2 class="section-title">Berita Terkini</h2>
        
        <div class="berita-grid">
            <?php if (!empty($recentArticles) && count($recentArticles) > 0): ?>
                <?php 
                // Kita ambil maksimal 3 artikel (sesuai grid desain)
                $articles = array_slice($recentArticles, 0, 3);
                foreach ($articles as $index => $article): 
                    // Artikel kedua (index 1) kita buat besar di tengah
                    $isMain = ($index === 1 || count($articles) === 1) ? 'main-news' : '';
                    
                    // Fallback gambar jika tidak ada (opsional, karena query saat ini tidak mengambil thumbnail)
                    $thumbnail = 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&q=80&w=' . ($isMain ? '1200' : '800');
                    
                    // Ambil deskripsi singkat jika main news
                    $desc = 'Cuplikan artikel tidak tersedia.';
                ?>
                <a href="/article/<?= htmlspecialchars($article['slug']) ?>" class="berita-card <?= $isMain ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?= $thumbnail ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                    <div class="berita-content">
                        <div class="berita-title"><?= htmlspecialchars($article['title']) ?></div>
                        <?php if ($isMain): ?>
                            <div class="berita-desc" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                Klik untuk membaca artikel lengkap.
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: #666; padding: 2rem 0;">Belum ada artikel yang diterbitkan.</div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
