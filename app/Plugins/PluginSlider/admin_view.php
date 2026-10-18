<div class="toolbar-modern" style="margin-bottom: 2rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em;">Manajer Slider & Banner</h2>
            <span id="slider-count-badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;">0 Slider</span>
        </div>
        <p style="margin: 0.35rem 0 0 0; color: #64748b; font-size: 0.9rem;">Kelola slideshow promosi, efek animasi sinematik Splide, dan konfigurasi auto-slide situs Anda.</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="showSliderForm()" style="height: 42px; padding: 0 1.4rem; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(15,118,110,0.25);">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
            <span>Buat Slider Baru</span>
        </button>
    </div>
</div>

<style>
/* Modern Slider Grid */
.slider-showcase-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 1.75rem;
}
@media (max-width: 640px) {
    .slider-showcase-grid {
        grid-template-columns: 1fr;
    }
}

.slider-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
}
.slider-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.09);
    border-color: #cbd5e1;
}

.slider-card-cover {
    position: relative;
    height: 180px;
    background: #0f172a;
    background-size: cover;
    background-position: center;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 1.25rem;
}
.slider-card-cover::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.3) 0%, rgba(15, 23, 42, 0.85) 100%);
    z-index: 1;
}
.slider-card-cover > * {
    position: relative;
    z-index: 2;
}

