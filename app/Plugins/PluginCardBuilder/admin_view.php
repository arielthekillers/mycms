<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Card Builder</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Buat Card dengan jumlah item tak terbatas dan opsi tampilan tingkat lanjut.</p>
    </div>
    <div>
        <button class="btn btn-primary" id="btn-create-widget">+ Buat Card Baru</button>
    </div>
</div>

<div class="editor-layout" style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1.5rem; align-items: flex-start;">
    
    <!-- Sidebar List -->
    <div class="widget-sidebar" style="flex: 0 0 280px; width: 100%;">
        <div class="admin-card" style="padding: 1rem;">
            <h3 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); margin: 0 0 1rem 0;">Daftar Card</h3>
            <div id="aw-list" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="padding: 1rem; text-align: center; color: var(--admin-muted);">Memuat...</div>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="widget-main admin-card" id="aw-workspace-empty" style="display: flex; flex-direction: column; padding: 3rem; flex: 1; min-width: 0; align-items:center; justify-content:center; text-align:center; color:var(--admin-muted); border: 2px dashed var(--admin-border);">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin-bottom:1rem; opacity:0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line></svg>
        <h3 style="font-size:1.25rem; color:var(--admin-text); margin-bottom:0.5rem;">Belum ada Card yang dipilih</h3>
        <p>Silakan pilih Card dari daftar di sebelah kiri, atau klik tombol <strong>+ Buat Card Baru</strong> di pojok kanan atas untuk mulai membuat Card.</p>
        <button class="btn btn-primary" style="margin-top:1rem;" onclick="document.getElementById('btn-create-widget').click()">Buat Card Baru Sekarang</button>
    </div>

    <div class="widget-main admin-card" id="aw-workspace" style="display: none; flex-direction: column; padding: 1.5rem; flex: 1; min-width: 0;">
        
        <form id="aw-form">
            <input type="hidden" id="aw-id">
            
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--admin-border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 style="margin:0; font-size: 1.25rem;">Pengaturan Utama</h3>
                <div>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-delete-widget" style="margin-right: 0.5rem;">Hapus</button>
                    <button type="submit" class="btn btn-primary">Simpan Card</button>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <label>
                    <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Nama Internal (Untuk Admin)</span>
                    <input type="text" id="aw-name" class="form-control" required placeholder="Cth: Promo Ramadhan">
                </label>
                <label>
                    <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Shortcode ID <span style="font-size:0.75rem; color:#888;">(Tanpa spasi)</span></span>
                    <div style="display:flex; align-items:center;">
                        <span style="background:#f1f5f9; padding:0.5rem; border:1px solid #cbd5e1; border-right:none; border-radius:4px 0 0 4px; color:#64748b;">id="</span>
                        <input type="text" id="aw-shortcode_id" class="form-control" required style="border-radius:0 4px 4px 0;">
                        <span style="background:#f1f5f9; padding:0.5rem; border:1px solid #cbd5e1; border-left:none; border-radius:0 4px 4px 0; color:#64748b;">"</span>
                    </div>
                </label>
            </div>

            <!-- Teks Pengantar -->
            <div style="margin-bottom: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                <h4 style="margin: 0 0 1rem 0;">Konten Header & Footer (Opsional)</h4>
                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                    <label>
                        <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Judul Utama Card</span>
                        <input type="text" id="aw-title" class="form-control" placeholder="Tampil besar di atas konten">
                    </label>
                    <label>
                        <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Teks Intro</span>
                        <textarea id="aw-intro" class="form-control" rows="2" placeholder="Penjelasan di bawah judul"></textarea>
                    </label>
                    <label>
                        <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Teks Extro / Footer</span>
                        <textarea id="aw-extro" class="form-control" rows="2" placeholder="Catatan kaki di bawah semua card"></textarea>
                    </label>
                </div>
            </div>

            <!-- Pengaturan Desain -->
            <div style="margin-bottom: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                <h4 style="margin: 0 0 1rem 0;">Opsi Tampilan (Desain)</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <label>
                        <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Tata Letak (Layout)</span>
                        <select id="aw-layout" class="form-control">
                            <option value="grid">Grid (Kotak-kotak ke bawah)</option>
                            <option value="scroll">Scroll (Geser Horizontal)</option>
                        </select>
                    </label>
                    <label>
                        <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Jumlah Kolom (Untuk Grid)</span>
                        <select id="aw-columns" class="form-control">
                            <option value="2">2 Kolom</option>
                            <option value="3" selected>3 Kolom</option>
                            <option value="4">4 Kolom</option>
                        </select>
                    </label>
                    <label>
                        <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Mode Warna</span>
                        <select id="aw-design_mode" class="form-control">
                            <option value="light">Terang (Default)</option>
                            <option value="dark">Gelap (Dark Mode)</option>
                            <option value="overlay">Teks di atas Gambar (Overlay)</option>
                        </select>
                    </label>
                    <label>
                        <span style="display:block; margin-bottom:0.25rem; font-weight:500;">Gaya Sudut</span>
                        <select id="aw-card_radius" class="form-control">
                            <option value="rounded">Membulat (Rounded)</option>
                            <option value="boxed">Kotak Kaku (Boxed)</option>
                        </select>
                    </label>
                </div>
            </div>

            <!-- List Card -->
            <div style="margin-bottom: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h4 style="margin: 0;">Item Card</h4>
                    <button type="button" class="btn btn-sm btn-ghost" id="btn-add-item" style="border:1px solid #cbd5e1;">+ Tambah Card</button>
                </div>
                
                <div id="aw-items-container" style="display:flex; flex-direction:column; gap:1rem;">
                    <!-- Items rendered here by JS -->
                </div>
            </div>
            
        </form>
    </div>
