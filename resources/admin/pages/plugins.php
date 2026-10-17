<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Manajer Plugin</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Kelola fitur tambahan situs Anda dengan menginstal dan mengaktifkan plugin.</p>
    </div>
    <form id="plugin-upload-form" style="display: flex; gap: 1rem; align-items: center;">
        <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 1rem; line-height: 1;">
            + Unggah Plugin (.zip)
            <input type="file" id="plugin-zip" accept=".zip" style="display: none;" required>
        </label>
        <button type="submit" class="btn btn-primary" style="height: 34px; line-height: 1;" hidden id="plugin-upload-submit">Install</button>
    </form>
</div>

<div id="plugin-list-view" style="margin-top: 1.5rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
    <!-- Plugins will be rendered here by JS -->
    <div style="grid-column: 1 / -1; color: var(--admin-muted); font-style: italic;">Memuat data...</div>
</div>