.slider-card-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.badge-glass {
    backdrop-filter: blur(8px);
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-glass.active-auto {
    background: rgba(16, 185, 129, 0.3);
    border-color: rgba(52, 211, 153, 0.5);
    color: #a7f3d0;
}

.slider-card-body {
    padding: 1.25rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.shortcode-container {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}
.shortcode-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.82rem;
    color: #0f766e;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.btn-copy-code {
    background: transparent;
    border: none;
    color: #64748b;
    cursor: pointer;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s;
    font-weight: 500;
}
.btn-copy-code:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.btn-copy-code.copied {
    background: #ecfdf5;
    color: #059669;
}

.slides-strip {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 4px;
}
.slide-strip-thumb {
    width: 52px;
    height: 38px;
    border-radius: 6px;
    background: #e2e8f0;
    object-fit: cover;
    border: 1px solid #cbd5e1;
    flex-shrink: 0;
}
.slide-strip-placeholder {
    width: 52px;
    height: 38px;
    border-radius: 6px;
    background: #f1f5f9;
    border: 1px dashed #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 0.7rem;
    font-weight: 600;
    flex-shrink: 0;
}

.slider-card-footer {
    padding: 0.9rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

/* Modal Enhancements */
.admin-modal-lg {
    width: 960px;
    max-width: 96vw;
    border-radius: 18px;
    padding: 0;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
    background: #ffffff;
}

.modal-header-modern {
    padding: 1.25rem 1.75rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}

.modal-nav-tabs {
    display: flex;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 0 1.75rem;
}
.modal-nav-tab {
    padding: 0.9rem 1.25rem;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.modal-nav-tab.active {
    color: var(--admin-primary, #0f766e);
    border-bottom-color: var(--admin-primary, #0f766e);
    background: #ffffff;
}

.modal-body-scrollable {
    padding: 1.75rem;
    overflow-y: auto;
    flex: 1;
}

.modal-footer-modern {
    padding: 1.25rem 1.75rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Slide Editor Card */
.slide-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    transition: border-color 0.2s;
    position: relative;
}
.slide-card-item:hover {
    border-color: #cbd5e1;
}
.slide-card-item:focus-within {
    border-color: var(--admin-primary, #0f766e);
}

.slide-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
    border-bottom: 1px dashed #e2e8f0;
}

.slide-image-preview-box {
    width: 140px;
    height: 95px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #f1f5f9;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.slide-image-preview-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.slide-image-preview-box .no-img-text {
    font-size: 0.72rem;
    color: #94a3b8;
    text-align: center;
    padding: 0.5rem;
    font-weight: 500;
}

/* Big Save Button */
.btn-save-master {
    height: 46px;
    padding: 0 2.2rem;
    font-size: 0.95rem;
    font-weight: 700;
    border-radius: 10px;
    background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
    color: #ffffff;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    box-shadow: 0 4px 15px rgba(15, 118, 110, 0.3);
    transition: all 0.2s;
}
.btn-save-master:hover:not(:disabled) {
    background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(15, 118, 110, 0.4);
}
.btn-save-master:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* Spinner Animation */
@keyframes spin {
    to { transform: rotate(360deg); }
}
.spinner-icon {
    animation: spin 0.8s linear infinite;
}
</style>

<!-- Slider Grid List Container -->
<div id="sliders-list" class="slider-showcase-grid">
    <!-- Populated by renderSliderList() -->
</div>

<!-- ======================================================== -->
<!-- MODAL ADD / EDIT SLIDER (ULTRA-MODERN)                     -->
<!-- ======================================================== -->
<div id="slider-modal" class="admin-modal-overlay">
    <div class="admin-modal admin-modal-lg">
        <!-- Header -->
        <div class="modal-header-modern">
            <div>
                <h3 id="modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;">Edit Slider</h3>
                <p id="modal-subtitle" style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">Atur slide presentasi, gambar latar, teks aksi, dan transisi.</p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal()" style="border-radius: 50%; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #64748b;">
                ✕
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="modal-nav-tabs">
            <button type="button" class="modal-nav-tab active" data-tab="tab-slides" onclick="switchModalTab('tab-slides')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Konten Slide</span>
            </button>
            <button type="button" class="modal-nav-tab" data-tab="tab-effects" onclick="switchModalTab('tab-effects')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                <span>Efek & Animasi Auto Slide</span>
            </button>
        </div>

        <!-- Modal Body Content -->
        <div class="modal-body-scrollable">
            <!-- TAB 1: KONTEN SLIDE -->
            <div id="tab-slides" class="tab-pane">
                <!-- Identitas Slider Row -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div>
                        <label style="display:block; font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #1e293b;">ID Unik (Slug / Shortcode) *</label>
                        <div style="position: relative;">
                            <input type="text" id="slider-id" class="admin-input" placeholder="contoh: promo-beranda" style="font-family: monospace; font-weight: 600; padding-left: 2.2rem;">
                            <span style="position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-family: monospace; font-weight: bold;">#</span>
                        </div>
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.35rem;">Digunakan di shortcode: <code>[slider id="..."]</code></small>
                    </div>
                    <div>
                        <label style="display:block; font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #1e293b;">Nama / Judul Slider *</label>
                        <input type="text" id="slider-name" class="admin-input" placeholder="contoh: Banner Beranda Utama">
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.35rem;">Nama referensi untuk memudahkan Anda di admin.</small>
                    </div>
                    <div>
                        <label style="display:block; font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #1e293b;">Mode Tampilan Default</label>
                        <select id="slider-layout" class="admin-input" style="font-size: 0.92rem; padding: 0.6rem 0.75rem;">
                            <option value="hero">Layar Penuh (Hero 100vh) — Banner Beranda</option>
                            <option value="standard">Standar Boxed (~480px, Berbayang) — Seksi Halaman</option>
                            <option value="article">Kompak / Artikel (~380px) — Dalam Berita & Konten</option>
                        </select>
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.35rem;">Ukuran bawaan saat dipasang tanpa opsi khusus.</small>
                    </div>
                    <div>
                        <label style="display:block; font-weight: 700; margin-bottom: 0.4rem; font-size: 0.85rem; color: #1e293b;">Tinggi Kustom (Opsional)</label>
                        <input type="text" id="slider-height" class="admin-input" placeholder="contoh: 500px atau 65vh">
                        <small style="color: #64748b; font-size: 0.78rem; display: block; margin-top: 0.35rem;">Kosongkan untuk memakai tinggi default mode tampilan.</small>
                    </div>
                </div>

                <!-- Slide Cards Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a;">Daftar Slide</h4>
                        <span id="slides-count-pill" style="background: #e2e8f0; color: #475569; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 50px;">0 Slide</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-ghost" onclick="addSlide()" style="color: var(--admin-primary); font-weight: 600; border: 1px dashed var(--admin-primary); border-radius: 6px; padding: 0.4rem 0.9rem;">
                        + Tambah Slide Baru
                    </button>
                </div>

                <!-- Slides Container -->
                <div id="slides-container">
                    <!-- Dynamic Slide Cards Injected Here -->
                </div>

                <!-- Big Add Slide Button -->
                <button type="button" onclick="addSlide()" style="width: 100%; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 1.1rem; background: #f8fafc; color: #0f766e; font-weight: 700; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s; margin-top: 0.5rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Tambah Slide Berikutnya</span>
                </button>
            </div>

            <!-- TAB 2: EFEK & ANIMASI -->
            <div id="tab-effects" class="tab-pane" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                    <!-- Auto Slide Toggle Card -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <strong style="display: block; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.25rem;">Auto Slide (Autoplay)</strong>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; line-height: 1.4;">Slide otomatis berpindah ke gambar berikutnya tanpa harus diklik.</p>
                            </div>
                            <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                                <input type="checkbox" id="slider-autoplay" checked style="opacity: 0; width: 0; height: 0;">
                                <span class="custom-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Pause on Hover Toggle Card -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <strong style="display: block; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.25rem;">Jeda saat Disorot Mouse</strong>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; line-height: 1.4;">Menghentikan putaran otomatis saat kursor pengunjung berada di atas slider.</p>
                            </div>
                            <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                                <input type="checkbox" id="slider-pause-on-hover" style="opacity: 0; width: 0; height: 0;">
                                <span class="custom-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Efek Transisi Antar Slide Card -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <span style="font-size: 1.2rem;">🎬</span>
                        <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a;">Efek Transisi Antar Slide</h4>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: #1e293b;">Pilihan Gaya Transisi</label>
                            <select id="slider-effect" class="admin-input" style="font-size: 0.95rem; padding: 0.65rem 0.8rem;">
                                <option value="fade">Fade — Memudar Halus & Elegan</option>
                                <option value="loop">Slide Loop — Geser Horizontal Kontinu</option>
                                <option value="kenburns">Ken Burns — Slow Zoom Sinematik</option>
                                <option value="ttb">Geser Vertikal — Dari Atas ke Bawah</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: #1e293b;">Kecepatan Transisi Pergantian</label>
                            <select id="slider-speed" class="admin-input" style="font-size: 0.95rem; padding: 0.65rem 0.8rem;">
                                <option value="500">Cepat (0.5 detik)</option>
                                <option value="800" selected>Sedang & Mulus (0.8 detik)</option>
                                <option value="1200">Lambat Sinematik (1.2 detik)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Animasi Konten Teks & Durasi -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <span style="font-size: 1.2rem;">✨</span>
                        <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a;">Animasi Teks Judul, Deskripsi & Tombol</h4>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: #1e293b;">Animasi Muncul Teks</label>
                            <select id="slider-content-anim" class="admin-input">
                                <option value="fade-up">Fade Up (Melayang dari Bawah)</option>
                                <option value="fade-down">Fade Down (Turun dari Atas)</option>
                                <option value="slide-left">Slide Left (Meluncur dari Kiri)</option>
                                <option value="slide-right">Slide Right (Meluncur dari Kanan)</option>
                                <option value="zoom-in">Zoom In (Membesar Lembut)</option>
                                <option value="zoom-out">Zoom Out (Mengecil Lembut)</option>
                                <option value="fade-in">Fade In (Memudar di Tempat)</option>
                                <option value="none">Statis (Tanpa Animasi)</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: #1e293b;">Durasi Per Slide</label>
                            <select id="slider-interval" class="admin-input">
                                <option value="3">3 Detik (Cepat)</option>
                                <option value="4">4 Detik</option>
                                <option value="5" selected>5 Detik (Rekomendasi)</option>
                                <option value="7">7 Detik</option>
                                <option value="10">10 Detik (Santai)</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: #1e293b;">Kurva Gerak (Easing)</label>
                            <select id="slider-easing" class="admin-input">
                                <option value="ease-out" selected>Lembut di Akhir (Cubic)</option>
                                <option value="ease-in-out">Alami (Ease-in-out)</option>
                                <option value="linear">Konstan (Linear)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions with BIG SAVE BUTTON & SPINNER -->
        <div class="modal-footer-modern">
            <button type="button" class="btn btn-ghost" onclick="closeModal()" style="font-weight: 600; color: #64748b; padding: 0.6rem 1.4rem;">
                Batal
            </button>
            <button type="button" id="btn-save-slider" class="btn-save-master" onclick="saveSlider()">
                <svg id="save-btn-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"></path></svg>
                <svg id="save-btn-spinner" class="spinner-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-linecap="round"></circle></svg>
                <span id="save-btn-text">Simpan Slider</span>
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL MEDIA PICKER POPUP (HIGHEST Z-INDEX)               -->
<!-- ======================================================== -->
<div id="slider-media-modal" class="admin-modal-overlay" style="z-index: 99999;" onclick="if(event.target === this) closeMediaPicker()">
    <div class="admin-modal" style="width: 820px; max-width: 95vw; max-height: 85vh; display: flex; flex-direction: column; padding: 0; border-radius: 16px; overflow: hidden; background: #fff; box-shadow: 0 25px 70px rgba(15, 23, 42, 0.4);">
        <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #ffffff;">
            <div>
                <h4 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a;">Pilih Gambar dari Media</h4>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.8rem; color: #64748b;">Klik salah satu gambar untuk menjadikannya latar slide, atau unggah gambar baru.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <label class="btn btn-sm btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 0.45rem 1rem; font-weight: 600; border-radius: 6px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span>+ Unggah Baru</span>
                    <input type="file" id="media-picker-upload-input" accept="image/*" style="display: none;" onchange="uploadDirectToSlide(this)">
                </label>
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeMediaPicker()" style="border-radius: 50%; width: 32px; height: 32px; padding: 0; font-size: 1.2rem; color: #64748b;">✕</button>
            </div>
        </div>

        <div id="media-picker-grid" style="padding: 1.5rem; overflow-y: auto; flex: 1; display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem;">
            <!-- Loaded via AJAX -->
        </div>

        <div style="padding: 1rem 1.75rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeMediaPicker()" style="font-weight: 600; color: #64748b;">Tutup</button>
        </div>
    </div>
</div>

<!-- Custom Toggle Switch CSS -->
<style>
.custom-toggle-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #cbd5e1;
    transition: .25s;
    border-radius: 24px;
}
.custom-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .25s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
input:checked + .custom-toggle-slider {
    background-color: var(--admin-primary, #0f766e);
}
input:checked + .custom-toggle-slider:before {
    transform: translateX(20px);
}
</style>

<!-- ======================================================== -->
<!-- TEMPLATE FOR SINGLE SLIDE CARD                           -->
<!-- ======================================================== -->
<template id="slide-template">
    <div class="slide-card-item">
        <!-- Slide Header -->
        <div class="slide-card-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="slide-badge-num" style="background: #0f766e; color: #fff; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;">
                    Slide #<span class="index">1</span>
                </span>
                <span class="slide-type-label" style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Hero Banner</span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <button type="button" class="btn btn-ghost btn-sm btn-move-up" title="Pindah ke Atas" onclick="moveSlideUp(this)" style="padding: 4px 8px; color: #64748b;">
                    ▲
                </button>
                <button type="button" class="btn btn-ghost btn-sm btn-move-down" title="Pindah ke Bawah" onclick="moveSlideDown(this)" style="padding: 4px 8px; color: #64748b;">
                    ▼
                </button>
                <button type="button" class="btn btn-ghost btn-sm" title="Hapus Slide" onclick="removeSlide(this)" style="padding: 4px 8px; color: #dc2626; margin-left: 0.5rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>

        <!-- Main Slide Form Layout: Left Thumbnail Box, Right Fields -->
        <div style="display: flex; gap: 1.25rem; align-items: flex-start; flex-wrap: wrap;">
            <!-- Left: Visual Thumbnail Preview Box & Media Button -->
            <div>
                <div class="slide-image-preview-box">
                    <img class="img-preview" src="" alt="" style="display: none;">
                    <div class="no-img-text">Belum ada gambar</div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="openMediaPickerForSlide(this)" style="width: 140px; margin-top: 0.5rem; font-size: 0.78rem; font-weight: 600; color: #0f766e; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 0; text-align: center;">
                    🖼️ Pilih Media
                </button>
            </div>

            <!-- Right: Type & URL -->
            <div style="flex: 1; min-width: 280px;">
                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.35rem;">Format Slide</label>
                        <select class="admin-input slide-type" onchange="toggleSlideFields(this)" style="font-size: 0.85rem; padding: 0.5rem 0.6rem;">
                            <option value="hero">Teks + Tombol (Hero)</option>
                            <option value="image">Gambar Penuh (Banner)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.35rem;">URL / Path Gambar Latar</label>
                        <input type="text" class="admin-input slide-image" placeholder="/uploads/... atau https://..." oninput="updateSlideThumbnail(this)" style="font-size: 0.85rem; padding: 0.5rem 0.6rem;">
                    </div>
                </div>

                <!-- Hero Content Fields (Title, Desc, Button) -->
                <div class="hero-fields" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.9rem; margin-top: 0.75rem;">
                    <div style="margin-bottom: 0.65rem;">
                        <label style="font-size: 0.78rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.25rem;">Judul Utama (Heading)</label>
                        <input type="text" class="admin-input slide-heading" placeholder="Judul slide inspiratif..." style="font-size: 0.88rem; padding: 0.45rem 0.65rem;">
                    </div>
                    
                    <div style="margin-bottom: 0.65rem;">
                        <label style="font-size: 0.78rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.25rem;">Deskripsi / Sub-judul</label>
                        <textarea class="admin-input slide-desc" style="min-height: 52px; font-size: 0.85rem; padding: 0.45rem 0.65rem;" placeholder="Penjelasan singkat konten slide..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.25rem;">Teks Tombol CTA</label>
                            <input type="text" class="admin-input slide-btn-text" placeholder="Pelajari Selengkapnya" style="font-size: 0.85rem; padding: 0.45rem 0.65rem;">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600; color: #475569; display: block; margin-bottom: 0.25rem;">Tautan Tombol (URL)</label>
                            <input type="text" class="admin-input slide-btn-link" placeholder="#tentang atau /pendaftaran" style="font-size: 0.85rem; padding: 0.45rem 0.65rem;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let currentSliders = [];
let activeSlideInputTarget = null;

// Switch tabs inside modal
function switchModalTab(tabId) {
    document.querySelectorAll('.modal-nav-tab').forEach(b => b.classList.remove('active'));
    document.querySelector(`.modal-nav-tab[data-tab="${tabId}"]`).classList.add('active');
    
    document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
}

// Load sliders from backend
async function loadSliders() {
    try {
        const res = await Admin.request('GET', '/admin/api/sliders');
        currentSliders = res.data || [];
        renderSliderList();
    } catch(e) {
        Admin.alert('Gagal memuat slider: ' + e.message, true);
    }
}

// Render modern cards
function renderSliderList() {
    const container = document.getElementById('sliders-list');
    const badge = document.getElementById('slider-count-badge');
    badge.textContent = `${currentSliders.length} Slider`;
    container.innerHTML = '';
    
    if (currentSliders.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1 / -1; background: #fff; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 3.5rem 1.5rem; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 0.75rem;">🖼️</div>
                <h3 style="margin: 0; font-size: 1.2rem; color: #1e293b;">Belum ada Slider yang dibuat</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0.5rem 0 1.5rem 0;">Buat slideshow pertama Anda untuk menampilkan promosi atau profil beranda.</p>
                <button class="btn btn-primary" onclick="showSliderForm()">+ Buat Slider Pertama</button>
            </div>
        `;
        return;
    }
    
    currentSliders.forEach(slider => {
        const card = document.createElement('div');
        card.className = 'slider-card';
        
        const shortcode = `[slider id="${slider.id}"]`;
        const isAuto = slider.autoplay !== false;
        const intervalSec = slider.interval ? Math.round(slider.interval / 1000) : 5;
        const effect = slider.effect || 'fade';
        const anim = slider.content_anim || 'fade-up';
        const layout = slider.layout || 'hero';
        const slides = slider.slides || [];
        
        // Find cover image from first slide
        let coverImg = '';
        if (slides.length > 0 && slides[0].image) {
            coverImg = slides[0].image;
        }

        const effectLabels = {
            'fade': 'Fade Halus',
            'loop': 'Slide Loop',
            'kenburns': 'Ken Burns',
            'ttb': 'Vertikal'
        };
        const effectText = effectLabels[effect] || effect;

        const layoutLabels = {
            'hero': 'Layar Penuh (100vh)',
            'standard': 'Standar Boxed',
            'article': 'Artikel / Berita'
        };
        const layoutText = layoutLabels[layout] || 'Layar Penuh';

        // Strip of thumbnails
        let stripHtml = '';
        if (slides.length > 0) {
            stripHtml = slides.slice(0, 5).map((s, idx) => {
                if (s.image) {
                    return `<img src="${Admin.escapeHtml(s.image)}" class="slide-strip-thumb" title="Slide #${idx+1}" alt="" onerror="this.src='/assets/admin/placeholder.png'">`;
                }
                return `<div class="slide-strip-placeholder">#${idx+1}</div>`;
            }).join('');
            if (slides.length > 5) {
                stripHtml += `<div class="slide-strip-placeholder">+${slides.length - 5}</div>`;
            }
        } else {
            stripHtml = '<span style="font-size:0.75rem; color:#94a3b8; font-style:italic;">Belum ada slide</span>';
        }

        card.innerHTML = `
            <div class="slider-card-cover" style="${coverImg ? `background-image: url('${Admin.escapeHtml(coverImg)}');` : ''}">
                <div class="slider-card-badges">
                    <span class="badge-glass ${isAuto ? 'active-auto' : ''}">
                        ${isAuto ? `● Auto: ${intervalSec}s` : '○ Auto: OFF'}
                    </span>
                    <span class="badge-glass">
                        📐 ${Admin.escapeHtml(layoutText)}
                    </span>
                    <span class="badge-glass">
                        🎬 ${Admin.escapeHtml(effectText)}
                    </span>
                </div>
                <div>
                    <h3 style="margin: 0; color: #ffffff; font-size: 1.25rem; font-weight: 700; text-shadow: 0 2px 8px rgba(0,0,0,0.6);">${Admin.escapeHtml(slider.name)}</h3>
                    <span style="color: rgba(255,255,255,0.85); font-size: 0.8rem; font-weight: 500;">${slides.length} slide terpasang</span>
                </div>
            </div>

            <div class="slider-card-body">
                <div>
                    <label style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.35rem;">Shortcode Pemanggilan</label>
                    <div class="shortcode-container">
                        <span class="shortcode-code">${shortcode}</span>
                        <button type="button" class="btn-copy-code" onclick="copyShortcode('${shortcode}', this)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            <span>Salin</span>
                        </button>
                    </div>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.35rem; line-height: 1.4;">
                        Opsi: <code>layout="article"</code>, <code>layout="standard"</code>, <code>height="450px"</code>
                    </small>
                </div>

                <div>
                    <label style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.35rem;">Pratinjau Slide</label>
                    <div class="slides-strip">
                        ${stripHtml}
                    </div>
                </div>
            </div>

            <div class="slider-card-footer">
                <button type="button" class="btn btn-sm btn-ghost" onclick="deleteSlider('${slider.id}')" style="color: #dc2626; display: inline-flex; align-items: center; gap: 4px;" title="Hapus Slider">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Hapus</span>
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="editSlider('${slider.id}')" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.45rem 1rem; font-weight: 600; border-radius: 6px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Edit Slider</span>
                </button>
            </div>
        `;
        container.appendChild(card);
    });
}

// Copy shortcode to clipboard
function copyShortcode(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const span = btn.querySelector('span');
        const origText = span.textContent;
        btn.classList.add('copied');
        span.textContent = 'Tersalin!';
        setTimeout(() => {
            btn.classList.remove('copied');
            span.textContent = origText;
        }, 2000);
    });
}

