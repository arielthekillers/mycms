<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Manajer Slider & Banner</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Kelola slideshow promosi dan banner dinamis Anda.</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="showSliderForm()">+ Buat Slider Baru</button>
    </div>
</div>

<div id="sliders-list" class="media-grid" style="margin-top: 2rem;">
    <!-- Rendered via JS -->
</div>

<!-- Modal Form -->
<div id="slider-modal" class="admin-modal-overlay">
    <div class="admin-modal" style="width: 700px; max-width: 95%;">
        <h3 id="modal-title">Buat Slider Baru</h3>
        
        <div class="setting-group" style="margin-bottom: 1rem;">
            <label style="display:block; font-weight:600; margin-bottom:0.5rem; font-size:0.9rem;">ID Unik (Tanpa Spasi)</label>
            <input type="text" id="slider-id" class="admin-input" placeholder="contoh: promo-beranda">
            <small style="color:var(--admin-muted); font-size:0.8rem;">Gunakan ini untuk shortcode: [slider id="promo-beranda"]</small>
        </div>
        
        <div class="setting-group" style="margin-bottom: 1rem;">
            <label style="display:block; font-weight:600; margin-bottom:0.5rem; font-size:0.9rem;">Nama / Deskripsi Internal</label>
            <input type="text" id="slider-name" class="admin-input" placeholder="contoh: Promo PPDB Utama">
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; margin-bottom: 1rem; border-bottom: 2px solid var(--admin-border); padding-bottom: 0.5rem;">
            <h4 style="margin:0;">Daftar Slide</h4>
            <button class="btn btn-sm btn-ghost" onclick="addSlide()">+ Tambah Slide</button>
        </div>
        
        <div id="slides-container" style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
            <!-- Slides here -->
        </div>

        <div class="admin-modal-actions" style="margin-top: 2rem;">
            <button class="btn btn-ghost" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveSlider()">Simpan Slider</button>
        </div>
    </div>
</div>

<!-- Template for a single slide -->
<template id="slide-template">
    <div class="widget-item slide-item" style="display: block; padding: 1rem; margin-bottom: 1rem; background: #fff; border-left: 4px solid var(--admin-primary);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
            <strong class="slide-title">Slide #<span class="index"></span></strong>
            <button class="media-delete-btn" onclick="this.closest('.slide-item').remove()" style="position:static; opacity:1;"><i class="fas fa-trash"></i></button>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label style="font-size: 0.85rem; font-weight: 500;">Tipe Slide</label>
                <select class="admin-input slide-type" onchange="toggleSlideFields(this)">
                    <option value="image">Gambar Penuh (Banner)</option>
                    <option value="hero">Gambar + Teks & Tombol (Hero)</option>
                </select>
            </div>
            <div>
                <label style="font-size: 0.85rem; font-weight: 500;">URL / Path Gambar Latar</label>
                <input type="text" class="admin-input slide-image" placeholder="/uploads/gambar.jpg">
            </div>
        </div>
        
        <div class="hero-fields" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--admin-border);">
            <div style="margin-bottom: 0.75rem;">
                <label style="font-size: 0.85rem; font-weight: 500;">Judul Besar (Heading)</label>
                <input type="text" class="admin-input slide-heading">
            </div>
            <div style="margin-bottom: 0.75rem;">
                <label style="font-size: 0.85rem; font-weight: 500;">Sub-judul / Deskripsi</label>
                <textarea class="admin-input slide-desc" style="min-height: 60px;"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-size: 0.85rem; font-weight: 500;">Teks Tombol</label>
                    <input type="text" class="admin-input slide-btn-text" placeholder="Selengkapnya...">
                </div>
                <div>
                    <label style="font-size: 0.85rem; font-weight: 500;">Tautan Tombol (URL)</label>
                    <input type="text" class="admin-input slide-btn-link" placeholder="/pendaftaran">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let currentSliders = [];

async function loadSliders() {
    try {
        const res = await Admin.request('GET', '/admin/api/sliders');
        currentSliders = res.data;
        renderSliderList();
    } catch(e) {
        Admin.alert('Gagal memuat slider: ' + e.message, true);
    }
}

