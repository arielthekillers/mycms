document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('plugin-tbody');
    const uploadForm = document.getElementById('plugin-upload-form');

    function loadPlugins() {
        Admin.request('GET', '/admin/api/plugins')
            .then(res => renderPlugins(res.data))
            .catch(err => Admin.alert(err.message, true));
    }

    function renderPlugins(plugins) {
        const container = document.getElementById('plugin-list-view');
        container.innerHTML = '';
        if (plugins.length === 0) {
            container.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1; margin-top: 2rem;">
                    <div class="empty-state-icon">🔌</div>
                    <h4 class="empty-state-title">Belum ada plugin</h4>
                    <p class="empty-state-desc">Unggah plugin dalam format .zip untuk menambahkan fitur baru.</p>
                </div>
            `;
            return;
        }

        plugins.forEach(ext => {
            const card = document.createElement('div');
            card.style.cssText = `background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; transition: all 0.2s; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.02);`;
            if (ext.is_active) {
                card.style.borderTop = '3px solid var(--admin-primary)';
            }
            
            const btnClass = ext.is_active ? 'btn-ghost' : 'btn-success';
            const btnText = ext.is_active ? 'Nonaktifkan' : 'Aktifkan';
            
            card.innerHTML = `
                <div style="padding: 1.25rem; flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                        <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 700;">${Admin.escapeHtml(ext.name)}</h3>
                        <span class="badge ${ext.is_active ? 'badge-success' : 'badge-secondary'}" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                            ${ext.is_active ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </div>
                    <p style="margin: 0 0 1rem 0; font-size: 0.9rem; color: #475569; line-height: 1.5;">${Admin.escapeHtml(ext.description)}</p>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; font-size: 0.8rem; color: #94a3b8; align-items: center;">
                        <span style="display: flex; align-items: center; gap: 0.25rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg> v${Admin.escapeHtml(ext.version)}</span>
                        <span style="display: flex; align-items: center; gap: 0.25rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg> ${Admin.escapeHtml(ext.directory)}</span>
                    </div>
                </div>
                
                <div style="padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid var(--admin-border); display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    ${ext.is_active && ext.admin_url ? `
                        <a href="${Admin.escapeHtml(ext.admin_url)}" class="btn btn-sm btn-ghost" style="color: var(--admin-primary); flex: 1; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--admin-primary); text-align: center;">
                            Kelola
                        </a>
                    ` : ''}
                    <button class="btn ${btnClass} btn-sm toggle-btn" data-dir="${Admin.escapeHtml(ext.directory)}" data-active="${ext.is_active ? 0 : 1}" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; text-align: center; ${ext.is_active ? 'color: var(--admin-danger); border: 1px solid #fca5a5;' : ''}">
                        ${btnText}
                    </button>
                    ${!ext.is_active ? `
                    <button class="btn btn-ghost btn-sm delete-btn" data-delete-dir="${Admin.escapeHtml(ext.directory)}" style="color: var(--admin-danger); width: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: 1px solid transparent;" title="Hapus Permanen">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                    ` : ''}
                </div>
            `;
            container.appendChild(card);
        });

        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const dir = e.target.getAttribute('data-dir');
                const active = e.target.getAttribute('data-active');
                togglePlugin(dir, active);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const dir = e.target.getAttribute('data-delete-dir');
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
        Admin.request('POST', `/admin/api/plugins/${encodeURIComponent(directory)}/toggle`, { is_active: isActive })
            .then(res => {
                Admin.alert(res.message, false);
                loadPlugins();
            })
            .catch(err => Admin.alert(err.message, true));
    }

    const fileInput = document.getElementById('plugin-zip');
    
    fileInput.addEventListener('change', (e) => {
        if (!fileInput.files.length) return;

        const formData = new FormData();
        formData.append('zip_file', fileInput.files[0]);

        Admin.request('POST', '/admin/api/plugins/upload', formData)
            .then(res => {
                Admin.alert(res.message, false);
                fileInput.value = ''; // Reset
                loadPlugins();
            })
            .catch(err => Admin.alert(err.message, true));
    });

    loadPlugins();
});
