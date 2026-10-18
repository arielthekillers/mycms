document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('plugin-upload-form');
    const fileInput = document.getElementById('plugin-zip');

    function loadPlugins() {
        Admin.request('GET', '/admin/api/plugins')
            .then(res => renderPlugins(res.data || []))
            .catch(err => Admin.alert(err.message, true));
    }

    // Map icons and colors for famous plugins
    function getPluginVisuals(dir, name) {
        const lower = (dir + ' ' + name).toLowerCase();
        if (lower.includes('slider')) {
            return {
                icon: '🎬',
                bg: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
                color: '#ffffff'
            };
        }
        if (lower.includes('card') || lower.includes('builder')) {
            return {
                icon: '🃏',
                bg: 'linear-gradient(135deg, #4f46e5 0%, #818cf8 100%)',
                color: '#ffffff'
            };
        }
        if (lower.includes('galeri') || lower.includes('photo') || lower.includes('gallery')) {
            return {
                icon: '🖼️',
                bg: 'linear-gradient(135deg, #ea580c 0%, #fb923c 100%)',
                color: '#ffffff'
            };
        }
        return {
            icon: '🧩',
            bg: 'linear-gradient(135deg, #334155 0%, #64748b 100%)',
            color: '#ffffff'
        };
    }

    function renderPlugins(plugins) {
        const container = document.getElementById('plugin-list-view');
        const statBadge = document.getElementById('plugins-stat-badge');
        
        const activeCount = plugins.filter(p => p.is_active).length;
        if (statBadge) {
            statBadge.textContent = `${plugins.length} Plugin · ${activeCount} Aktif`;
        }

        container.innerHTML = '';
        if (plugins.length === 0) {
            container.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1; margin-top: 2rem; background: #fff; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 3rem 1.5rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔌</div>
                    <h4 style="margin: 0; font-size: 1.2rem; color: #1e293b;">Belum Ada Plugin</h4>
                    <p style="color: #64748b; font-size: 0.9rem; margin: 0.5rem 0 1.5rem 0;">Unggah berkas plugin berekstensi .zip untuk menambahkan fitur baru.</p>
                </div>
            `;
            return;
        }

        plugins.forEach(ext => {
            const card = document.createElement('div');
            card.className = `plugin-card-modern ${ext.is_active ? 'is-active' : 'is-inactive'}`;
            
            const visuals = getPluginVisuals(ext.directory, ext.name);
            const authorName = ext.author || 'mycms team';
            const authorHtml = ext.author_url 
                ? `<a href="${Admin.escapeHtml(ext.author_url)}" target="_blank" rel="noopener noreferrer" style="color:var(--admin-primary); text-decoration:none;"><strong>${Admin.escapeHtml(authorName)}</strong></a>`
                : `<strong>${Admin.escapeHtml(authorName)}</strong>`;

            card.innerHTML = `
                <div class="plugin-card-top">
                    <div class="plugin-header-row">
                        <div class="plugin-icon-wrap" style="background: ${visuals.bg}; color: ${visuals.color};">
                            ${visuals.icon}
                        </div>
                        <div class="plugin-title-wrap">
                            <h3 class="plugin-title">${Admin.escapeHtml(ext.name)}</h3>
                            <span class="plugin-status-badge ${ext.is_active ? 'active' : 'inactive'}">
                                ${ext.is_active ? '● Aktif' : '○ Nonaktif'}
                            </span>
                        </div>
                    </div>

                    <p class="plugin-desc">${Admin.escapeHtml(ext.description || 'Tidak ada deskripsi.')}</p>

                    <div class="plugin-meta-bar">
                        <div class="plugin-author-badge">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span>Oleh ${authorHtml}</span>
                        </div>
                        <div class="plugin-tags-group">
                            <span class="plugin-pill" title="Versi">v${Admin.escapeHtml(ext.version || '1.0.0')}</span>
                            <span class="plugin-pill" title="Folder direktori">📁 ${Admin.escapeHtml(ext.directory)}</span>
                        </div>
                    </div>
                </div>

                <div class="plugin-card-actions">
                    ${ext.is_active && ext.admin_url ? `
                        <a href="${Admin.escapeHtml(ext.admin_url)}" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.45rem 1.1rem; font-weight: 600; border-radius: 6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Kelola</span>
                        </a>
                    ` : ''}

                    <button type="button" class="btn btn-sm toggle-btn" data-dir="${Admin.escapeHtml(ext.directory)}" data-active="${ext.is_active ? 0 : 1}" style="${ext.is_active ? 'color: #e11d48; background: #fff1f2; border: 1px solid #fecdd3;' : 'background: var(--admin-primary, #0f766e); color: #fff; border: none;'} padding: 0.45rem 1rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                        <span>${ext.is_active ? 'Nonaktifkan' : 'Aktifkan'}</span>
                    </button>

                    ${!ext.is_active ? `
                        <button type="button" class="btn btn-ghost btn-sm delete-btn" data-delete-dir="${Admin.escapeHtml(ext.directory)}" style="color: #dc2626; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;" title="Hapus Permanen">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    ` : ''}
                </div>
            `;
            container.appendChild(card);
        });

        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.currentTarget;
                const dir = target.getAttribute('data-dir');
                const active = target.getAttribute('data-active');
                target.disabled = true;
                target.innerHTML = '<span>Memproses...</span>';
                togglePlugin(dir, active);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const target = e.currentTarget;
                const dir = target.getAttribute('data-delete-dir');
                if (await Admin.confirmDelete(`Yakin ingin menghapus plugin ini secara permanen? Semua data terkait (jika didukung) akan terhapus!`)) {
                    deletePlugin(dir);
                }
            });
        });
    }

    function deletePlugin(directory) {
        Admin.request('DELETE', `/admin/api/plugins/${encodeURIComponent(directory)}`)
            .then(res => {
                Admin.alert(res.message, false);
                loadPlugins();
            })
            .catch(err => Admin.alert(err.message, true));
    }

    function togglePlugin(directory, isActive) {
        Admin.request('POST', `/admin/api/plugins/${encodeURIComponent(directory)}/toggle`, { is_active: parseInt(isActive, 10) })
            .then(res => {
                const msg = (res && res.message) ? res.message : 'Status plugin berhasil diubah.';
                Admin.alert(msg, false);
                loadPlugins();
            })
            .catch(err => {
                const msg = (err && err.message) ? err.message : 'Gagal mengubah status plugin.';
                Admin.alert(msg, true);
                loadPlugins();
            });
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (!fileInput.files.length) return;

            const formData = new FormData();
            formData.append('zip_file', fileInput.files[0]);

            Admin.alert('Mengunggah & menginstal plugin...');

            Admin.request('POST', '/admin/api/plugins/upload', formData)
                .then(res => {
                    Admin.alert(res.message, false);
                    fileInput.value = '';
                    loadPlugins();
                })
                .catch(err => {
                    Admin.alert(err.message, true);
                    fileInput.value = '';
                });
        });
    }

    loadPlugins();
});
