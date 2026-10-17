<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Pengaturan Sistem</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Kelola preferensi situs, zona waktu, format bacaan, dan konfigurasi inti CMS.</p>
    </div>
</div>

<div class="tabs-container">
    <button type="button" class="tab-btn active" data-tab="general" onclick="switchTab('general')">Umum</button>
    <button type="button" class="tab-btn" data-tab="content" onclick="switchTab('content')">Konten</button>
    <button type="button" class="tab-btn" data-tab="integration" onclick="switchTab('integration')">Integrasi</button>
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
.setting-input input:focus, .setting-input select:focus {
    border-color: var(--brand, #0f766e);
    outline: none;
    box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
}
.setting-input input[type="file"] {
    font-size: 0.85rem;
    padding: 0.4rem;
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    background: #f8fafc;
    width: 100%;
    max-width: 450px;
    cursor: pointer;
}
.media-preview-box {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 0.5rem;
}
@media (max-width: 768px) {
    .setting-group {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
}
</style>

<form id="settings-form" class="admin-form" style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-top: 1rem;">
    <!-- TAB UMUM -->
    <div id="tab-general" class="tab-content">
        
        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-site-name">Nama Situs</label>
                <span>Judul utama yang akan muncul di header atau title bar browser pengunjung.</span>
            </div>
            <div class="setting-input">
                <input type="text" id="setting-site-name" name="site_name" required>
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-site-description">Deskripsi Situs</label>
                <span>Slogan atau penjelasan singkat tentang situs. Sangat berguna untuk SEO.</span>
            </div>
            <div class="setting-input">
                <input type="text" id="setting-site-description" name="site_description">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-site-address">Alamat Situs (URL)</label>
                <span>Contoh: <code>https://pesantrenku.com</code>. Kosongkan untuk mengikuti konfigurasi sistem default.</span>
            </div>
            <div class="setting-input">
                <input type="url" id="setting-site-address" name="site_address" placeholder="https://...">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-site-logo-file">Logo Situs</label>
                <span>Gambar logo utama situs Anda. Akan teroptimasi secara otomatis ke format ringan (WebP).</span>
            </div>
            <div class="setting-input">
                <div class="media-preview-box">
                    <img id="logo-preview" src="" style="max-height: 50px; display: none; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px; background: #fff;">
                </div>
                <input type="file" id="setting-site-logo-file" accept="image/*">
                <input type="hidden" id="setting-site-logo" name="site_logo">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-site-favicon-file">Favicon (Ikon Tab)</label>
                <span>Gambar ikon kecil yang muncul di tab browser. Disarankan format PNG atau ICO dengan rasio persegi (1:1).</span>
            </div>
            <div class="setting-input">
                <div class="media-preview-box">
                    <img id="favicon-preview" src="" style="max-height: 32px; display: none; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px; background: #fff;">
                </div>
                <input type="file" id="setting-site-favicon-file" accept="image/png, image/x-icon, image/jpeg, image/webp">
                <input type="hidden" id="setting-site-favicon" name="site_favicon">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-contact-email">Email Kontak</label>
                <span>Email utama untuk menerima notifikasi sistem atau pesan dari pengunjung.</span>
            </div>
            <div class="setting-input">
                <input type="email" id="setting-contact-email" name="contact_email" placeholder="admin@domain.com">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-timezone">Zona Waktu (Timezone)</label>
                <span>Pilih zona waktu lokal Anda untuk menyesuaikan jam publikasi artikel.</span>
            </div>
            <div class="setting-input">
                <select id="setting-timezone" name="timezone" style="max-width: 250px;">
                    <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                    <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                    <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                    <option value="UTC">UTC (Universal Time)</option>
                </select>
            </div>
        </div>

    </div>

    <!-- TAB KONTEN -->
    <div id="tab-content" class="tab-content" style="display: none;">
        
        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-posts-per-page">Batas Artikel per Halaman</label>
                <span>Jumlah artikel maksimum yang ditampilkan dalam satu halaman daftar artikel atau kategori.</span>
            </div>
            <div class="setting-input">
                <input type="number" id="setting-posts-per-page" name="posts_per_page" min="1" max="100" value="10" style="max-width: 150px;">
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-date-format">Format Tanggal</label>
                <span>Tampilan tanggal pada artikel dan halaman depan (frontend).</span>
            </div>
            <div class="setting-input">
                <select id="setting-date-format" name="date_format" style="max-width: 300px;">
                    <option value="d M Y">25 Sep 2026 (d M Y)</option>
                    <option value="d/m/Y">25/09/2026 (d/m/Y)</option>
                    <option value="Y-m-d">2026-09-25 (Y-m-d)</option>
                    <option value="l, d F Y">Jumat, 25 September 2026 (l, d F Y)</option>
                </select>
            </div>
        </div>

        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-time-format">Format Waktu</label>
                <span>Tampilan waktu publikasi pada artikel.</span>
            </div>
            <div class="setting-input">
                <select id="setting-time-format" name="time_format" style="max-width: 300px;">
                    <option value="H:i">08:30 (H:i - 24 Jam)</option>
                    <option value="h:i A">08:30 AM (h:i A - 12 Jam)</option>
                </select>
            </div>
        </div>

    </div>

    <!-- TAB INTEGRASI -->
    <div id="tab-integration" class="tab-content" style="display: none;">
        
        <div class="setting-group">
            <div class="setting-info">
                <label for="setting-ga-id">Google Analytics 4 (GA4)</label>
                <span>Masukkan <strong>Measurement ID</strong> dari Google Analytics Anda (contoh: <code>G-XXXXXXXXXX</code>) untuk melacak statistik pengunjung situs secara otomatis.</span>
            </div>
            <div class="setting-input">
                <div style="display: flex; max-width: 450px; align-items: stretch;">
                    <span style="display: flex; align-items: center; justify-content: center; padding: 0 1rem; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0; color: #64748b; font-weight: 600;">G-</span>
                    <input type="text" id="setting-ga-id" name="ga_measurement_id" placeholder="XXXXXXXXXX" style="border-top-left-radius: 0; border-bottom-left-radius: 0; border-left: 1px solid #cbd5e1; margin: 0; flex: 1;">
                </div>
                <div style="margin-top: 0.75rem; font-size: 0.85rem; color: #64748b; display: flex; gap: 0.5rem; align-items: flex-start; background: #f0fdf4; padding: 0.75rem; border-radius: 6px; border: 1px solid #bbf7d0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" style="margin-top: 2px; flex-shrink: 0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span>Jika diisi, script Google Analytics akan otomatis disisipkan ke semua halaman publik secara *asynchronous* (tidak membebani loading website).</span>
                </div>
            </div>
        </div>

    </div>

    <div style="padding: 1.5rem 0; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
        <button type="submit" class="btn btn-primary" style="padding: 0.6rem 2rem; font-size: 0.95rem;">Simpan Pengaturan</button>
    </div>
</form>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).style.display = 'block';
    document.querySelector('.tab-btn[data-tab="' + tabId + '"]').classList.add('active');
}
</script>