// Slide items management
function toggleSlideFields(selectEl) {
    const item = selectEl.closest('.slide-card-item');
    const heroFields = item.querySelector('.hero-fields');
    const typeLabel = item.querySelector('.slide-type-label');
    if (selectEl.value === 'hero') {
        heroFields.style.display = 'block';
        if (typeLabel) typeLabel.textContent = 'Hero Banner (Teks & CTA)';
    } else {
        heroFields.style.display = 'none';
        if (typeLabel) typeLabel.textContent = 'Gambar Penuh (Banner)';
    }
}

function updateSlideThumbnail(inputEl) {
    const item = inputEl.closest('.slide-card-item');
    const previewBox = item.querySelector('.slide-image-preview-box');
    const img = previewBox.querySelector('.img-preview');
    const noImg = previewBox.querySelector('.no-img-text');
    const val = inputEl.value.trim();

    if (val) {
        img.src = val;
        img.style.display = 'block';
        noImg.style.display = 'none';
        img.onerror = () => {
            img.style.display = 'none';
            noImg.style.display = 'block';
            noImg.textContent = 'Gambar rusak';
        };
    } else {
        img.style.display = 'none';
        noImg.style.display = 'block';
        noImg.textContent = 'Belum ada gambar';
    }
}

function renumberSlides() {
    const items = document.querySelectorAll('#slides-container .slide-card-item');
    items.forEach((item, idx) => {
        item.querySelector('.index').textContent = idx + 1;
    });
    document.getElementById('slides-count-pill').textContent = `${items.length} Slide`;
}

