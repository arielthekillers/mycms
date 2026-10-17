(function () {
    const listView = document.getElementById('pages-list-view');
    const formView = document.getElementById('pages-form-view');
    const tbody = document.getElementById('pages-tbody');
    const form = document.getElementById('page-form');
    const tabs = document.querySelectorAll('#pages-tabs .tab-btn');
    
    let allPages = [];
    let currentStatus = '';
    let searchQuery = '';
    let sortBy = 'created_at';
    let sortDir = 'desc';

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
            renderList();
        });
    });

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Auto scroll to make tab fully visible on mobile
            e.target.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            
            currentStatus = tab.dataset.status;
            document.getElementById('pages-check-all').checked = false;
            renderList();
        });
    });

    let searchTimeout;
    document.getElementById('pages-search-input').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchQuery = e.target.value.trim().toLowerCase();
            renderList();
        }, 300);
    });

    function showList() { listView.hidden = false; formView.hidden = true; fetchList(); }
    function showForm() { listView.hidden = true; formView.hidden = false; }

    async function fetchList() {
        const skeletonHtml = Array.from({length: 3}).map(() => `
            <tr class="skeleton-row">
                <td style="text-align: center;"><div class="skeleton-box" style="width: 18px; margin: 0 auto;"></div></td>
                <td><div class="skeleton-box" style="width: 60%;"></div></td>
                <td><div class="skeleton-box" style="width: 80px;"></div></td>
                <td><div class="skeleton-box" style="width: 120px;"></div></td>
                <td><div class="skeleton-box" style="width: 30px; margin-left: auto;"></div></td>
            </tr>
        `).join('');
        tbody.innerHTML = skeletonHtml;

        try {
            const res = await Admin.request('GET', '/admin/pages');
            allPages = res.data || [];
            
            // Update tab counts
            const counts = {
                all: allPages.length,
                published: allPages.filter(p => p.status === 'published').length,
                draft: allPages.filter(p => p.status === 'draft').length
            };
            
            tabs.forEach(tab => {
                const status = tab.dataset.status || 'all';
                let label = '';
                if (status === 'all') label = 'Semua Halaman';
                else if (status === 'published') label = 'Published';
                else if (status === 'draft') label = 'Draft';
                tab.textContent = `${label} (${counts[status] || 0})`;
            });

            renderList();
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 2rem; color: var(--admin-danger);">Gagal memuat: ${Admin.escapeHtml(e.message)}</td></tr>`;
        }
    }

    function renderList() {
        let filtered = allPages;
        if (currentStatus) {
            filtered = filtered.filter(p => p.status === currentStatus);
        }
        if (searchQuery) {
            filtered = filtered.filter(p => p.title.toLowerCase().includes(searchQuery));
        }

        filtered.sort((a, b) => {
            let valA = a[sortBy] || '';
            let valB = b[sortBy] || '';
            if (sortBy === 'title') {
                valA = valA.toLowerCase();
                valB = valB.toLowerCase();
            }
            if (valA < valB) return sortDir === 'asc' ? -1 : 1;
            if (valA > valB) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });

        document.getElementById('pages-pagination-info').textContent = `Menampilkan ${filtered.length} halaman`;

        if (filtered.length === 0) {
            let emptyStateHtml = '';
            
            if (allPages.length > 0) {
                let title = 'Belum ada halaman';
                let desc = 'Tidak ada halaman yang sesuai dengan pencarian atau filter Anda.';
                
                if (currentStatus === 'published') {
                    title = 'Belum ada halaman terbit';
                    desc = 'Semua halaman Anda saat ini berstatus Draft.';
                } else if (currentStatus === 'draft') {
                    title = 'Tidak ada Draft';
                    desc = 'Semua halaman Anda saat ini berstatus Published.';
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
                        <h4 class="empty-state-title">Belum ada halaman</h4>
                        <p class="empty-state-desc">Buat halaman profil, kontak, atau informasi statis lainnya.</p>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('pages-new-btn').click()">+ Buat Halaman Pertama</button>
                    </div>
                `;
            }

            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="padding: 0;">
                        ${emptyStateHtml}
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = filtered.map(p => {
            return `
                <tr class="page-row" data-id="${p.id}" style="cursor: pointer;">
                    <td style="text-align: center;"><input type="checkbox" class="page-cb custom-checkbox" value="${p.id}"></td>
                    <td><span style="font-weight: 600; font-size: 1rem; color: #1e293b;">${Admin.escapeHtml(p.title)}</span></td>
                    <td><span class="badge badge-${p.status}">${Admin.escapeHtml(p.status)}</span></td>
                    <td style="font-size: 0.85rem; color: var(--admin-muted);">${p.created_at ? new Date(p.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }) : '-'}</td>
                    <td style="text-align: right;">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-ghost dropdown-toggle" style="padding: 0.2rem; border: none; background: transparent; color: var(--admin-muted);" type="button">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item" data-edit="${p.id}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Edit
                                </button>
                                <button class="dropdown-item text-danger" data-delete="${p.id}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Hapus
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('pages-check-all').checked = false;

        document.querySelectorAll('.page-row').forEach(row => {
            row.addEventListener('click', (e) => {
                if (e.target.closest('button') || e.target.closest('input[type="checkbox"]')) {
                    return;
                }
                const id = row.dataset.id;
                if (id) {
                    loadIntoForm(id).catch((err) => Admin.alert(err.message, true));
                }
            });
        });

        document.querySelectorAll('.dropdown-toggle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
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

        const checkAllBtn = document.getElementById('pages-check-all');
        const rowCheckboxes = document.querySelectorAll('.page-cb');
        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                checkAllBtn.checked = document.querySelectorAll('.page-cb:checked').length === rowCheckboxes.length;
            });
        });
    }

    async function loadIntoForm(id) {
        const res = await Admin.request('GET', `/admin/pages/${id}`);
        const p = res.data;
        document.getElementById('page-id').value = p.id;
        document.getElementById('page-title').value = p.title;
        document.getElementById('page-slug').value = p.slug;
        Admin.setWysiwygContent('page-content', p.content || '');
        document.getElementById('page-meta-title').value = p.meta_title || '';
        document.getElementById('page-meta-description').value = p.meta_description || '';
        document.getElementById('page-status').value = p.status;
        
        const btnPublish = document.getElementById('page-save-publish');
        if (p.status === 'published') {
            btnPublish.textContent = 'Perbarui (Published)';
        } else {
            btnPublish.textContent = 'Publikasikan';
        }

        showForm();
    }

    document.getElementById('pages-new-btn').addEventListener('click', (e) => {
        e.preventDefault();
        form.reset();
        document.getElementById('page-id').value = '';
        document.getElementById('page-save-publish').textContent = 'Publikasikan';
        document.getElementById('page-save-status').textContent = '';
        showForm();
    });

    document.getElementById('page-cancel-btn').addEventListener('click', (e) => {
        e.preventDefault();
        showList();
    });

    tbody.addEventListener('click', async (e) => {
        const target = e.target;
        if (target.dataset.edit) {
            loadIntoForm(target.dataset.edit).catch((err) => Admin.alert(err.message, true));
        } else if (target.dataset.delete) {
            if (!await Admin.confirmDelete('Apakah Anda yakin ingin menghapus halaman ini?')) return;
            try {
                await Admin.request('DELETE', `/admin/pages/${target.dataset.delete}`);
                Admin.alert('Halaman dihapus.');
                fetchList();
            } catch (err) { Admin.alert(err.message, true); }
        }
    });

    document.getElementById('pages-check-all').addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        document.querySelectorAll('.page-cb').forEach(cb => {
            cb.checked = isChecked;
        });
    });

    document.getElementById('pages-bulk-apply').addEventListener('click', async () => {
        const action = document.getElementById('pages-bulk-action').value;
        if (!action) return;

        const checkedBoxes = document.querySelectorAll('.page-cb:checked');
        if (checkedBoxes.length === 0) {
            Admin.alert('Pilih setidaknya satu halaman untuk tindakan ini.', true);
            return;
        }

        const confirmMsg = `Apakah Anda yakin ingin memproses ${checkedBoxes.length} halaman yang dipilih?`;
        if (!await Admin.confirmDelete(confirmMsg)) return;

        const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        const promises = [];

        ids.forEach(id => {
            if (action === 'delete') {
                promises.push(Admin.request('DELETE', `/admin/pages/${id}`));
            }
        });

        try {
            await Promise.all(promises);
            Admin.alert(`Berhasil memproses ${checkedBoxes.length} halaman.`, false);
            document.getElementById('pages-bulk-action').value = '';
            fetchList();
        } catch (err) {
            Admin.alert('Beberapa tindakan gagal: ' + err.message, true);
            fetchList();
        }
    });

    async function saveForm(forceStatus) {
        if (!form.reportValidity()) return;
        
        const id = document.getElementById('page-id').value;
        if (forceStatus) {
            document.getElementById('page-status').value = forceStatus;
        }
        
        const payload = {
            title: document.getElementById('page-title').value,
            slug: document.getElementById('page-slug').value || undefined,
            content: document.getElementById('page-content').value,
            meta_title: document.getElementById('page-meta-title').value || undefined,
            meta_description: document.getElementById('page-meta-description').value || undefined,
            status: document.getElementById('page-status').value,
        };

        const btnPublish = document.getElementById('page-save-publish');
        const btnDraft = document.getElementById('page-save-draft');
        const originalPublish = btnPublish.textContent;
        const originalDraft = btnDraft.textContent;
        
        btnPublish.disabled = true;
        btnDraft.disabled = true;
        if (forceStatus === 'published') btnPublish.textContent = 'Menyimpan...';
        else btnDraft.textContent = 'Menyimpan...';

        try {
            if (id) {
                await Admin.request('PUT', `/admin/pages/${id}`, payload);
            } else {
                const res = await Admin.request('POST', '/admin/pages', payload);
                document.getElementById('page-id').value = res.data.id;
            }
            document.getElementById('page-save-status').textContent = '';
            Admin.alert('Halaman berhasil disimpan.');
        } catch (err) {
            Admin.alert(err.message, true);
        } finally {
            btnPublish.disabled = false;
            btnDraft.disabled = false;
            btnPublish.textContent = forceStatus === 'published' ? (payload.status === 'published' ? 'Perbarui (Published)' : 'Publikasikan') : originalPublish;
            btnDraft.textContent = originalDraft;
        }
    }

    document.getElementById('page-save-draft').addEventListener('click', () => {
        saveForm('draft');
    });
    
    document.getElementById('page-save-publish').addEventListener('click', () => {
        saveForm('published');
    });

    showList();
})();
