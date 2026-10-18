<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Opsi Tema</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Kelola warna, tipografi, dan pengaturan tampilan tema aktif Anda.</p>
    </div>
</div>

<div class="tabs-container">
    <button type="button" class="tab-btn active" data-tab="umum" onclick="switchTab('umum')">Umum</button>
    <button type="button" class="tab-btn" data-tab="header" onclick="switchTab('header')">Header</button>
    <button type="button" class="tab-btn" data-tab="beranda" onclick="switchTab('beranda')">Beranda</button>
</div>

<style>
.setting-group {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid #e2e8f0;
    align-items: start;
}
.setting-group:last-child {
    border-bottom: none;
}
.setting-info label {
    font-weight: 600;
    color: var(--text-main, #0f172a);
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
}
.setting-info span {
    font-size: 0.85rem;
    color: var(--text-muted, #64748b);
    display: block;
    line-height: 1.5;
}
.setting-input input[type="text"],
.setting-input input[type="url"],
.setting-input input[type="email"],
.setting-input input[type="number"],
.setting-input select {
    width: 100%;
    max-width: 450px;
    padding: 0.6rem 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background-color: #fff;
    transition: all 0.2s;
    font-family: inherit;
    font-size: 0.9rem;
}
.setting-input input[type="color"] {
    width: 100%;
    max-width: 100px;
    height: 40px;
    padding: 2px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    cursor: pointer;
}
.setting-input input:focus, .setting-input select:focus {
    border-color: var(--brand, #0f766e);
    outline: none;
    box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
}
@media (max-width: 768px) {
    .setting-group {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
}
</style>

<form id="theme-options-form" class="admin-form" style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-top: 1rem;">
    <!-- TAB UMUM -->
    <div id="tab-umum" class="tab-content" style="padding: 0 2rem;">
        
        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-primary-color">Warna Utama (Primary Color)</label>
                <span>Warna utama (aksen) yang digunakan pada tombol, tautan, dan elemen antarmuka penting lainnya.</span>
            </div>
            <div class="setting-input">
                <input type="color" id="opt-primary-color">
            </div>
        </div>
        
        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-secondary-color">Warna Sekunder (Secondary Color)</label>
                <span>Warna kedua yang digunakan untuk latar belakang header, footer, atau aksen teks pendukung.</span>
            </div>
            <div class="setting-input">
                <input type="color" id="opt-secondary-color">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-font-heading">Font Heading</label>
                <span>Nama keluarga huruf (font family) yang digunakan untuk semua judul/heading (h1-h6).</span>
            </div>
            <div class="setting-input">
                <input type="text" id="opt-font-heading" placeholder="'Poppins', sans-serif">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-font-body">Font Body</label>
                <span>Nama keluarga huruf (font family) yang digunakan untuk teks paragraf dan konten pada umumnya.</span>
            </div>
            <div class="setting-input">
                <input type="text" id="opt-font-body" placeholder="'Inter', sans-serif">
            </div>
        </div>
        
    </div>

    <!-- TAB HEADER -->
    <div id="tab-header" class="tab-content" style="display: none; padding: 0 2rem;">
        
        <?php
        $app = \App\Core\Application::getInstance();
        $availableMenus = $app->menus()->listMenus();
        ?>

        <h3 style="margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 1rem; color: var(--brand, #0f766e); border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">Branding & Logo</h3>

        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-logo-display">Tampilan Logo</label>
                <span>Bagaimana Anda ingin menampilkan logo situs Anda di bagian header halaman.</span>
            </div>
            <div class="setting-input">
                <select id="opt-logo-display">
                    <option value="logo_text">Tampilkan Gambar Logo & Teks Nama Situs</option>
                    <option value="logo_only">Tampilkan Gambar Logo Saja</option>
                    <option value="text_only">Tampilkan Teks Nama Situs Saja</option>
                </select>
            </div>
        </div>
        
        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-logo-text-width">Maksimal Lebar Teks Logo</label>
                <span>Batasi lebar teks logo Anda agar dapat turun ke baris baru jika terlalu panjang. Masukkan angka dengan satuan px atau %.</span>
            </div>
            <div class="setting-input">
                <input type="text" id="opt-logo-text-width" placeholder="Contoh: 200px atau 100%">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-sticky-header">Sticky Header</label>
                <span>Aktifkan fitur ini agar header tetap menempel di bagian atas layar saat pengguna menggulir halaman ke bawah.</span>
            </div>
            <div class="setting-input">
                <select id="opt-sticky-header">
                    <option value="yes">Aktif (Tetap Menempel)</option>
                    <option value="no">Non-Aktif (Ikut Tergulir)</option>
                </select>
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-header-style">Gaya Latar Header</label>
                <span>Pilih gaya warna latar belakang header. "Otomatis" akan membuat header transparan di beranda (bila ada gambar/hero) dan solid di halaman yang berlatar putih.</span>
            </div>
            <div class="setting-input">
                <select id="opt-header-style">
                    <option value="auto">Otomatis (Cerdas)</option>
                    <option value="transparent">Selalu Transparan</option>
                    <option value="solid">Selalu Solid Gelap</option>
                </select>
            </div>
        </div>

        <h3 style="margin-top: 2rem; margin-bottom: 0.5rem; font-size: 1rem; color: var(--brand, #0f766e); border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">Navigasi</h3>

        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-menu-header">Menu Header (Utama)</label>
                <span>Pilih menu yang akan ditampilkan di bagian atas situs (Header). Anda dapat membuat menu baru di <strong>Manajer Menu</strong>.</span>
            </div>
            <div class="setting-input">
                <select id="opt-menu-header">
                    <option value="">-- Gunakan Bawaan Tema --</option>
                    <?php foreach ($availableMenus as $menu): ?>
                        <option value="<?= htmlspecialchars($menu['slug']) ?>"><?= htmlspecialchars($menu['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-menu-footer">Menu Footer (Bawah)</label>
                <span>Pilih menu yang akan ditampilkan di bagian bawah situs (Footer).</span>
            </div>
            <div class="setting-input">
                <select id="opt-menu-footer">
                    <option value="">-- Jangan Tampilkan Menu --</option>
                    <?php foreach ($availableMenus as $menu): ?>
                        <option value="<?= htmlspecialchars($menu['slug']) ?>"><?= htmlspecialchars($menu['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
    </div>

    <!-- TAB BERANDA -->
    <div id="tab-beranda" class="tab-content" style="display: none; padding: 0 2rem;">
        <h3 style="margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 1rem; color: var(--brand, #0f766e); border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">Pengaturan Hero (Bagian Atas)</h3>
        
        <div class="setting-group">
            <div class="setting-info">
                <label>Area Hero Beranda (Slider / Banner)</label>
                <span>Sesuai arsitektur tema, bagian atas beranda dikelola sepenuhnya melalui <strong><a href="/admin/panel/widgets" style="color: var(--brand, #0f766e); text-decoration: underline;">Pengelola Widget</a></strong> pada area <strong>Beranda - Hero Section</strong>. Jika area tersebut kosong, tidak ada banner yang ditampilkan.</span>
            </div>
            <div class="setting-input" style="display: flex; align-items: center;">
                <a href="/admin/panel/widgets" class="btn" style="padding: 0.6rem 1.25rem; background: var(--brand, #0f766e); color: #fff; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-th-large"></i> Buka Pengelola Widget
                </a>
            </div>
        </div>

        <h3 style="margin-top: 2rem; margin-bottom: 0.5rem; font-size: 1rem; color: var(--brand, #0f766e); border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">Pengaturan Section Berita</h3>
        
        <?php
        $availablePages = $app->db->select("SELECT id, title FROM pages WHERE status = 'published' ORDER BY title ASC");
        ?>

        <div class="setting-group">
            <div class="setting-info">
                <label for="opt-homepage-berita-mode">Sumber Konten Berita</label>
                <span>Pilih apa yang ingin Anda tampilkan pada section Berita di beranda.</span>
            </div>
            <div class="setting-input">
                <select id="opt-homepage-berita-mode">
                    <option value="latest">Tampilkan List Artikel Terbaru (Berita)</option>
                    <option value="static">Tampilkan Halaman Statis Terpilih</option>
                </select>
            </div>
        </div>

        <div class="setting-group" id="wrap-homepage-berita-page" style="display: none;">
            <div class="setting-info">
                <label for="opt-homepage-berita-page">Pilih Halaman Statis</label>
                <span>Halaman ini akan ditampilkan menggantikan daftar berita terbaru.</span>
            </div>
            <div class="setting-input">
                <select id="opt-homepage-berita-page">
                    <option value="">-- Pilih Halaman --</option>
                    <?php foreach ($availablePages as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div style="padding: 1.5rem 2rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
        <button type="submit" class="btn btn-primary" style="padding: 0.6rem 2rem; font-size: 0.95rem;">Simpan Perubahan</button>
    </div>
</form>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).style.display = 'block';
    document.querySelector('.tab-btn[data-tab="' + tabId + '"]').classList.add('active');
}

// Logic for Berita Option Toggle
document.addEventListener('DOMContentLoaded', function() {
    const beritaMode = document.getElementById('opt-homepage-berita-mode');
    const wrapBeritaPage = document.getElementById('wrap-homepage-berita-page');
    
    if (beritaMode && wrapBeritaPage) {
        // Toggle on change
        beritaMode.addEventListener('change', function() {
            wrapBeritaPage.style.display = this.value === 'static' ? 'block' : 'none';
        });
        
        // Setup initial state via interval or timeout (in case generic loader sets value later)
        setTimeout(() => {
            wrapBeritaPage.style.display = beritaMode.value === 'static' ? 'block' : 'none';
        }, 500);
    }
});
</script>