function addSlide(data = null) {
    const tpl = document.getElementById('slide-template').content.cloneNode(true);
    const container = document.getElementById('slides-container');
    
    if (data) {
        tpl.querySelector('.slide-type').value = data.type || 'hero';
        tpl.querySelector('.slide-image').value = data.image || '';
        tpl.querySelector('.slide-heading').value = data.title || '';
        tpl.querySelector('.slide-desc').value = data.description || '';
        tpl.querySelector('.slide-btn-text').value = data.button_text || '';
        tpl.querySelector('.slide-btn-link').value = data.button_link || '';
    }
    
    container.appendChild(tpl);
    
    const items = container.querySelectorAll('.slide-card-item');
    const lastItem = items[items.length - 1];
    
    toggleSlideFields(lastItem.querySelector('.slide-type'));
    updateSlideThumbnail(lastItem.querySelector('.slide-image'));
    renumberSlides();
}

function removeSlide(btn) {
    const item = btn.closest('.slide-card-item');
    item.remove();
    renumberSlides();
}

function moveSlideUp(btn) {
    const item = btn.closest('.slide-card-item');
    const prev = item.previousElementSibling;
    if (prev) {
        item.parentNode.insertBefore(item, prev);
        renumberSlides();
    }
}

function moveSlideDown(btn) {
    const item = btn.closest('.slide-card-item');
    const next = item.nextElementSibling;
    if (next) {
        item.parentNode.insertBefore(next, item);
        renumberSlides();
    }
}

