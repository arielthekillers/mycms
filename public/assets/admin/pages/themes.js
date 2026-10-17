(() => {
    const grid = document.getElementById('themes-grid');
    const uploadInput = document.getElementById('theme-upload-input');
    const uploadProgress = document.getElementById('themes-upload-progress');

    async function loadThemes() {
        try {
            const res = await Admin.request('GET', '/admin/api/themes');
            const themes = res.data || [];
            
            if (themes.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--admin-muted);">Belum ada tema yang terinstal.</div>';
                return;
            }

            grid.innerHTML = themes.map(t => {
                const isActive = t.is_active;
                const thumb = t.screenshot_file 
                    ? `/api/themes/assets?theme=${encodeURIComponent(t.directory)}&file=${encodeURIComponent(t.screenshot_file)}`
                    : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCA0MDAgMzAwIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2YxZjVmOSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzk0YTNiOCI+VGFucGEgR2FtYmFyPC90ZXh0Pjwvc3ZnPg==';
                
                return `
                    <div class="admin-card" style="display: flex; flex-direction: column; overflow: hidden; border: ${isActive ? '2px solid var(--admin-primary)' : '1px solid var(--admin-border)'};">
                        <div style="height: 200px; background-image: url('${thumb}'); background-size: cover; background-position: top center; background-color: #f8fafc; border-bottom: 1px solid var(--admin-border);">
                            ${isActive ? '<div style="position: absolute; margin: 0.5rem; background: var(--admin-primary); color: white; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">Aktif</div>' : ''}
                        </div>
                        <div style="padding: 1.25rem; flex-grow: 1; display: flex; flex-direction: column;">
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: #1e293b;">${Admin.escapeHtml(t.name)}</h3>
                            <div style="font-size: 0.8rem; color: var(--admin-muted); margin-bottom: 0.75rem;">Versi ${Admin.escapeHtml(t.version)} &bull; Oleh ${Admin.escapeHtml(t.author)}</div>
                            <p style="font-size: 0.85rem; color: #475569; margin: 0 0 1.5rem 0; line-height: 1.5; flex-grow: 1;">${Admin.escapeHtml(t.description)}</p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--admin-border); padding-top: 1rem;">
                                ${isActive ? `
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <button class="btn btn-sm" disabled style="background: #e2e8f0; color: #64748b; cursor: default;">Sedang Dipakai</button>
                                        <a href="/admin/panel/theme-options" class="btn btn-sm" style="background: var(--admin-primary); color: white; text-decoration: none; display: inline-block;">Opsi Tema</a>
                                    </div>
                                ` : `
                                    <button class="btn btn-sm btn-primary activate-btn" data-dir="${t.directory}">Aktifkan</button>
                                    <button class="btn btn-sm btn-ghost text-danger delete-btn" data-dir="${t.directory}" ${t.directory === 'pesantren-default' ? 'disabled title="Tema bawaan tidak dapat dihapus"' : ''}>Hapus</button>
                                `}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (err) {
            Admin.alert('Gagal memuat daftar tema: ' + err.message, true);
        }
    }

    grid.addEventListener('click', async (e) => {
        const target = e.target;

        if (target.classList.contains('activate-btn')) {
            const dir = target.dataset.dir;
            try {
                await Admin.request('POST', `/admin/api/themes/${encodeURIComponent(dir)}/activate`);
                Admin.alert('Tema berhasil diaktifkan.');
                loadThemes();
            } catch (err) {
                Admin.alert(err.message, true);
            }
        }

        if (target.classList.contains('delete-btn')) {
            const dir = target.dataset.dir;
            if (!await Admin.confirmDelete(`Apakah Anda yakin ingin menghapus tema ${dir} secara permanen?`, 'Ya, Hapus Tema')) return;

            try {
                await Admin.request('DELETE', `/admin/api/themes/${encodeURIComponent(dir)}`);
                Admin.alert('Tema berhasil dihapus.');
                loadThemes();
            } catch (err) {
                Admin.alert(err.message, true);
            }
        }
    });

    uploadInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        uploadInput.value = ''; // Reset
        uploadProgress.style.display = 'block';

        try {
            const res = await Admin.request('POST', '/admin/api/themes/upload', formData);
            uploadProgress.style.display = 'none';
            Admin.alert(res.message);
            loadThemes();
        } catch (err) {
            uploadProgress.style.display = 'none';
            Admin.alert(err.message, true);
        }
    });

    loadThemes();
})();
