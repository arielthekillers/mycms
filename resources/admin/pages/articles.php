<div id="articles-list-view">
    <div class="toolbar-modern" style="align-items: flex-end;">
        <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%; max-width: 100%;">
            <div class="tabs-container" id="articles-tabs" style="overflow-x: auto; white-space: nowrap; padding-bottom: 0.25rem;">
                <button type="button" class="tab-btn active" data-status="">Semua Artikel</button>
                <button type="button" class="tab-btn" data-status="published">Published</button>
                <button type="button" class="tab-btn" data-status="draft">Draft</button>
                <button type="button" class="tab-btn" data-status="deleted">Sampah</button>
            </div>
            
            <div class="list-action-row" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; justify-content: space-between; width: 100%;">
                <div class="list-action-group" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                    <select id="articles-bulk-action" class="admin-input" style="padding: 0.35rem 0.5rem; height: 34px; margin: 0; width: auto; font-size: 0.85rem; flex: 1;">
                        <option value="">Tindakan Massal</option>
                        <option value="trash">Pindahkan ke Sampah</option>
                        <option value="restore">Pulihkan dari Sampah</option>
                        <option value="publish">Publikasikan</option>
                        <option value="draft">Ubah ke Draft</option>
                        <option value="delete">Hapus Permanen</option>
                    </select>
                    <button type="button" id="articles-bulk-apply" class="btn btn-sm btn-ghost" style="border: 1px solid var(--admin-border); height: 34px;">Terapkan</button>
                    <span style="color: var(--admin-border); margin: 0 0.25rem; display: none;">|</span>
                    <select id="articles-filter-category" class="admin-input" style="padding: 0.35rem 0.5rem; height: 34px; margin: 0; width: auto; font-size: 0.85rem; flex: 1;">
                        <option value="">Semua Kategori</option>
                    </select>
                </div>
                
                <div class="list-action-group" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; flex: 1; justify-content: flex-end;">
                    <input type="text" id="articles-search-input" placeholder="Cari artikel..." class="admin-input list-search-input" style="padding: 0.35rem 0.75rem; height: 34px; margin: 0; min-width: 150px; max-width: 250px; font-size: 0.85rem; flex: 1;">
                    <a href="#" id="articles-new-btn" class="btn btn-primary" style="height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0 1rem; text-decoration: none; white-space: nowrap;">+ Artikel Baru</a>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;"><input type="checkbox" id="articles-check-all" class="custom-checkbox"></th>
                    <th class="sortable" data-sort="title" style="min-width: 250px;">Judul</th>
                    <th class="sortable" data-sort="author_name" style="min-width: 150px;">Penulis</th>
                    <th>Status</th>
                    <th>Kategori</th>
                    <th class="sortable" data-sort="created_at">Tanggal</th>
                    <th style="width: 50px; text-align: right;"></th>
                </tr>
            </thead>
            <tbody id="articles-tbody"><tr><td colspan="7">Memuat...</td></tr></tbody>
        </table>
    </div>
    <div id="articles-pagination" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; margin-top: 1rem; border-top: 1px solid var(--admin-border);">
        <div id="articles-pagination-info" style="font-size: 0.85rem; color: var(--admin-muted);">Menampilkan 0-0 dari 0 artikel</div>
        <div id="articles-pagination-controls" style="display: flex; gap: 0.25rem;">
            <!-- Buttons rendered via JS -->
        </div>
    </div>
</div>