// Media Picker integration
async function openMediaPickerForSlide(btn) {
    const item = btn.closest('.slide-card-item');
    activeSlideInputTarget = item.querySelector('.slide-image');
    
    const modal = document.getElementById('slider-media-modal');
    const grid = document.getElementById('media-picker-grid');
    grid.innerHTML = '<p style="grid-column:1/-1; color:#94a3b8; text-align:center;">Memuat pustaka media...</p>';
    modal.classList.add('show');

    try {
        const res = await Admin.request('GET', '/admin/media');
        const items = res.data || [];
        
        if (items.length === 0) {
            grid.innerHTML = '<p style="grid-column:1/-1; color:#94a3b8; text-align:center;">Belum ada media terunggah di CMS.</p>';
            return;
        }

        grid.innerHTML = items.map(m => {
            const url = `/uploads/${m.disk_path}`;
            return `
                <div onclick="selectMediaUrl('${url}')" style="cursor:pointer; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; background:#f8fafc; transition:all 0.2s;" onmouseover="this.style.borderColor='var(--admin-primary)'; this.style.transform='scale(1.03)'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='none'">
                    <img src="${url}" alt="" style="width:100%; height:85px; object-fit:cover; display:block;">
                    <div style="padding:4px 6px; font-size:0.7rem; color:#475569; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${Admin.escapeHtml(m.file_name)}</div>
                </div>
            `;
        }).join('');
    } catch(e) {
        grid.innerHTML = `<p style="grid-column:1/-1; color:#dc2626;">Gagal memuat media: ${Admin.escapeHtml(e.message)}</p>`;
    }
}

