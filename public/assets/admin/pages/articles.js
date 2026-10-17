(function () {
    const listView = document.getElementById('articles-list-view');
    const formView = document.getElementById('articles-form-view');
    const tbody = document.getElementById('articles-tbody');
    const form = document.getElementById('article-form');
    const tabs = document.querySelectorAll('#articles-tabs .tab-btn');
    let currentStatus = '';
    let currentCategory = '';
    let searchQuery = '';
    let currentPage = 1;
    let sortBy = 'created_at';
    let sortDir = 'desc';
    const itemsPerPage = 20;
    
    let currentTags = [];
    
    // Global listener to close dropdowns
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown.show').forEach(d => d.classList.remove('show'));
        }
    });


    function updateSortHeaders() {
        document.querySelectorAll('th.sortable').forEach(th => {
            th.classList.remove('asc', 'desc');
            if (th.dataset.sort === sortBy) {
                th.classList.add(sortDir);
            }
        });
    }
    updateSortHeaders();

    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const sort = th.dataset.sort;
            if (sortBy === sort) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy = sort;
                sortDir = 'asc';
            }
            updateSortHeaders();
            currentPage = 1;
            loadList();
        });
    });

    const filterCategoryEl = document.getElementById('articles-filter-category');

    async function loadCategoryFilterOptions() {
        try {
            const res = await Admin.request('GET', '/admin/categories');
            let optionsHtml = '<option value="">Semua Kategori</option>';
            res.data.forEach(c => {
                optionsHtml += `<option value="${c.id}">${Admin.escapeHtml(c.name)}</option>`;
            });
            if (filterCategoryEl) filterCategoryEl.innerHTML = optionsHtml;
        } catch (e) {
            console.error('Failed to load categories for filter', e);
        }
    }

    if (filterCategoryEl) {
        filterCategoryEl.addEventListener('change', (e) => {
            currentCategory = e.target.value;
            currentPage = 1;
            loadList();
        });
    }

    function updateTabCounts(counts) {
        tabs.forEach(tab => {
            const status = tab.dataset.status || 'all';
            let label = '';
            if (status === 'all') label = 'Semua Artikel';
            else if (status === 'published') label = 'Published';
            else if (status === 'draft') label = 'Draft';
            else if (status === 'deleted') label = 'Sampah';
            
            const count = counts[status] || 0;
            tab.textContent = `${label} (${count})`;
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Auto scroll to make tab fully visible on mobile
            e.target.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            
            currentStatus = tab.dataset.status;
            currentPage = 1;
            document.getElementById('articles-check-all').checked = false;
            loadList();
        });
    });

    let searchTimeout;
    document.getElementById('articles-search-input').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchQuery = e.target.value.trim();
            currentPage = 1;
            loadList();
        }, 300);
    });

    function showList() { listView.hidden = false; formView.hidden = true; loadList(); }
    function showForm() { listView.hidden = true; formView.hidden = false; }

    async function loadList() {
        let query = '?limit=' + itemsPerPage + '&offset=' + ((currentPage - 1) * itemsPerPage);
        if (currentStatus) query += '&status=' + currentStatus;
        if (searchQuery) query += '&search=' + encodeURIComponent(searchQuery);
        if (currentCategory) query += '&category_id=' + currentCategory;
        query += '&sort_by=' + sortBy + '&sort_dir=' + sortDir;

        const skeletonHtml = Array.from({length: 3}).map(() => `
            <tr class="skeleton-row">
                <td style="text-align: center;"><div class="skeleton-box" style="width: 18px; margin: 0 auto;"></div></td>
                <td><div class="skeleton-box" style="width: 60%;"></div></td>
                <td><div class="skeleton-box" style="width: 80px;"></div></td>
                <td><div class="skeleton-box" style="width: 120px;"></div></td>
                <td><div class="skeleton-box" style="width: 90px;"></div></td>
                <td><div class="skeleton-box" style="width: 100px; margin-left: auto;"></div></td>
            </tr>
        `).join('');
        tbody.innerHTML = skeletonHtml;

        try {
            const res = await Admin.request('GET', `/admin/articles${query}`);
            const rows = res.data;
            if (res.counts) updateTabCounts(res.counts);

            if (rows.length === 0) {
                let emptyStateHtml = '';
                const totalArticles = res.counts ? res.counts.all : 0;
                
                if (currentStatus === 'deleted') {
                    emptyStateHtml = `
                        <div class="empty-state">
                            <div class="empty-state-icon" style="opacity: 0.5;">🗑️</div>
                            <h4 class="empty-state-title">Sampah Kosong</h4>
                            <p class="empty-state-desc">Tidak ada artikel yang dihapus.</p>
                        </div>
                    `;
                } else if (totalArticles > 0) {
                    let title = 'Belum ada artikel';
                    let desc = 'Tidak ada artikel yang sesuai dengan pencarian atau filter Anda.';
                    
                    if (currentStatus === 'published') {
                        title = 'Belum ada artikel terbit';
                        desc = 'Semua artikel Anda saat ini berstatus Draft.';
                    } else if (currentStatus === 'draft') {
                        title = 'Tidak ada Draft';
                        desc = 'Semua artikel Anda saat ini berstatus Published.';
                    }
                    
                    emptyStateHtml = `
                        <div class="empty-state">
                            <div class="empty-state-icon" style="opacity: 0.5;">📄</div>
                            <h4 class="empty-state-title">${title}</h4>
                            <p class="empty-state-desc">${desc}</p>
                        </div>
                    `;
                } else {
                    emptyStateHtml = `
                        <div class="empty-state">
                            <div class="empty-state-icon">📄</div>
                            <h4 class="empty-state-title">Belum ada artikel</h4>
                            <p class="empty-state-desc">Mulai menulis dan publikasikan karya pertama Anda hari ini.</p>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('articles-new-btn').click()">+ Buat Artikel Pertama</button>
                        </div>
                    `;
                }

                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="padding: 0;">
                            ${emptyStateHtml}
                        </td>
                    </tr>
                `;
                renderPagination(0);
                return;
            }
            tbody.innerHTML = rows.map((a) => {
                const authorName = a.author_name || 'System';
                const initials = authorName.substring(0, 2).toUpperCase();
                return `
                <tr class="article-row" data-id="${a.id}" style="cursor: pointer;">
                    <td style="text-align: center;"><input type="checkbox" class="article-cb custom-checkbox" value="${a.id}"></td>
                    <td><span style="font-weight: 600; font-size: 1rem; color: #1e293b;">${Admin.escapeHtml(a.title)}</span></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            ${a.author_avatar_url 
                                ? `<img src="/uploads/${Admin.escapeHtml(a.author_avatar_url)}" alt="${Admin.escapeHtml(authorName)}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 1px solid var(--admin-border-light, #e2e8f0);">` 
                                : `<div style="width: 28px; height: 28px; border-radius: 50%; background: var(--admin-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600;">${Admin.escapeHtml(initials)}</div>`
                            }
                            <span style="font-size: 0.85rem; color: var(--admin-muted);">${Admin.escapeHtml(authorName)}</span>
                        </div>
                    </td>
                    <td><span class="badge badge-${a.status}">${Admin.escapeHtml(a.status)}</span></td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                            ${a.categories && a.categories.length > 0
                                ? a.categories.map(c => `<span class="badge" style="background: #e0f2fe; color: #0369a1;">${Admin.escapeHtml(c.name)}</span>`).join('')
                                : '<span class="badge badge-uncategorized">Uncategorized</span>'
                            }
                        </div>
                    </td>
                    <td style="font-size: 0.85rem; color: var(--admin-muted);">${a.created_at ? new Date(a.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }) : '-'}</td>
                    <td style="text-align: right;">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-ghost dropdown-toggle" style="padding: 0.2rem; border: none; background: transparent; color: var(--admin-muted);" type="button">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                            </button>
                            <div class="dropdown-menu">
                                ${a.status === 'deleted' ? `
                                    <button class="dropdown-item" data-restore="${a.id}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 14 4 9 9 4"></polyline><path d="M20 20v-7a4 4 0 0 0-4-4H4"></path></svg> Restore
                                    </button>
                                    <button class="dropdown-item text-danger" data-force-delete="${a.id}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg> Hapus Permanen
                                    </button>
                                ` : `
                                    <button class="dropdown-item" data-edit="${a.id}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Edit
                                    </button>
                                    ${a.status !== 'published' ? `
                                    <button class="dropdown-item" data-publish="${a.id}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Publish
                                    </button>` : ''}
                                    <button class="dropdown-item text-danger" data-delete="${a.id}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Hapus
                                    </button>
                                `}
                            </div>
                        </div>
                    </td>
                </tr>
            `;
            }).join('');
            
            renderPagination(res.total || 0);
            document.getElementById('articles-check-all').checked = false;
            
            // Add row click listener for edit
            document.querySelectorAll('.article-row').forEach(row => {
                row.addEventListener('click', (e) => {
                    // Prevent row click if clicking on buttons or checkboxes
                    if (e.target.closest('button') || e.target.closest('input[type="checkbox"]') || e.target.closest('a')) {
                        return;
                    }
                    const id = row.dataset.id;
                    if (id) {
                        loadIntoForm(id).catch((err) => Admin.alert(err.message, true));
                    }
                });
            });
            
            // Add dropdown toggle listeners
            document.querySelectorAll('.dropdown-toggle').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation(); // prevent row click and document click
                    const dropdown = btn.closest('.dropdown');
                    const wasShow = dropdown.classList.contains('show');
                    
                    document.querySelectorAll('.dropdown.show').forEach(d => {
                        d.classList.remove('show');
                    });
                    
                    if (!wasShow) {
                        dropdown.classList.add('show');
                    }
                });
            });
            
            // Smart Check All logic
            const checkAllBtn = document.getElementById('articles-check-all');
            const rowCheckboxes = document.querySelectorAll('.article-cb');
            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const allChecked = document.querySelectorAll('.article-cb:checked').length === rowCheckboxes.length;
                    checkAllBtn.checked = allChecked;
                });
            });
            
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 2rem; color: var(--admin-danger);">Gagal memuat: ${Admin.escapeHtml(e.message)}</td></tr>`;
        }
    }

    function renderPagination(total) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const infoEl = document.getElementById('articles-pagination-info');
        const controlsEl = document.getElementById('articles-pagination-controls');
        
        if (total === 0) {
            infoEl.textContent = 'Menampilkan 0-0 dari 0 artikel';
            controlsEl.innerHTML = '';
            return;
        }

        const start = ((currentPage - 1) * itemsPerPage) + 1;
        const end = Math.min(currentPage * itemsPerPage, total);
        infoEl.textContent = `Menampilkan ${start}-${end} dari ${total} artikel`;

        let html = '';
        if (currentPage > 1) {
            html += `<button class="btn btn-sm btn-ghost" data-page="${currentPage - 1}">&laquo; Prev</button>`;
        }
        if (currentPage < totalPages) {
            html += `<button class="btn btn-sm btn-ghost" data-page="${currentPage + 1}">Next &raquo;</button>`;
        }
        controlsEl.innerHTML = html;
        
        controlsEl.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                currentPage = parseInt(e.target.dataset.page);
                loadList();
            });
        });
    }

    function setFeaturedImagePreview(url, id) {
        const box = document.getElementById('article-featured-box');
        const removeBtn = document.getElementById('article-featured-remove');
        const hiddenInput = document.getElementById('article-featured-media-id');
        let img = box.querySelector('img');

        if (url) {
            if (!img) {
                img = document.createElement('img');
                box.appendChild(img);
            }
            img.src = url;
            box.classList.add('has-image');
            removeBtn.style.display = 'inline-block';
            hiddenInput.value = id;
        } else {
            if (img) img.remove();
            box.classList.remove('has-image');
            removeBtn.style.display = 'none';
            hiddenInput.value = '';
            document.getElementById('article-featured-upload').value = '';
        }
    }

    async function loadCategoriesIntoForm(selectedIds = []) {
        try {
            const res = await Admin.request('GET', '/admin/categories');
            const listEl = document.getElementById('article-categories-list');
            if (res.data.length === 0) {
                listEl.innerHTML = '<div style="color:var(--admin-muted); font-size: 0.85rem; padding: 0.5rem 0;">Belum ada kategori.</div>';
                return;
            }
            
            listEl.innerHTML = res.data.map(c => `
                <div class="category-item" data-name="${Admin.escapeHtml(c.name)}" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; padding: 0.25rem 0; border-bottom: 1px solid var(--admin-border-light, #f1f5f9);">
                    <label style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; margin: 0; cursor: pointer; flex: 1; word-break: break-word; line-height: 1.4;">
                        <input type="checkbox" name="article_category_ids[]" value="${c.id}" ${selectedIds.includes(c.id) ? 'checked' : ''} style="width: auto !important; margin: 0.2rem 0 0 0 !important; flex-shrink: 0;">
                        <span style="flex: 1;">${Admin.escapeHtml(c.name)}</span>
                    </label>
                    <button type="button" class="btn btn-sm btn-ghost" style="padding: 0 0.25rem; color: var(--admin-danger); font-size: 1.1rem; line-height: 1; height: auto; border: none; margin-left: 0.5rem; flex-shrink: 0;" data-delete-category="${c.id}" data-name="${Admin.escapeHtml(c.name)}" title="Hapus Kategori">&times;</button>
                </div>
            `).join('');
        } catch (err) {
            Admin.alert('Gagal memuat kategori: ' + err.message, true);
        }
    }

    async function loadIntoForm(id) {
        const res = await Admin.request('GET', `/admin/articles/${id}`);
        const a = res.data;
        document.getElementById('article-id').value = a.id;
        document.getElementById('article-title').value = a.title;
        document.getElementById('article-slug').value = a.slug;
        document.getElementById('article-excerpt').value = a.excerpt || '';
        Admin.setWysiwygContent('article-content', a.content || '');
        document.getElementById('article-meta-title').value = a.meta_title || '';
        document.getElementById('article-meta-description').value = a.meta_description || '';
        setFeaturedImagePreview(a.featured_image_path ? '/uploads/' + a.featured_image_path : null, a.featured_media_id);
        
        if (a.tags) {
            currentTags = a.tags.map(t => t.name);
        } else {
            currentTags = [];
        }
        if (typeof renderTags === 'function') renderTags();
        
        const authorSelect = document.getElementById('article-author');
        if (authorSelect) {
            authorSelect.value = a.author_id || '';
        }

        const selectedCatIds = (a.categories || []).map(c => c.id);
        await loadCategoriesIntoForm(selectedCatIds);
        
        const btnPublish = document.getElementById('article-save-publish');
        if (a.status === 'published') {
            btnPublish.textContent = 'Perbarui (Published)';
        } else {
            btnPublish.textContent = 'Publikasikan';
        }

        const previewBtn = document.getElementById('article-preview-btn');
        if (a.slug) {
            previewBtn.href = `/${a.slug}?preview=1`;
            previewBtn.style.display = 'inline-block';
        } else {
            previewBtn.style.display = 'none';
        }
        
        showForm();
        setTimeout(() => {
            if (typeof updateSeoPreview === 'function') updateSeoPreview();
            if (typeof updateWordCount === 'function') updateWordCount();
        }, 300);

        await loadRevisions(a.id);
    }

    function resetForm() {
        form.reset();
        document.getElementById('article-id').value = '';
        document.getElementById('article-save-publish').textContent = 'Publikasikan';
        
        currentTags = [];
        if (typeof renderTags === 'function') renderTags();
        const authorSelect = document.getElementById('article-author');
        if (authorSelect) authorSelect.value = '';

        setFeaturedImagePreview(null, null);
    }

    document.getElementById('articles-new-btn').addEventListener('click', async (e) => {
        e.preventDefault();
        document.getElementById('article-save-status').textContent = '';
        try {
            const res = await Admin.request('POST', '/admin/articles', {
                title: 'Draft Tanpa Judul',
                status: 'draft'
            });
            await loadIntoForm(res.data.id);
        } catch (err) {
            Admin.alert(err.message, true);
        }
    });

    let autosaveTimer = null;
    let isSaving = false;

    async function triggerAutosave() {
        if (isSaving) return;
        const id = document.getElementById('article-id').value;
        if (!id) return;

        const titleVal = document.getElementById('article-title').value.trim();
        if (!titleVal) return;

        const statusEl = document.getElementById('article-save-status');
        statusEl.textContent = 'Menyimpan...';
        isSaving = true;

        const payload = {
            title: titleVal,
            slug: document.getElementById('article-slug').value || undefined,
            excerpt: document.getElementById('article-excerpt').value,
            content: document.getElementById('article-content').value,
            meta_title: document.getElementById('article-meta-title').value || undefined,
            meta_description: document.getElementById('article-meta-description').value || undefined,
            featured_media_id: document.getElementById('article-featured-media-id').value || undefined,
            category_ids: getSelectedCategoryIds(),
        };

        try {
            await Admin.request('PUT', `/admin/articles/${id}`, payload);
            const now = new Date();
            statusEl.textContent = `Tersimpan otomatis pada ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
        } catch (err) {
            statusEl.textContent = 'Gagal autosave';
        } finally {
            isSaving = false;
        }
    }

    function scheduleAutosave() {
        if (autosaveTimer) clearTimeout(autosaveTimer);
        const statusEl = document.getElementById('article-save-status');
        if (statusEl.textContent !== 'Menyimpan...') {
            statusEl.textContent = 'Menunggu diketik...';
        }
        autosaveTimer = setTimeout(() => {
            triggerAutosave();
        }, 2500);
    }

    function updateSeoPreview() {
        const titleEl = document.getElementById('seo-preview-title');
        const descEl = document.getElementById('seo-preview-desc');
        const urlEl = document.getElementById('seo-preview-url');

        if (!titleEl || !descEl || !urlEl) return;

        let title = document.getElementById('article-meta-title').value.trim();
        if (!title) title = document.getElementById('article-title').value.trim();
        if (!title) title = 'Judul Artikel';

        let desc = document.getElementById('article-meta-description').value.trim();
        if (!desc) desc = document.getElementById('article-excerpt').value.trim();
        if (!desc) {
            if (typeof tinymce !== 'undefined' && tinymce.get('article-content')) {
                desc = tinymce.get('article-content').getContent({format: 'text'}).substring(0, 150);
            } else {
                desc = document.getElementById('article-content').value.replace(/<[^>]+>/g, '').substring(0, 150);
            }
        }
        if (!desc) desc = 'Deskripsi singkat akan muncul di sini...';

        titleEl.textContent = title;
        descEl.textContent = desc;
        
        let slug = document.getElementById('article-slug').value.trim();
        urlEl.innerHTML = `namasitus.com &rsaquo; artikel &rsaquo; <span style="color:#4d5156">${Admin.escapeHtml(slug || '...')}</span>`;
    }

    function updateWordCount() {
        let text = '';
        if (typeof tinymce !== 'undefined' && tinymce.get('article-content')) {
            text = tinymce.get('article-content').getContent({format: 'text'});
        } else {
            text = document.getElementById('article-content').value.replace(/<[^>]+>/g, '');
        }
        
        const words = text.trim() ? text.trim().split(/\s+/).length : 0;
        const readTime = Math.ceil(words / 200); // 200 words per min
        const statsEl = document.getElementById('article-stats');
        if (statsEl) {
            statsEl.innerHTML = `${words} kata &bull; ${readTime} mnt baca`;
        }
    }

    form.addEventListener('input', () => {
        scheduleAutosave();
        updateSeoPreview();
        updateWordCount();
    });

    if (typeof tinymce !== 'undefined') {
        tinymce.on('AddEditor', function(e) {
            e.editor.on('input change keyup', function() {
                scheduleAutosave();
                updateSeoPreview();
                updateWordCount();
            });
        });
    }

    const titleInput = document.getElementById('article-title');
    titleInput.addEventListener('focus', function() {
        if (this.value === 'Draft Tanpa Judul') {
            this.value = '';
        }
    });
    titleInput.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            this.value = 'Draft Tanpa Judul';
            scheduleAutosave();
        }
    });

    document.getElementById('article-cancel-btn').addEventListener('click', (e) => {
        e.preventDefault();
        showList();
    });

    tbody.addEventListener('click', async (e) => {
        const target = e.target;

        if (target.dataset.edit) {
            loadIntoForm(target.dataset.edit).catch((err) => Admin.alert(err.message, true));
        } else if (target.dataset.publish) {
            try {
                await Admin.request('POST', `/admin/articles/${target.dataset.publish}/publish`);
                Admin.alert('Artikel dipublikasikan.');
                loadList();
            } catch (err) { Admin.alert(err.message, true); }
        } else if (target.dataset.delete) {
            if (!await Admin.confirmDelete('Apakah Anda yakin ingin menghapus artikel ini?')) return;
            try {
                await Admin.request('DELETE', `/admin/articles/${target.dataset.delete}`);
                Admin.alert('Artikel dipindahkan ke sampah.');
                loadList();
            } catch (err) { Admin.alert(err.message, true); }
        } else if (target.dataset.restore) {
            try {
                await Admin.request('PUT', `/admin/articles/${target.dataset.restore}`, { status: 'draft' });
                Admin.alert('Artikel dipulihkan.');
                loadList();
            } catch (err) { Admin.alert(err.message, true); }
        } else if (target.dataset.forceDelete) {
            if (!await Admin.confirmDelete('Apakah Anda yakin ingin menghapus permanen artikel ini?')) return;
            try {
                await Admin.request('DELETE', `/admin/articles/${target.dataset.forceDelete}?force=1`);
                Admin.alert('Artikel dihapus permanen.');
                loadList();
            } catch (err) { Admin.alert(err.message, true); }
        }
    });

    document.getElementById('articles-check-all').addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        document.querySelectorAll('.article-cb').forEach(cb => {
            cb.checked = isChecked;
        });
    });

    document.getElementById('articles-bulk-apply').addEventListener('click', async () => {
        const action = document.getElementById('articles-bulk-action').value;
        if (!action) return;

        const checkedBoxes = document.querySelectorAll('.article-cb:checked');
        if (checkedBoxes.length === 0) {
            Admin.alert('Pilih setidaknya satu artikel untuk tindakan ini.', true);
            return;
        }

        const confirmMsg = `Apakah Anda yakin ingin memproses ${checkedBoxes.length} artikel yang dipilih?`;
        if (!await Admin.confirmDelete(confirmMsg)) return;

        const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        const promises = [];

        ids.forEach(id => {
            if (action === 'trash') {
                promises.push(Admin.request('DELETE', `/admin/articles/${id}`));
            } else if (action === 'delete') {
                promises.push(Admin.request('DELETE', `/admin/articles/${id}?force=1`));
            } else if (action === 'restore') {
                promises.push(Admin.request('PUT', `/admin/articles/${id}`, { status: 'draft' }));
            } else if (action === 'publish') {
                promises.push(Admin.request('POST', `/admin/articles/${id}/publish`));
            } else if (action === 'draft') {
                promises.push(Admin.request('PUT', `/admin/articles/${id}`, { status: 'draft' }));
            }
        });

        try {
            await Promise.all(promises);
            Admin.alert(`Berhasil memproses ${checkedBoxes.length} artikel.`, false);
            document.getElementById('articles-check-all').checked = false;
            document.getElementById('articles-bulk-action').value = '';
            loadList();
        } catch (err) {
            Admin.alert('Beberapa tindakan gagal: ' + err.message, true);
            loadList();
        }
    });

    const featuredBox = document.getElementById('article-featured-box');
    const featuredInput = document.getElementById('article-featured-upload');

    featuredInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await Admin.request('POST', '/admin/media', formData);
            setFeaturedImagePreview('/uploads/' + res.data.disk_path, res.data.id);
        } catch (err) {
            Admin.alert(err.message, true);
        }
    });

    featuredBox.addEventListener('dragover', (e) => {
        e.preventDefault();
        featuredBox.style.borderColor = 'var(--admin-primary)';
        featuredBox.style.background = '#f0f9ff';
    });
    featuredBox.addEventListener('dragleave', (e) => {
        e.preventDefault();
        featuredBox.style.borderColor = '';
        featuredBox.style.background = '';
    });
    featuredBox.addEventListener('drop', (e) => {
        e.preventDefault();
        featuredBox.style.borderColor = '';
        featuredBox.style.background = '';
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            featuredInput.files = e.dataTransfer.files;
            featuredInput.dispatchEvent(new Event('change'));
        }
    });

    document.getElementById('article-featured-remove').addEventListener('click', () => {
        setFeaturedImagePreview(null, null);
    });

    function getSelectedCategoryIds() {
        return Array.from(document.querySelectorAll('input[name="article_category_ids[]"]:checked')).map(cb => parseInt(cb.value));
    }

    document.getElementById('article-new-category-btn').addEventListener('click', async () => {
        const input = document.getElementById('article-new-category-input');
        const name = input.value.trim();
        if (!name) return;
        
        try {
            const res = await Admin.request('POST', '/admin/categories', { name });
            const selectedIds = getSelectedCategoryIds();
            selectedIds.push(res.data.id);
            await loadCategoriesIntoForm(selectedIds);
            input.value = '';
        } catch (err) {
            Admin.alert(err.message, true);
        }
    });

    document.getElementById('article-categories-list').addEventListener('click', async (e) => {
        const target = e.target;
        if (target.dataset.deleteCategory) {
            if (!await Admin.confirmDelete(`Apakah Anda yakin ingin menghapus kategori "${target.dataset.name}" secara permanen? Semua artikel di kategori ini tidak akan terhapus.`)) return;
            try {
                await Admin.request('DELETE', `/admin/categories/${target.dataset.deleteCategory}`);
                const selectedIds = getSelectedCategoryIds();
                await loadCategoriesIntoForm(selectedIds);
            } catch (err) {
                Admin.alert(err.message, true);
            }
        }
    });

    const catSearchEl = document.getElementById('article-category-search');
    if (catSearchEl) {
        catSearchEl.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const items = document.querySelectorAll('#article-categories-list .category-item');
            items.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                if (name.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    function renderTags() {
        const container = document.getElementById('article-tags-container');
        const input = document.getElementById('article-tags-input');
        if (!container || !input) return;
        
        container.querySelectorAll('.tag-badge').forEach(el => el.remove());
        
        currentTags.forEach((tag, index) => {
            const badge = document.createElement('span');
            badge.className = 'tag-badge';
            badge.style.cssText = 'background: #e2e8f0; color: #334155; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.75rem; display: flex; align-items: center; gap: 0.3rem;';
            badge.innerHTML = `<span>${Admin.escapeHtml(tag)}</span><span class="tag-remove" data-index="${index}" style="cursor: pointer; font-weight: bold; font-size: 1rem; line-height: 1;">&times;</span>`;
            container.insertBefore(badge, input);
        });
    }

    const tagsContainer = document.getElementById('article-tags-container');
    const tagsInput = document.getElementById('article-tags-input');

    if (tagsContainer) {
        tagsContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('tag-remove')) {
                const index = parseInt(e.target.dataset.index);
                currentTags.splice(index, 1);
                renderTags();
                scheduleAutosave();
            } else {
                tagsInput.focus();
            }
        });
    }

    if (tagsInput) {
        tagsInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ',' || e.key === ' ') {
                e.preventDefault();
                const val = tagsInput.value.trim().replace(/,/g, '');
                if (val && !currentTags.includes(val)) {
                    currentTags.push(val);
                    tagsInput.value = '';
                    renderTags();
                    scheduleAutosave();
                } else if (val) {
                    tagsInput.value = '';
                }
            } else if (e.key === 'Backspace' && tagsInput.value === '' && currentTags.length > 0) {
                currentTags.pop();
                renderTags();
                scheduleAutosave();
            }
        });
    }

    async function saveArticle(status) {
        if (!form.reportValidity()) return;

        const id = document.getElementById('article-id').value;
        const payload = {
            title: document.getElementById('article-title').value,
            slug: document.getElementById('article-slug').value || undefined,
            excerpt: document.getElementById('article-excerpt').value,
            content: document.getElementById('article-content').value,
            meta_title: document.getElementById('article-meta-title').value || undefined,
            meta_description: document.getElementById('article-meta-description').value || undefined,
            status: status,
            featured_media_id: document.getElementById('article-featured-media-id').value || undefined,
            category_ids: getSelectedCategoryIds(),
        };

        payload.tags = currentTags;

        const authorId = document.getElementById('article-author').value;
        if (authorId) {
            payload.author_id = authorId;
        }

        const btnPublish = document.getElementById('article-save-publish');
        const btnDraft = document.getElementById('article-save-draft');
        const originalPublish = btnPublish.textContent;
        const originalDraft = btnDraft.textContent;
        
        btnPublish.disabled = true;
        btnDraft.disabled = true;
        if (status === 'published') btnPublish.textContent = 'Menyimpan...';
        else btnDraft.textContent = 'Menyimpan...';

        try {
            let res;
            if (id) {
                res = await Admin.request('PUT', `/admin/articles/${id}`, payload);
            } else {
                res = await Admin.request('POST', `/admin/articles`, payload);
                document.getElementById('article-id').value = res.data.id;
                // Update URL to reflect the new ID without reloading
                const url = new URL(window.location);
                url.searchParams.set('edit', res.data.id);
                window.history.pushState({}, '', url);
            }
            document.getElementById('article-save-status').textContent = '';
            
            Admin.alert('Artikel berhasil disimpan.');
        } catch (err) {
            Admin.alert(err.message, true);
        } finally {
            btnPublish.disabled = false;
            btnDraft.disabled = false;
            btnPublish.textContent = status === 'published' ? (payload.status === 'published' ? 'Perbarui (Published)' : 'Publikasikan') : originalPublish;
            btnDraft.textContent = originalDraft;
        }
    }

    document.getElementById('article-save-publish').addEventListener('click', () => saveArticle('published'));
    document.getElementById('article-save-draft').addEventListener('click', () => saveArticle('draft'));

    // Deep link support: /admin/panel/articles?edit=5
    const params = new URLSearchParams(window.location.search);
    if (params.has('edit')) {
        loadAuthors().then(() => loadIntoForm(params.get('edit')).catch(() => showList()));
    } else {
        loadAuthors().then(() => loadCategoryFilterOptions().then(() => showList()));
    }

    async function loadAuthors() {
        try {
            const res = await Admin.request('GET', '/admin/authors');
            const select = document.getElementById('article-author');
            if (select) {
                select.innerHTML = '<option value="">-- Pilih Penulis --</option>' + 
                    res.data.map(u => `<option value="${u.id}">${Admin.escapeHtml(u.name)}</option>`).join('');
            }
        } catch (err) {
            console.error('Failed to load authors', err);
        }
    }

    async function loadRevisions(articleId) {
        const listEl = document.getElementById('article-revisions-list');
        if (!listEl) return;
        
        if (!articleId) {
            listEl.innerHTML = '<div style="color: var(--admin-muted); text-align: center;">Belum ada riwayat.</div>';
            return;
        }

        try {
            const res = await Admin.request('GET', `/admin/articles/${articleId}/revisions`);
            if (!res.data || res.data.length === 0) {
                listEl.innerHTML = '<div style="color: var(--admin-muted); text-align: center;">Belum ada riwayat.</div>';
                return;
            }

            listEl.innerHTML = res.data.map(r => `
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; border: 1px solid var(--admin-border); border-radius: 6px; padding: 0.75rem; margin-bottom: 0.75rem; background: #f8fafc; transition: all 0.2s;">
                    <div style="flex: 1; min-width: 150px;">
                        <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.25rem;">${Admin.escapeHtml(r.title)}</div>
                        <div style="color: var(--admin-muted); font-size: 0.8rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem;">
                            <span style="background: #e2e8f0; padding: 0.1rem 0.4rem; border-radius: 4px; color: #334155;">${r.author_name ? Admin.escapeHtml(r.author_name) : 'Sistem'}</span>
                            <span>&bull;</span>
                            <span>${new Date(r.created_at).toLocaleString('id-ID', {day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'})}</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm restore-revision-btn" data-id="${r.id}" style="background: white; border: 1px solid var(--admin-primary); color: var(--admin-primary); border-radius: 4px; padding: 0.35rem 0.75rem; font-weight: 500; font-size: 0.8rem; cursor: pointer; white-space: nowrap;">Pulihkan</button>
                </div>
            `).join('');

            listEl.querySelectorAll('.restore-revision-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const revId = e.target.dataset.id;
                    if (!await Admin.confirmDelete('Apakah Anda yakin ingin memulihkan revisi ini? Konten saat ini akan ditimpa.', 'Ya, Pulihkan', 'Batal')) return;
                    
                    try {
                        const restoreRes = await Admin.request('POST', `/admin/articles/${articleId}/restore-revision`, { revision_id: revId });
                        Admin.alert('Revisi berhasil dipulihkan.');
                        await loadIntoForm(restoreRes.data.id);
                    } catch (err) {
                        Admin.alert(err.message, true);
                    }
                });
            });
        } catch (err) {
            listEl.innerHTML = '<div style="color: var(--admin-danger); text-align: center;">Gagal memuat riwayat.</div>';
        }
    }
})();
