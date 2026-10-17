<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Manajer Tema</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Kelola tampilan situs Anda dengan memasang dan mengaktifkan tema.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 1rem; line-height: 1;">
            + Unggah Tema (.zip)
            <input type="file" id="theme-upload-input" accept=".zip" style="display: none;">
        </label>
    </div>
</div>

<div id="themes-upload-progress" class="admin-card" style="display: none; margin-bottom: 1rem; padding: 1rem; text-align: center; border: 2px dashed var(--admin-primary); background: #f0fdfa;">
    <div style="font-weight: 600; color: var(--admin-primary); margin-bottom: 0.5rem;">Mengunggah dan mengekstrak tema...</div>
    <div style="font-size: 0.85rem; color: var(--admin-muted);">Mohon tunggu sebentar, proses ini memakan waktu tergantung pada ukuran file tema.</div>
</div>

<div id="themes-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
    <!-- Rendered via JS -->
    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--admin-muted);">Memuat daftar tema...</div>
</div>