function selectMediaUrl(url) {
    if (activeSlideInputTarget) {
        activeSlideInputTarget.value = url;
        updateSlideThumbnail(activeSlideInputTarget);
    }
    closeMediaPicker();
}

function closeMediaPicker() {
    document.getElementById('slider-media-modal').classList.remove('show');
}

async function uploadDirectToSlide(fileInput) {
    if (!fileInput.files || !fileInput.files[0]) return;
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        Admin.alert('Mengunggah gambar...');
        const res = await Admin.request('POST', '/admin/media', formData);
        const url = `/uploads/${res.data.disk_path}`;
        selectMediaUrl(url);
        Admin.alert('Gambar berhasil diunggah dan diterapkan ke slide!');
    } catch (err) {
        Admin.alert('Gagal mengunggah gambar: ' + err.message, true);
    } finally {
        fileInput.value = '';
    }
}

// Modal open/close
function showSliderForm(data = null) {
    document.getElementById('slides-container').innerHTML = '';
    switchModalTab('tab-slides');
    
    if (data) {
        document.getElementById('modal-title').textContent = 'Edit Slider';
        document.getElementById('modal-subtitle').textContent = `Mengubah pengaturan untuk slider: ${data.name}`;
        document.getElementById('slider-id').value = data.id;
        document.getElementById('slider-id').disabled = true;
        document.getElementById('slider-name').value = data.name;
        document.getElementById('slider-layout').value = data.layout || 'hero';
        document.getElementById('slider-height').value = data.height || '';
        
        // Settings
        document.getElementById('slider-autoplay').checked = data.autoplay !== false;
        document.getElementById('slider-pause-on-hover').checked = !!data.pause_on_hover;
        
        const sec = data.interval ? Math.round(data.interval / 1000) : 5;
        document.getElementById('slider-interval').value = String(sec);
        document.getElementById('slider-effect').value = data.effect || 'fade';
        document.getElementById('slider-content-anim').value = data.content_anim || 'fade-up';
        document.getElementById('slider-speed').value = data.speed || '800';
        document.getElementById('slider-easing').value = data.easing || 'ease-out';
        
        if (data.slides && data.slides.length > 0) {
            data.slides.forEach(slide => addSlide(slide));
        } else {
            addSlide();
        }
    } else {
        document.getElementById('modal-title').textContent = 'Buat Slider Baru';
        document.getElementById('modal-subtitle').textContent = 'Tambahkan slider slideshow baru untuk beranda atau halaman lain.';
        document.getElementById('slider-id').value = '';
        document.getElementById('slider-id').disabled = false;
        document.getElementById('slider-name').value = '';
        document.getElementById('slider-layout').value = 'hero';
        document.getElementById('slider-height').value = '';
        
        // Defaults
        document.getElementById('slider-autoplay').checked = true;
        document.getElementById('slider-pause-on-hover').checked = false;
        document.getElementById('slider-interval').value = '5';
        document.getElementById('slider-effect').value = 'fade';
        document.getElementById('slider-content-anim').value = 'fade-up';
        document.getElementById('slider-speed').value = '800';
        document.getElementById('slider-easing').value = 'ease-out';
        
        addSlide();
    }
    
    document.getElementById('slider-modal').classList.add('show');
}