<div id="articles-form-view" hidden>
    <div class="editor-toolbar" style="margin-bottom: 1.5rem; padding: 1rem; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; position: sticky; top: 0; z-index: 10;">
        <div class="editor-toolbar-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <a href="#" id="article-cancel-btn" class="btn btn-ghost" style="color: var(--admin-muted); display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.5rem;"><span style="font-size: 1.2rem; line-height: 1;">&larr;</span> Kembali</a>
            <div id="article-save-status" style="font-size: 0.8rem; color: var(--admin-muted); text-align: right;"></div>
        </div>
        <div class="editor-toolbar-actions" style="display: flex; gap: 0.75rem; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
            <a href="#" id="article-preview-btn" target="_blank" class="btn btn-ghost" style="color: var(--admin-primary); border-color: var(--admin-primary); display: none; flex: 1; text-align: center; justify-content: center;">Pratinjau</a>
            <button type="button" id="article-save-draft" class="btn btn-ghost" style="background: #f1f5f9; border-color: #e2e8f0; color: #475569; flex: 1;">Simpan Draft</button>
            <button type="button" id="article-save-publish" class="btn btn-primary" style="flex: 1; min-width: 140px;">Publikasikan</button>
        </div>
    </div>

    <form id="article-form" class="editor-layout">
        <input type="hidden" id="article-id">
        <input type="hidden" id="article-featured-media-id">

        <div class="editor-main">
            <input type="text" id="article-title" class="editor-title-input" placeholder="Ketik judul artikel di sini..." required>
            <textarea id="article-content" class="wysiwyg"></textarea>
            <div id="article-stats" style="text-align: right; font-size: 0.8rem; color: var(--admin-muted); margin-top: 0.5rem; padding-right: 0.5rem;">0 kata &bull; 0 mnt baca</div>
            
            <div style="margin-top: 2rem; padding-top: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Riwayat Revisi</h3>
                <div id="article-revisions-list" style="cursor: default; font-size: 0.85rem; padding-right: 0.5rem;">
                    <div style="color: var(--admin-muted); text-align: left;">Belum ada riwayat.</div>
                </div>
            </div>
        </div>

        <div class="editor-sidebar" style="position: sticky; top: 100px; height: fit-content; max-height: calc(100vh - 120px); overflow-y: auto; padding-right: 0.5rem; scrollbar-width: thin;">
            <div class="sidebar-card">
                <h3>Gambar Unggulan</h3>
                <label id="article-featured-box" class="featured-image-box">
                    <span id="article-featured-text">Set Featured Image</span>
                    <input type="file" id="article-featured-upload" accept="image/*">
                </label>
                <div style="text-align:center; margin-top:0.5rem;">
                    <button type="button" id="article-featured-remove" class="btn btn-sm btn-ghost" style="display:none; color:var(--admin-danger);">Hapus Gambar</button>
                </div>
            </div>

            <div class="sidebar-card">
                <h3>Kategori</h3>
                <div style="margin-bottom: 0.5rem;">
                    <input type="text" id="article-category-search" placeholder="Cari kategori..." style="width: 100%; padding: 0.25rem 0.5rem; height: 30px; font-size: 0.85rem; border: 1px solid var(--admin-border); border-radius: 4px; box-sizing: border-box;">
                </div>
                <div id="article-categories-list" style="max-height: 200px; overflow-y: auto; margin-bottom: 1rem; padding-right: 0.5rem;">
                    <!-- Checkboxes will be rendered here -->
                </div>
                <div style="border-top: 1px solid var(--admin-border); padding-top: 1rem;">
                    <label style="font-size: 0.8rem; margin-bottom: 0.5rem; display:block;">+ Tambah Kategori Baru</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="article-new-category-input" placeholder="Nama kategori..." style="flex: 1; padding: 0 0.5rem; font-size: 0.85rem; border: 1px solid var(--admin-border); border-radius: 4px; height: 32px; box-sizing: border-box; margin: 0;">
                        <button type="button" id="article-new-category-btn" class="btn btn-sm btn-ghost" style="border: 1px solid var(--admin-border); padding: 0 0.75rem; border-radius: 4px; height: 32px; display: inline-flex; align-items: center; justify-content: center; margin: 0; box-sizing: border-box;">Tambah</button>
                    </div>
                </div>
            </div>

            <div class="sidebar-card">
                <h3>Tags</h3>
                <div id="article-tags-container" style="display: flex; flex-wrap: wrap; gap: 0.25rem; padding: 0.25rem; border: 1px solid var(--admin-border); border-radius: 4px; background: #fff; min-height: 34px; cursor: text;">
                    <input type="text" id="article-tags-input" placeholder="Ketik tag..." style="flex: 1; min-width: 60px; border: none; outline: none; background: transparent; padding: 0.1rem; font-size: 0.85rem;">
                </div>
            </div>

            <details class="sidebar-card" style="padding: 1rem; cursor: pointer;">
                <summary style="font-weight: 600;">Pengaturan Dasar</summary>
                <div style="margin-top: 1rem; cursor: default;">
                    <label>Penulis
                        <select id="article-author" class="admin-form" style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem;"></select>
                    </label>
                    <label>Slug (opsional)
                        <input type="text" id="article-slug" placeholder="Otomatis dari judul">
                    </label>
                    <label>Ringkasan
                        <textarea id="article-excerpt" style="min-height:80px" placeholder="Ambil otomatis dari isi artikel..."></textarea>
                    </label>
                </div>
            </details>

            <details class="sidebar-card" style="padding: 1rem; cursor: pointer;">
                <summary style="font-weight: 600;">SEO (Opsional)</summary>
                <div style="margin-top: 1rem; cursor: default;">
                    <!-- Live SEO Preview -->
                    <div id="seo-preview" style="border: 1px solid var(--admin-border); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #fff;">
                        <div id="seo-preview-url" style="color: #1a0dab; font-size: 0.85rem; margin-bottom: 0.25rem;">namasitus.com &rsaquo; artikel &rsaquo; ...</div>
                        <div id="seo-preview-title" style="color: #1a0dab; font-size: 1.1rem; font-family: arial, sans-serif; cursor: pointer; text-decoration: none; margin-bottom: 0.25rem;">Judul Artikel</div>
                        <div id="seo-preview-desc" style="color: #4d5156; font-size: 0.85rem; line-height: 1.4; font-family: arial, sans-serif;">Deskripsi singkat akan muncul di sini...</div>
                    </div>
                    <label>Meta Title
                        <input type="text" id="article-meta-title" placeholder="Gunakan Judul">
                    </label>
                    <label>Meta Description
                        <textarea id="article-meta-description" style="min-height:60px" placeholder="Gunakan Ringkasan"></textarea>
                    </label>
                </div>
            </details>
        </div>
    </form>
</div>
