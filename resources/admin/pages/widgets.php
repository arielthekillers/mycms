<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Pengelola Widget</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Kelola blok konten pada area yang didukung oleh tema Anda.</p>
    </div>
</div>

<div class="editor-layout" style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1.5rem; align-items: flex-start;">
    
    <!-- Sidebar Area List -->
    <div class="widget-sidebar" style="flex: 0 0 280px; width: 100%;">
        <div class="admin-card" style="padding: 1rem;">
            <h3 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); margin: 0 0 1rem 0;">Area Widget</h3>
            <div id="widget-area-list" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="padding: 1rem; text-align: center; color: var(--admin-muted);">Memuat area...</div>
            </div>
        </div>
    </div>

    <!-- Active Area Workspace -->
    <div class="widget-main admin-card" id="widget-workspace" style="display: none; flex-direction: column; padding: 1.5rem; min-height: 400px; flex: 1; min-width: 0;">
        <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 1rem;">
            <h2 id="active-area-title" style="margin: 0; font-size: 1.2rem; font-weight: 600; color: var(--admin-primary);">Pilih Area</h2>
            <button class="btn btn-primary" id="btn-show-add-widget" style="flex: 1; min-width: 140px; max-width: max-content; justify-content: center;">+ Tambah Widget</button>
        </div>

        <div id="widget-instance-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
            <!-- Rendered via JS -->
            <div style="text-align: center; padding: 2rem; color: var(--admin-muted);">Silakan pilih area widget di sebelah kiri.</div>
        </div>
    </div>
</div>

<!-- Modal Tambah Widget -->
<div id="widget-add-panel" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: white; width: 100%; max-width: 650px; border-radius: 12px; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto; position: relative;">
        <button type="button" id="btn-close-modal" style="position: absolute; top: 1.25rem; right: 1.25rem; background: none; border: none; cursor: pointer; color: #64748b; padding: 0.5rem; display: flex; align-items: center; justify-content: center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        
        <div id="widget-selection-step">
            <h3 style="margin: 0 0 1.5rem 0; font-size: 1.25rem; color: #0f172a;">Pilih Tipe Widget</h3>
            <div id="widget-selection-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                <!-- Diisi via JS -->
            </div>
        </div>
        
        <div id="widget-form-step" style="display: none;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                <button type="button" id="btn-back-to-selection" style="background: none; border: none; cursor: pointer; color: #64748b; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </button>
                <h3 style="margin: 0; font-size: 1.25rem; color: #0f172a;" id="form-step-title">Pengaturan Widget</h3>
            </div>

            <form id="widget-add-form" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" id="widget-type-input">
                
                <label>
                    <span style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Judul Widget (Opsional)</span>
                    <input type="text" id="widget-title-override" placeholder="Ganti judul default..." style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
                </label>
                
                <div id="dynamic-widget-fields" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 0.5rem; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                    <!-- Diisi secara dinamis melalui JavaScript -->
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-ghost" id="btn-cancel-add" style="padding: 0.65rem 1.5rem;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem;">Simpan Widget</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Styling khusus untuk halaman Widget modern */
    .widget-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    @media (max-width: 768px) {
        .widget-sidebar {
            flex: 1 1 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }
        .widget-sidebar > .admin-card {
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
        .widget-sidebar h3 {
            padding-left: 0.25rem;
        }
        .area-btn {
            background: #fff !important;
            border: 1px solid var(--admin-border) !important;
            border-radius: 8px !important;
        }
        .widget-main {
            flex: 1 1 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
        .widget-form-grid {
            grid-template-columns: 1fr;
        }
    }

    .area-btn {
        padding: 0.75rem 1rem;
        border-radius: 6px;
        background: transparent;
        border: 1px solid transparent;
        text-align: left;
        cursor: pointer;
        font-weight: 500;
        color: var(--text-main);
        transition: all 0.2s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .area-btn:hover {
        background: #f1f5f9;
    }
    .area-btn.active {
        background: #e0e7ff;
        color: var(--brand);
        border-color: #c7d2fe;
    }
    .area-btn .badge {
        font-size: 0.7rem;
        background: white;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        color: var(--admin-muted);
        border: 1px solid #e2e8f0;
    }
    .area-btn.active .badge {
        background: white;
        color: var(--brand);
        border-color: #c7d2fe;
    }

    .widget-type-card {
        padding: 1.25rem;
        border: 1px solid var(--admin-border);
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .widget-type-card:hover {
        border-color: var(--brand);
        box-shadow: 0 4px 6px -1px rgba(15, 118, 110, 0.1);
        transform: translateY(-2px);
    }
    .widget-type-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--brand);
    }
    .widget-type-title {
        font-weight: 600;
        color: var(--text-main);
        font-size: 0.95rem;
    }
    .widget-type-desc {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .widget-item-card {
        background: white;
        border: 1px solid var(--admin-border);
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .widget-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .widget-item-card.dragging {
        opacity: 0.5;
        border: 2px dashed var(--brand);
    }
    .widget-handle {
        cursor: grab;
        color: #94a3b8;
        margin-right: 1rem;
        display: inline-flex;
        align-items: center;
    }
</style>
