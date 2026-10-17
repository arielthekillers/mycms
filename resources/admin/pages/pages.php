<div id="pages-list-view">
    <div class="toolbar-modern" style="align-items: flex-end;">
        <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%; max-width: 100%;">
            <div class="tabs-container" id="pages-tabs" style="overflow-x: auto; white-space: nowrap; padding-bottom: 0.25rem;">
                <button type="button" class="tab-btn active" data-status="">Semua Halaman</button>
                <button type="button" class="tab-btn" data-status="published">Published</button>
                <button type="button" class="tab-btn" data-status="draft">Draft</button>
            </div>
            
            <div class="list-action-row" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; justify-content: space-between; width: 100%;">
                <div class="list-action-group" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                    <select id="pages-bulk-action" class="admin-input" style="padding: 0.35rem 0.5rem; height: 34px; margin: 0; width: auto; font-size: 0.85rem; flex: 1;">
                        <option value="">Tindakan Massal</option>
                        <option value="delete">Hapus</option>
                    </select>
                    <button type="button" id="pages-bulk-apply" class="btn btn-sm btn-ghost" style="border: 1px solid var(--admin-border); height: 34px;">Terapkan</button>
                </div>
                
                <div class="list-action-group" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; flex: 1; justify-content: flex-end;">
                    <input type="text" id="pages-search-input" placeholder="Cari halaman..." class="admin-input list-search-input" style="padding: 0.35rem 0.75rem; height: 34px; margin: 0; min-width: 150px; max-width: 250px; font-size: 0.85rem; flex: 1;">
                    <a href="#" id="pages-new-btn" class="btn btn-primary" style="height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0 1rem; text-decoration: none; white-space: nowrap;">+ Halaman Baru</a>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;"><input type="checkbox" id="pages-check-all" class="custom-checkbox"></th>
                <th class="sortable" data-sort="title">Judul</th>
                <th>Status</th>
                <th class="sortable" data-sort="created_at">Tanggal</th>
                <th style="width: 50px; text-align: right;"></th>
            </tr>
        </thead>
        <tbody id="pages-tbody"><tr><td colspan="5">Memuat...</td></tr></tbody>
    </table>
    </div>
    <div id="pages-pagination" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; margin-top: 1rem; border-top: 1px solid var(--admin-border);">
        <div id="pages-pagination-info" style="font-size: 0.85rem; color: var(--admin-muted);">Menampilkan 0 dari 0 halaman</div>
    </div>
</div>

<div id="pages-form-view" hidden>
    <div class="editor-toolbar" style="margin-bottom: 1.5rem; padding: 1rem; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; position: sticky; top: 0; z-index: 10;">
        <div class="editor-toolbar-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <a href="#" id="page-cancel-btn" class="btn btn-ghost" style="color: var(--admin-muted); display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.5rem;"><span style="font-size: 1.2rem; line-height: 1;">&larr;</span> Kembali</a>
            <div id="page-save-status" style="font-size: 0.8rem; color: var(--admin-muted); text-align: right;"></div>
        </div>
        <div class="editor-toolbar-actions" style="display: flex; gap: 0.75rem; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
            <a href="#" id="page-preview-btn" target="_blank" class="btn btn-ghost" style="color: var(--admin-primary); border-color: var(--admin-primary); display: none; flex: 1; text-align: center; justify-content: center;">Pratinjau</a>
            <button type="button" id="page-save-draft" class="btn btn-ghost" style="background: #f1f5f9; border-color: #e2e8f0; color: #475569; flex: 1;">Simpan Draft</button>
            <button type="button" id="page-save-publish" class="btn btn-primary" style="flex: 1; min-width: 140px;">Publikasikan</button>
        </div>
    </div>

    <form id="page-form" class="editor-layout">
        <input type="hidden" id="page-id">
        <input type="hidden" id="page-status" value="draft">

        <div class="editor-main">
            <input type="text" id="page-title" class="editor-title-input" placeholder="Ketik judul halaman di sini..." required>
            <textarea id="page-content" class="wysiwyg"></textarea>
        </div>

        <div class="editor-sidebar" style="position: sticky; top: 100px; height: fit-content; max-height: calc(100vh - 120px); overflow-y: auto; padding-right: 0.5rem; scrollbar-width: thin;">
            <details class="sidebar-card" style="padding: 1rem; cursor: pointer;" open>
                <summary style="font-weight: 600;">Pengaturan Dasar</summary>
                <div style="margin-top: 1rem; cursor: default;">
                    <label>Slug (opsional)
                        <input type="text" id="page-slug" placeholder="Otomatis dari judul">
                    </label>
                </div>
            </details>

            <details class="sidebar-card" style="padding: 1rem; cursor: pointer;">
                <summary style="font-weight: 600;">SEO (Opsional)</summary>
                <div style="margin-top: 1rem; cursor: default;">
                    <label>Meta Title
                        <input type="text" id="page-meta-title" placeholder="Gunakan Judul">
                    </label>
                    <label>Meta Description
                        <textarea id="page-meta-description" style="min-height:60px" placeholder="Gunakan isi awal konten..."></textarea>
                    </label>
                </div>
            </details>
        </div>
    </form>
</div>