</div>

<!-- Modal Tambah Item -->
<div id="modal-add-item" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:1.5rem; border-radius:8px; width:100%; max-width:600px; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <h4 style="margin-top:0; margin-bottom:1rem; font-size:1.15rem;">Tambah Card Baru</h4>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <label>
                <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Judul Card</span>
                <input type="text" id="add-item-title" class="form-control">
            </label>
            <label>
                <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Gambar</span>
                <div style="display:flex; gap:0.5rem;">
                    <input type="text" id="add-item-image" class="form-control" placeholder="https:// atau /uploads/..." style="flex:1;">
                    <button type="button" class="btn btn-ghost" onclick="openCardMediaPicker('add-item-image')" style="border:1px solid #cbd5e1; padding:0 0.75rem; border-radius:4px; cursor:pointer;">Pilih</button>
                </div>
            </label>
        </div>
        <label style="display:block; margin-bottom:1rem;">
            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Deskripsi / Teks</span>
            <textarea id="add-item-desc" class="form-control" rows="2"></textarea>
        </label>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; border-top: 1px dashed #e2e8f0; padding-top: 1rem;">
            <label style="grid-column: 1 / -1;">
                <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Teks Footer (Opsional)</span>
                <input type="text" id="add-item-footer_text" class="form-control">
            </label>
            <label>
                <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Teks Tombol Footer</span>
                <input type="text" id="add-item-button_text" class="form-control" placeholder="Misal: Baca Selengkapnya">
            </label>
            <label>
                <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">URL Tombol Footer</span>
                <input type="text" id="add-item-button_url" class="form-control" placeholder="https://...">
            </label>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1.5rem; border-top: 1px dashed #e2e8f0; padding-top: 1rem;">
            <label>
                <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Badge Teks (Mis: HOT)</span>
                <input type="text" id="add-item-badge_text" class="form-control">
            </label>
            <label>
                <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Warna Badge</span>
                <select id="add-item-badge_color" class="form-control">
                    <option value="red">Merah</option>
                    <option value="green">Hijau</option>
                    <option value="dark">Gelap</option>
                </select>
            </label>
            <label>
                <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Posisi Badge</span>
                <select id="add-item-badge_position" class="form-control">
                    <option value="left">Kiri Kiri</option>
                    <option value="right">Kanan Atas</option>
                </select>
            </label>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-add-item').style.display='none'" style="border:1px solid #cbd5e1; padding:0.5rem 1rem; border-radius:4px; cursor:pointer;">Batal</button>
            <button type="button" class="btn btn-primary" id="btn-save-new-item" style="padding:0.5rem 1rem; border-radius:4px; cursor:pointer;">Tambah Card</button>
        </div>
    </div>
