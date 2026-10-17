<style>
.nested-sortable { min-height: 10px; }
.menu-item-block { margin-bottom: 0.35rem; }
.menu-item-content { border: 1px solid var(--admin-border); background: #fff; border-radius: 4px; }
.menu-item-header { padding: 0.5rem 0.75rem; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; cursor: grab; border-radius: 4px; border-bottom: 1px solid transparent; }
.menu-item-header:active { cursor: grabbing; }
.menu-item-header.expanded { border-bottom-color: var(--admin-border); border-bottom-left-radius: 0; border-bottom-right-radius: 0; }
.menu-item-body { padding: 0.75rem; display: none; background: #fff; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; }
.menu-item-body.show { display: block; }
.menu-item-title { font-weight: 600; font-size: 0.9rem; color: #1e293b; }
.menu-item-type { font-size: 0.65rem; color: var(--admin-muted); text-transform: uppercase; margin-left: 0.5rem; }
.menu-item-children { padding-left: 2.5rem; }
.menu-item-children:not(:empty) { margin-top: 0.5rem; padding-bottom: 0; }
.menu-item-block.is-dragging > .menu-item-content { opacity: 0.5; }
.menu-item-block.drag-over > .menu-item-content { border-top: 2px solid var(--admin-primary); }
.menu-item-block.drag-over-child > .menu-item-content { border-left: 2px solid var(--admin-primary); }
.accordion-content label { display: flex !important; align-items: center !important; justify-content: flex-start !important; gap: 0.5rem !important; padding: 0.25rem 0 !important; cursor: pointer !important; text-align: left !important; }
.accordion-content label input { margin: 0 !important; width: auto !important; flex: none !important; }

@media (max-width: 768px) {
    .menu-action-btn { flex: 1; }
    .menu-sidebar { flex: 1 1 100% !important; max-width: 100% !important; min-width: 0 !important; }
    .menu-main { flex: 1 1 100% !important; max-width: 100% !important; min-width: 0 !important; }
}
</style>

<div style="background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; margin-bottom: 1.5rem; padding: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center;">
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; flex: 1; min-width: 260px;">
            <span style="font-weight:600; color:#1e293b; font-size:0.95rem; white-space: nowrap;">Pilih Menu :</span>
            <select id="menu-select" class="admin-input" style="min-width: 150px; flex: 1; max-width: 300px; padding:0.4rem 0.75rem; height: 34px; margin: 0;"></select>
            <button type="button" id="menu-create-link" class="btn btn-sm btn-ghost" style="color:var(--admin-primary); font-weight:600; white-space: nowrap; height: 34px;">
                + Menu Baru
            </button>
        </div>
        
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; justify-content: flex-end; flex: 1; min-width: 260px;">
            <span id="menu-save-status" style="font-size: 0.85rem; color: var(--admin-muted); width: 100%; text-align: right; display: none;"></span>
            <button type="button" id="menu-delete-btn" class="btn btn-ghost menu-action-btn" style="display:none; color:var(--admin-danger); border: 1px solid #fca5a5; font-weight:600; min-height: 38px; height: auto; justify-content: center; padding: 0.5rem; line-height: 1.2; white-space: normal;">Hapus</button>
            <button id="menu-save-btn" class="btn btn-primary menu-action-btn" style="display:none; min-height: 38px; height: auto; min-width: 140px; justify-content: center; padding: 0.5rem 1rem; line-height: 1.2; white-space: normal;">Simpan Perubahan</button>
        </div>
    </div>
</div>

<div id="menu-create-wrapper" hidden style="margin-bottom: 1.5rem; padding: 1rem; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px;">
    <form id="menu-create-form" style="display:flex; flex-wrap: wrap; gap:.75rem; align-items:center;">
        <input type="text" id="menu-name" class="admin-input" placeholder="Nama menu (mis. Main Navigation)" required style="margin:0; flex: 1; min-width: 200px; padding:0.4rem 0.5rem; border:1px solid var(--admin-border); border-radius:4px;">
        <input type="text" id="menu-slug" class="admin-input" placeholder="slug (mis. main-nav)" required style="margin:0; flex: 1; min-width: 150px; padding:0.4rem 0.5rem; border:1px solid var(--admin-border); border-radius:4px;">
        <button type="submit" class="btn btn-primary" style="flex: 1; min-width: 120px;">Buat Menu</button>
        <button type="button" id="menu-create-cancel" class="btn btn-ghost" style="flex: 1; min-width: 100px;">Batal</button>
    </form>
</div>

<div id="menu-builder-wrapper" class="editor-layout" style="display:none; gap:1.5rem; align-items:flex-start;">
    <div class="menu-sidebar" style="flex: 0 0 320px;">
        <div class="sidebar-card" style="padding: 1rem;">
            <h3 style="margin-top:0; margin-bottom:1rem; font-size:1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.5rem;">Tambahkan Item</h3>
            
            <details class="menu-add-accordion" style="margin-bottom: 0.5rem; border:1px solid var(--admin-border); border-radius:4px;">
                <summary style="padding:0.75rem; cursor:pointer; font-weight:600; background:#f8fafc; border-bottom:1px solid var(--admin-border);">Halaman</summary>
                <div style="padding:0.75rem; max-height:200px; overflow-y:auto; font-size:0.9rem;" class="accordion-content" id="menu-add-pages">Memuat...</div>
                <div style="padding:0.75rem; border-top:1px solid var(--admin-border); text-align:right; background:#f8fafc;">
                    <button type="button" class="btn btn-sm btn-ghost add-to-menu-btn" data-type="page">Tambah ke Menu</button>
                </div>
            </details>

            <details class="menu-add-accordion" style="margin-bottom: 0.5rem; border:1px solid var(--admin-border); border-radius:4px;">
                <summary style="padding:0.75rem; cursor:pointer; font-weight:600; background:#f8fafc; border-bottom:1px solid var(--admin-border);">Kategori</summary>
                <div style="padding:0.75rem; max-height:200px; overflow-y:auto; font-size:0.9rem;" class="accordion-content" id="menu-add-categories">Memuat...</div>
                <div style="padding:0.75rem; border-top:1px solid var(--admin-border); text-align:right; background:#f8fafc;">
                    <button type="button" class="btn btn-sm btn-ghost add-to-menu-btn" data-type="category">Tambah ke Menu</button>
                </div>
            </details>

            <details class="menu-add-accordion" style="margin-bottom: 0.5rem; border:1px solid var(--admin-border); border-radius:4px;">
                <summary style="padding:0.75rem; cursor:pointer; font-weight:600; background:#f8fafc; border-bottom:1px solid var(--admin-border);">Tautan Kustom</summary>
                <div style="padding:0.75rem; font-size:0.9rem;">
                    <label style="display:block; margin-bottom:0.5rem;">URL<input type="text" id="custom-link-url" placeholder="https://" style="margin-bottom:0; width:100%; box-sizing:border-box; padding:0.4rem 0.5rem; border:1px solid var(--admin-border); border-radius:4px;"></label>
                    <label style="display:block; margin-bottom:0.5rem;">Teks Tautan<input type="text" id="custom-link-text" placeholder="Teks menu..." style="margin-bottom:0; width:100%; box-sizing:border-box; padding:0.4rem 0.5rem; border:1px solid var(--admin-border); border-radius:4px;"></label>
                </div>
                <div style="padding:0.75rem; border-top:1px solid var(--admin-border); text-align:right; background:#f8fafc;">
                    <button type="button" class="btn btn-sm btn-ghost" id="add-custom-link-btn">Tambah ke Menu</button>
                </div>
            </details>
            
            <details class="menu-add-accordion" style="margin-bottom: 0.5rem; border:1px solid var(--admin-border); border-radius:4px;">
                <summary style="padding:0.75rem; cursor:pointer; font-weight:600; background:#f8fafc; border-bottom:1px solid var(--admin-border);">Sistem</summary>
                <div style="padding:0.75rem; max-height:200px; overflow-y:auto; font-size:0.9rem;" class="accordion-content" id="menu-add-system">
                    <label><input type="checkbox" value="1" data-label="Login Admin"> Login Admin</label>
                    <label><input type="checkbox" value="2" data-label="Cari"> Cari</label>
                    <label><input type="checkbox" value="3" data-label="Sitemap"> Sitemap</label>
                </div>
                <div style="padding:0.75rem; border-top:1px solid var(--admin-border); text-align:right; background:#f8fafc;">
                    <button type="button" class="btn btn-sm btn-ghost add-to-menu-btn" data-type="system">Tambah ke Menu</button>
                </div>
            </details>
        </div>
    </div>

    <div class="menu-main" style="flex:1;">
        <div class="sidebar-card" style="padding: 1.5rem; min-height: 400px; background:#f8fafc;">
            <h3 style="margin-top:0; font-size:1.2rem;" id="active-menu-name">Struktur Menu</h3>
            <p style="color:var(--admin-muted); font-size:0.85rem; margin-bottom:1.5rem;">Tarik dan lepas (drag and drop) item untuk mengatur urutannya. Tarik menjorok ke kanan di bawah item lain untuk membuatnya menjadi sub-menu.</p>
            
            <div id="menu-items-list" class="nested-sortable">
                <p style="color:var(--admin-muted); font-style:italic;">Memuat struktur menu...</p>
            </div>
        </div>
    </div>
</div>

<template id="menu-item-template">
    <div class="menu-item-block" draggable="true" data-id="">
        <div class="menu-item-content">
            <div class="menu-item-header">
                <div>
                    <span class="menu-item-title"></span>
                    <span class="menu-item-type"></span>
                </div>
                <button type="button" class="btn btn-sm btn-ghost toggle-item-btn" style="padding:0.2rem 0.5rem;">▼</button>
            </div>
            <div class="menu-item-body">
                <div style="display:flex; gap:1rem; margin-bottom:1rem;">
                    <div style="flex:1;">
                        <label style="font-size:0.85rem; font-weight:600; display:block; margin-bottom:0.3rem;">Label Navigasi</label>
                        <input type="text" class="edit-label" style="width:100%; padding:0.4rem 0.6rem; border:1px solid var(--admin-border); border-radius:4px; box-sizing:border-box; font-size:0.9rem;">
                    </div>
                    <div style="flex:1;" class="url-field-wrap">
                        <label style="font-size:0.85rem; font-weight:600; display:block; margin-bottom:0.3rem;">URL</label>
                        <input type="text" class="edit-url" style="width:100%; padding:0.4rem 0.6rem; border:1px solid var(--admin-border); border-radius:4px; box-sizing:border-box; font-size:0.9rem; color:var(--admin-muted);">
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding-top:0.75rem; border-top:1px dashed #e2e8f0;">
                    <label style="font-size:0.85rem; display:flex; align-items:center; justify-content:flex-start; gap:0.5rem; cursor:pointer; margin:0; font-weight:500;">
                        <input type="checkbox" class="edit-new-tab" style="margin:0; width:auto; flex:none;"> Buka tautan di tab baru
                    </label>
                    <button type="button" class="btn btn-sm btn-ghost remove-item-btn" style="color:#ef4444; padding:0.25rem 0.5rem; font-size:0.8rem;">Hapus Item</button>
                </div>
            </div>
        </div>
        <div class="menu-item-children nested-sortable"></div>
    </div>
</template>
