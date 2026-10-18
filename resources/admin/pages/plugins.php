<div class="toolbar-modern" style="margin-bottom: 2rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em;">Manajer Plugin</h2>
            <span id="plugins-stat-badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 50px;">Memuat...</span>
        </div>
        <p style="margin: 0.35rem 0 0 0; color: #64748b; font-size: 0.9rem;">Kelola ekstensi, modul tambahan, dan integrasi eksternal untuk memperluas kapabilitas CMS.</p>
    </div>
    <form id="plugin-upload-form" style="display: flex; gap: 1rem; align-items: center;">
        <label class="btn btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; height: 42px; padding: 0 1.25rem; font-weight: 600; border-radius: 8px; box-shadow: 0 4px 14px rgba(15,118,110,0.25);">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            <span>+ Unggah Plugin (.zip)</span>
            <input type="file" id="plugin-zip" accept=".zip" style="display: none;" required>
        </label>
        <button type="submit" class="btn btn-primary" style="display: none;" id="plugin-upload-submit">Install</button>
    </form>
</div>

<style>
.plugin-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 1.75rem;
}
@media (max-width: 640px) {
    .plugin-grid-modern {
        grid-template-columns: 1fr;
    }
}

.plugin-card-modern {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}
.plugin-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.plugin-card-modern.is-active {
    border-top: 3px solid var(--admin-primary, #0f766e);
}
.plugin-card-modern.is-inactive {
    border-top: 3px solid #cbd5e1;
    opacity: 0.92;
}

.plugin-card-top {
    padding: 1.5rem 1.5rem 1rem 1.5rem;
    flex: 1;
}

.plugin-header-row {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.plugin-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

.plugin-title-wrap {
    flex: 1;
    min-width: 0;
}
.plugin-title {
    margin: 0 0 0.25rem 0;
    font-size: 1.12rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
}

.plugin-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 50px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.plugin-status-badge.active {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.plugin-status-badge.inactive {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
}

.plugin-desc {
    margin: 0 0 1.25rem 0;
    font-size: 0.88rem;
    color: #475569;
    line-height: 1.55;
    min-height: 42px;
}

.plugin-meta-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.6rem;
    padding-top: 0.85rem;
    border-top: 1px dashed #e2e8f0;
    font-size: 0.8rem;
    color: #64748b;
}

.plugin-author-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    color: #334155;
    font-weight: 500;
}
.plugin-author-badge strong {
    color: var(--admin-primary, #0f766e);
    font-weight: 700;
}

.plugin-tags-group {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.plugin-pill {
    background: #f1f5f9;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 4px;
    font-family: ui-monospace, monospace;
}

.plugin-card-actions {
    padding: 0.9rem 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 0.6rem;
    align-items: center;
    justify-content: flex-end;
}
</style>

<div id="plugin-list-view" class="plugin-grid-modern">
    <div style="grid-column: 1 / -1; color: var(--admin-muted); font-style: italic; padding: 2rem 0; text-align: center;">Memuat pustaka plugin...</div>
</div>