</div>

    </div>
</div>

<!-- Modal Media Picker -->
<div id="card-media-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div style="background:white; padding:1.5rem; border-radius:8px; width:90%; max-width:800px; height:80vh; display:flex; flex-direction:column; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h4 style="margin:0; font-size:1.15rem;">Pilih Gambar dari Media</h4>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('card-media-modal').style.display='none'" style="border:none; cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
        </div>
        <div id="card-media-grid" style="flex:1; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill, minmax(120px, 1fr)); gap:0.5rem; border:1px solid #e2e8f0; padding:0.5rem; background:#f8fafc;">
            <!-- Memuat Media... -->
        </div>
    </div>
</div>

<style>
.form-control { width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; }
.card-item-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; position: relative; }
.card-item-box h5 { margin: 0; font-size: 1rem; }
.item-header { display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none; }
.item-body { display: none; padding-top: 1rem; border-top: 1px solid #e2e8f0; margin-top: 1rem; }

</style>

<script>
function initCardBuilder() {
    const listContainer = document.getElementById('aw-list');
    const workspace = document.getElementById('aw-workspace');
    const form = document.getElementById('aw-form');
    const itemsContainer = document.getElementById('aw-items-container');
    const btnCreate = document.getElementById('btn-create-widget');
    const btnDelete = document.getElementById('btn-delete-widget');
    const btnAddItem = document.getElementById('btn-add-item');

    let widgets = [];
    let currentWidgetId = null;
    let cardItems = [];

    async function loadWidgets() {
        try {
            const res = await Admin.request('GET', '/admin/api/aw-widgets');
            widgets = res?.data || [];
            renderList();
        } catch (e) {
            listContainer.innerHTML = `<div style="color:red; padding:1rem;">Error loadWidgets: ${e.message || String(e)}</div>`;
            Admin.alert('Gagal memuat daftar Card.', true);
        }
    }

    function renderList() {
        try {
            if (widgets.length === 0) {
                listContainer.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--admin-muted);">Belum ada Card dibuat.</div>';
                return;
            }

            listContainer.innerHTML = widgets.map(w => `
                <button type="button" class="area-btn ${currentWidgetId === w.id ? 'active' : ''}" data-id="${w.id}">
                    <div style="text-align:left;">
                        <strong style="display:block;">${Admin.escapeHtml(w.name || '')}</strong>
                        <span style="font-size:0.75rem; color:var(--admin-muted);">[aw_card id="${Admin.escapeHtml(w.shortcode_id || '')}"]</span>
                    </div>
                </button>
            `).join('');

            listContainer.querySelectorAll('.area-btn').forEach(btn => {
                btn.addEventListener('click', () => loadWidget(parseInt(btn.dataset.id, 10)));
            });
        } catch (e) {
            listContainer.innerHTML = `<div style="color:red; padding:1rem;">Error rendering list: ${e.message}</div>`;
            console.error(e);
        }
    }

    async function loadWidget(id) {
        currentWidgetId = id;
        try {
            const res = await Admin.request('GET', `/admin/api/aw-widgets/${id}`);
            const w = res.data;
            
            document.getElementById('aw-id').value = w.id;
            document.getElementById('aw-name').value = w.name || '';
            document.getElementById('aw-shortcode_id').value = w.shortcode_id || '';
            document.getElementById('aw-title').value = w.title || '';
            document.getElementById('aw-intro').value = w.intro || '';
            document.getElementById('aw-extro').value = w.extro || '';
            document.getElementById('aw-layout').value = w.layout || 'grid';
            document.getElementById('aw-columns').value = w.columns || 3;
            document.getElementById('aw-design_mode').value = w.design_mode || 'light';
            document.getElementById('aw-card_radius').value = w.card_radius || 'rounded';

            cardItems = w.items || [];
            renderItems();

            btnDelete.style.display = 'inline-block';
            document.getElementById('aw-workspace-empty').style.display = 'none';
            workspace.style.display = 'flex';
            renderList();
        } catch (e) {
            Admin.alert('Gagal memuat data widget.', true);
        }
    }

    function resetForm() {
        currentWidgetId = null;
        form.reset();
        document.getElementById('aw-id').value = '';
        cardItems = [{title: 'Card 1'}]; 
        renderItems();
        btnDelete.style.display = 'none';
        document.getElementById('aw-workspace-empty').style.display = 'none';
        workspace.style.display = 'flex';
        renderList();
    }

    function renderItems() {
        if (cardItems.length === 0) {
            itemsContainer.innerHTML = '<div style="text-align:center; color:#94a3b8; padding:1rem;">Belum ada card. Klik Tambah Card.</div>';
            return;
        }

        itemsContainer.innerHTML = cardItems.map((item, i) => `
            <div class="card-item-box" data-index="${i}">
                <div class="item-header" onclick="const body = this.nextElementSibling; body.style.display = body.style.display === 'none' ? 'block' : 'none';">
                    <h5 style="display:flex; align-items:center; gap:0.5rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#94a3b8;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        Card #${i + 1}: <span style="font-weight:normal; color:#64748b;" class="display-title">${Admin.escapeHtml(item.title || '')}</span>
                    </h5>
                    <button type="button" class="btn-remove-item" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:0.25rem;" title="Hapus Card ini" onclick="event.stopPropagation();">✕</button>
                </div>
                
                <div class="item-body">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <label>
                            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Judul Card</span>
                            <input type="text" class="form-control item-title" value="${Admin.escapeHtml(item.title || '')}" required oninput="this.closest('.card-item-box').querySelector('.display-title').textContent = this.value">
                        </label>
                        <label>
                            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Gambar</span>
                            <div style="display:flex; gap:0.5rem;">
                                <input type="text" class="form-control item-image" id="item-img-${i}" value="${Admin.escapeHtml(item.image_url || '')}" placeholder="https:// atau /uploads/..." style="flex:1;">
                                <button type="button" class="btn btn-ghost" onclick="openCardMediaPicker('item-img-${i}')" style="border:1px solid #cbd5e1; padding:0 0.75rem; border-radius:4px; cursor:pointer;">Pilih</button>
                            </div>
                        </label>
                    </div>
                    <label style="display:block; margin-bottom:1rem;">
                        <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Deskripsi / Teks</span>
                        <textarea class="form-control item-desc" rows="2">${Admin.escapeHtml(item.description || '')}</textarea>
                    </label>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; border-top: 1px dashed #e2e8f0; padding-top: 1rem;">
                        <label style="grid-column: 1 / -1;">
                            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Teks Footer (Opsional)</span>
                            <input type="text" class="form-control item-footer_text" value="${Admin.escapeHtml(item.footer_text || '')}">
                        </label>
                        <label>
                            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Teks Tombol Footer</span>
                            <input type="text" class="form-control item-button_text" value="${Admin.escapeHtml(item.button_text || '')}" placeholder="Misal: Baca Selengkapnya">
                        </label>
                        <label>
                            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">URL Tombol Footer</span>
                            <input type="text" class="form-control item-button_url" value="${Admin.escapeHtml(item.button_url || '')}" placeholder="https://...">
                        </label>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; border-top: 1px dashed #e2e8f0; padding-top: 1rem;">
                        <label>
                            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Badge Teks (Mis: HOT)</span>
                            <input type="text" class="form-control item-badge_text" value="${Admin.escapeHtml(item.badge_text || '')}">
                        </label>
                        <label>
                            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Warna Badge</span>
                            <select class="form-control item-badge_color">
                                <option value="red" ${item.badge_color==='red'?'selected':''}>Merah</option>
                                <option value="green" ${item.badge_color==='green'?'selected':''}>Hijau</option>
                                <option value="dark" ${item.badge_color==='dark'?'selected':''}>Gelap</option>
                            </select>
                        </label>
                        <label>
                            <span style="display:block; margin-bottom:0.25rem; font-weight:500; font-size:0.85rem;">Posisi Badge</span>
                            <select class="form-control item-badge_position">
                                <option value="left" ${item.badge_position==='left'?'selected':''}>Kiri Kiri</option>
                                <option value="right" ${item.badge_position==='right'?'selected':''}>Kanan Atas</option>
                            </select>
                        </label>
                    </div>
                </div>
            </div>
        `).join('');

        itemsContainer.querySelectorAll('.btn-remove-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = parseInt(e.target.closest('.card-item-box').dataset.index, 10);
                saveItemsState();
                cardItems.splice(idx, 1);
                renderItems();
            });
        });
    }

    function saveItemsState() {
        const boxes = itemsContainer.querySelectorAll('.card-item-box');
        cardItems = Array.from(boxes).map(box => ({
            title: box.querySelector('.item-title').value,
            image_url: box.querySelector('.item-image').value,
            description: box.querySelector('.item-desc').value,
            footer_text: box.querySelector('.item-footer_text').value,
            button_text: box.querySelector('.item-button_text').value,
            button_url: box.querySelector('.item-button_url').value,
            badge_text: box.querySelector('.item-badge_text').value,
            badge_color: box.querySelector('.item-badge_color').value,
            badge_position: box.querySelector('.item-badge_position').value,
        }));
    }

    btnAddItem.addEventListener('click', () => {
        // Kosongkan modal input
        document.getElementById('add-item-title').value = '';
        document.getElementById('add-item-image').value = '';
        document.getElementById('add-item-desc').value = '';
        document.getElementById('add-item-footer_text').value = '';
        document.getElementById('add-item-button_text').value = '';
        document.getElementById('add-item-button_url').value = '';
        document.getElementById('add-item-badge_text').value = '';
        document.getElementById('add-item-badge_color').value = 'red';
        document.getElementById('add-item-badge_position').value = 'left';
        
        // Tampilkan modal
        document.getElementById('modal-add-item').style.display = 'flex';
    });

    document.getElementById('btn-save-new-item').addEventListener('click', () => {
        saveItemsState();
        
        const newItem = {
            title: document.getElementById('add-item-title').value || 'Card Baru',
            image_url: document.getElementById('add-item-image').value,
            description: document.getElementById('add-item-desc').value,
            footer_text: document.getElementById('add-item-footer_text').value,
            button_text: document.getElementById('add-item-button_text').value,
            button_url: document.getElementById('add-item-button_url').value,
            badge_text: document.getElementById('add-item-badge_text').value,
            badge_color: document.getElementById('add-item-badge_color').value,
            badge_position: document.getElementById('add-item-badge_position').value
        };
        
        cardItems.push(newItem);
        renderItems();
        
        // Sembunyikan modal
        document.getElementById('modal-add-item').style.display = 'none';
        
        // Otomatis buka item terakhir
        setTimeout(() => {
            const boxes = itemsContainer.querySelectorAll('.card-item-box');
            if(boxes.length > 0) {
                const lastBox = boxes[boxes.length - 1];
                const body = lastBox.querySelector('.item-body');
                if (body) body.style.display = 'block';
            }
        }, 50);
    });

    btnCreate.addEventListener('click', resetForm);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        saveItemsState();

        const payload = {
            name: document.getElementById('aw-name').value,
            shortcode_id: document.getElementById('aw-shortcode_id').value,
            title: document.getElementById('aw-title').value,
            intro: document.getElementById('aw-intro').value,
            extro: document.getElementById('aw-extro').value,
            layout: document.getElementById('aw-layout').value,
            columns: document.getElementById('aw-columns').value,
            design_mode: document.getElementById('aw-design_mode').value,
            card_radius: document.getElementById('aw-card_radius').value,
            items: cardItems
        };

        try {
            if (currentWidgetId) {
                await Admin.request('PUT', `/admin/api/aw-widgets/${currentWidgetId}`, payload);
                Admin.alert('Berhasil diperbarui!', false);
            } else {
                await Admin.request('POST', `/admin/api/aw-widgets`, payload);
                Admin.alert('Widget berhasil dibuat!', false);
            }
            loadWidgets();
            workspace.style.display = 'none'; 
            document.getElementById('aw-workspace-empty').style.display = 'flex';
        } catch (err) {
            Admin.alert(err.message, true);
        }
    });

    btnDelete.addEventListener('click', async () => {
        if (!currentWidgetId) return;
        if (!await Admin.confirmDelete('widget ini beserta seluruh cardnya')) return;

        try {
            await Admin.request('DELETE', `/admin/api/aw-widgets/${currentWidgetId}`);
            workspace.style.display = 'none';
            document.getElementById('aw-workspace-empty').style.display = 'flex';
            loadWidgets();
        } catch (err) {
            Admin.alert(err.message, true);
        }
    });

    loadWidgets();
}