function editSlider(id) {
    const slider = currentSliders.find(s => s.id === id);
    if (slider) showSliderForm(slider);
}

async function deleteSlider(id) {
    const yes = await Admin.confirmDelete('Slider ' + id);
    if (!yes) return;
    
    try {
        await Admin.request('DELETE', '/admin/api/sliders', { id });
        Admin.alert('Slider berhasil dihapus.');
        loadSliders();
    } catch(e) {
        Admin.alert(e.message, true);
    }
}

function closeModal() {
    document.getElementById('slider-modal').classList.remove('show');
}

// SAVE SLIDER WITH SPINNER LOADING ANIMATION
async function saveSlider() {
    const id = document.getElementById('slider-id').value.trim();
    const name = document.getElementById('slider-name').value.trim();
    
    if (!id || !name) {
        Admin.alert('ID Unik dan Nama Slider wajib diisi!', true);
        return;
    }

    const saveBtn = document.getElementById('btn-save-slider');
    const saveIcon = document.getElementById('save-btn-icon');
    const saveSpinner = document.getElementById('save-btn-spinner');
    const saveText = document.getElementById('save-btn-text');

    // Activate Loading State
    saveBtn.disabled = true;
    saveIcon.style.display = 'none';
    saveSpinner.style.display = 'inline-block';
    saveText.textContent = 'Menyimpan Slider...';

    const layout = document.getElementById('slider-layout').value || 'hero';
    const height = document.getElementById('slider-height').value.trim();
    const autoplay = document.getElementById('slider-autoplay').checked;
    const pause_on_hover = document.getElementById('slider-pause-on-hover').checked;
    const intervalSec = parseFloat(document.getElementById('slider-interval').value) || 5;
    const interval = Math.max(2, intervalSec) * 1000;
    const effect = document.getElementById('slider-effect').value || 'fade';
    const content_anim = document.getElementById('slider-content-anim').value || 'fade-up';
    const speed = parseInt(document.getElementById('slider-speed').value) || 800;
    const easing = document.getElementById('slider-easing').value || 'ease-out';
    
    const slideItems = document.querySelectorAll('#slides-container .slide-card-item');
    const slides = [];
    
    slideItems.forEach(item => {
        slides.push({
            type: item.querySelector('.slide-type').value,
            image: item.querySelector('.slide-image').value.trim(),
            title: item.querySelector('.slide-heading').value.trim(),
            description: item.querySelector('.slide-desc').value.trim(),
            button_text: item.querySelector('.slide-btn-text').value.trim(),
            button_link: item.querySelector('.slide-btn-link').value.trim()
        });
    });
    
    const payload = {
        id,
        name,
        layout,
        height,
        autoplay,
        pause_on_hover,
        interval,
        effect,
        content_anim,
        speed,
        easing,
        slides
    };
    
    try {
        await Admin.request('POST', '/admin/api/sliders', payload);
        Admin.alert('Slider berhasil disimpan!');
        closeModal();
        await loadSliders();
    } catch (e) {
        Admin.alert(e.message, true);
    } finally {
        // Reset Button Loading State
        saveBtn.disabled = false;
        saveIcon.style.display = 'inline-block';
        saveSpinner.style.display = 'none';
        saveText.textContent = 'Simpan Slider';
    }
}

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', loadSliders);
</script>