function renderSliderList() {
    const container = document.getElementById('sliders-list');
    container.innerHTML = '';
    
    if (currentSliders.length === 0) {
        container.innerHTML = '<p style="color:var(--admin-muted);">Belum ada slider yang dibuat.</p>';
        return;
    }
    
    currentSliders.forEach(slider => {
        const card = document.createElement('div');
        card.className = 'widget-area';
        card.style.marginBottom = '0';
        
        const shortcode = `[slider id="${slider.id}"]`;
        
        card.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h3 style="margin:0 0 0.25rem 0; font-size:1.1rem; color:var(--admin-text);">${Admin.escapeHtml(slider.name)}</h3>
                    <code style="font-size:0.8rem; background:#f1f5f9; padding:2px 6px; border-radius:4px;">${shortcode}</code>
                    <p style="margin:0.5rem 0 0 0; font-size:0.85rem; color:var(--admin-muted);">${slider.slides ? slider.slides.length : 0} slide</p>
                </div>
                <div style="display:flex; gap:0.5rem;">
                    <button class="btn btn-sm btn-ghost" onclick="editSlider('${slider.id}')"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteSlider('${slider.id}')"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function toggleSlideFields(selectEl) {
    const item = selectEl.closest('.slide-item');
    const heroFields = item.querySelector('.hero-fields');
    if (selectEl.value === 'hero') {
        heroFields.style.display = 'block';
    } else {
        heroFields.style.display = 'none';
    }
}

function addSlide(data = null) {
    const tpl = document.getElementById('slide-template').content.cloneNode(true);
    const container = document.getElementById('slides-container');
    
    if (data) {
        tpl.querySelector('.slide-type').value = data.type || 'image';
        tpl.querySelector('.slide-image').value = data.image || '';
        tpl.querySelector('.slide-heading').value = data.title || '';
        tpl.querySelector('.slide-desc').value = data.description || '';
        tpl.querySelector('.slide-btn-text').value = data.button_text || '';
        tpl.querySelector('.slide-btn-link').value = data.button_link || '';
    }
    
    container.appendChild(tpl);
    
    // update index
    const items = container.querySelectorAll('.slide-item');
    const lastItem = items[items.length - 1];
    lastItem.querySelector('.index').textContent = items.length;
    
    toggleSlideFields(lastItem.querySelector('.slide-type'));
}

function showSliderForm(data = null) {
    document.getElementById('slides-container').innerHTML = '';
    
    if (data) {
        document.getElementById('modal-title').textContent = 'Edit Slider';
        document.getElementById('slider-id').value = data.id;
        document.getElementById('slider-id').disabled = true;
        document.getElementById('slider-name').value = data.name;
        
        if (data.slides) {
            data.slides.forEach(slide => addSlide(slide));
        }
    } else {
        document.getElementById('modal-title').textContent = 'Buat Slider Baru';
        document.getElementById('slider-id').value = '';
        document.getElementById('slider-id').disabled = false;
        document.getElementById('slider-name').value = '';
        addSlide(); // 1 default slide
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
        Admin.alert('Slider dihapus.');
        loadSliders();
    } catch(e) {
        Admin.alert(e.message, true);
    }
}

function closeModal() {
    document.getElementById('slider-modal').classList.remove('show');
}

async function saveSlider() {
    const id = document.getElementById('slider-id').value;
    const name = document.getElementById('slider-name').value;
    
    if (!id || !name) {
        Admin.alert('ID dan Nama harus diisi', true);
        return;
    }
    
    const slideItems = document.querySelectorAll('.slide-item');
    const slides = [];
    
    slideItems.forEach(item => {
        slides.push({
            type: item.querySelector('.slide-type').value,
            image: item.querySelector('.slide-image').value,
            title: item.querySelector('.slide-heading').value,
            description: item.querySelector('.slide-desc').value,
            button_text: item.querySelector('.slide-btn-text').value,
            button_link: item.querySelector('.slide-btn-link').value
        });
    });
    
    const payload = { id, name, slides };
    
    try {
        await Admin.request('POST', '/admin/api/sliders', payload);
        Admin.alert('Slider berhasil disimpan');
        closeModal();
        loadSliders();
    } catch (e) {
        Admin.alert(e.message, true);
    }
}

// Init
document.addEventListener('DOMContentLoaded', loadSliders);
</script>