if (typeof Admin !== 'undefined') {
    initCardBuilder();
} else {
    document.addEventListener('DOMContentLoaded', initCardBuilder);
}

// Fungsi global untuk media picker
let currentTargetInputId = null;
window.openCardMediaPicker = function(inputId) {
    currentTargetInputId = inputId;
    const modal = document.getElementById('card-media-modal');
    const grid = document.getElementById('card-media-grid');
    
    modal.style.display = 'flex';
    grid.innerHTML = '<div style="padding:1rem; grid-column:1/-1; text-align:center;">Memuat data...</div>';
    
    Admin.request('GET', '/admin/media')
        .then(res => {
            grid.innerHTML = '';
            if (!res || !res.data) {
                grid.innerHTML = '<p style="color:#ef4444;grid-column: 1/-1;">Gagal memuat media.</p>';
                return;
            }
            const images = res.data.filter(m => m.mime_type.startsWith('image/'));
            if(images.length === 0) {
                grid.innerHTML = '<p style="grid-column: 1/-1; padding:1rem; text-align:center;">Belum ada gambar di media.</p>';
                return;
            }

            images.forEach(media => {
                const imgDiv = document.createElement('div');
                imgDiv.style.cssText = `height: 100px; background-image: url(/uploads/${media.disk_path}); background-size: cover; background-position: center; cursor: pointer; border-radius: 4px; border: 2px solid transparent;`;
                imgDiv.title = media.file_name;
                
                imgDiv.addEventListener('click', () => {
                    document.getElementById(currentTargetInputId).value = `/uploads/${media.disk_path}`;
                    // Trigger input event to update any live bindings
                    document.getElementById(currentTargetInputId).dispatchEvent(new Event('input'));
                    modal.style.display = 'none';
                });

                imgDiv.addEventListener('mouseenter', () => imgDiv.style.borderColor = 'var(--admin-primary, #3b82f6)');
                imgDiv.addEventListener('mouseleave', () => imgDiv.style.borderColor = 'transparent');

                grid.appendChild(imgDiv);
            });
        })
        .catch(err => {
            grid.innerHTML = `<p style="color:#ef4444;grid-column: 1/-1;">Error: ${err.message}</p>`;
        });
};
</script>
