(function () {
    const grid = document.getElementById('media-grid');
    const uploadForm = document.getElementById('media-upload-form');
    const fileInput = document.getElementById('media-file-input');

    async function loadGrid() {
        grid.innerHTML = '<p>Memuat...</p>';

        try {
            const res = await Admin.request('GET', '/admin/media');
            const items = res.data;

            if (items.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <div class="empty-state-icon">🖼️</div>
                        <h4 class="empty-state-title">Belum ada media</h4>
                        <p class="empty-state-desc">Unggah gambar atau dokumen pertama Anda menggunakan form di atas.</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = items.map((m) => {
                const isImage = (m.mime_type || '').startsWith('image/');
                
                let variants = {};
                try {
                    variants = typeof m.variants === 'string' ? JSON.parse(m.variants || '{}') : (m.variants || {});
                } catch(e) {}
                
                const origUrl = `/uploads/${m.disk_path}`;
                const webpUrl = variants.webp ? `/uploads/${variants.webp}` : null;
                const url = webpUrl || origUrl;
                
                const thumb = isImage
                    ? `<img src="${url}" alt="${Admin.escapeHtml(m.alt_text || m.file_name)}" loading="lazy">`
                    : `<div style="height:100%;width:100%;display:flex;align-items:center;justify-content:center;background:#f1f5f9;font-weight:600;color:#94a3b8;">${Admin.escapeHtml(m.mime_type.split('/')[1] || 'FILE')}</div>`;

                return `
                    <div class="media-card">
                        <div class="media-actions">
                            <button class="media-delete-btn" data-delete="${m.id}" data-name="${Admin.escapeHtml(m.file_name)}" title="Hapus Media">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                        <div class="media-card-img-wrap">
                            ${thumb}
                        </div>
                        <div class="media-meta">
                            <span class="media-title">${Admin.escapeHtml(m.file_name)}</span>
                            ${(m.file_size / 1024).toFixed(1)} KB
                            ${m.usage_count > 0 ? `<br><span style="color:var(--admin-primary);font-size:0.75rem;">Dipakai di ${m.usage_count} tempat</span>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        } catch (e) {
            grid.innerHTML = `<p>Gagal memuat: ${Admin.escapeHtml(e.message)}</p>`;
        }
    }

    async function handleUpload(files) {
        if (!files || files.length === 0) return;
        
        // Tampilkan progress bar
        let progressContainer = document.getElementById('upload-progress-container');
        if (!progressContainer) {
            progressContainer = document.createElement('div');
            progressContainer.id = 'upload-progress-container';
            progressContainer.innerHTML = `
                <div style="margin: 1rem 0; background: #e2e8f0; border-radius: 999px; overflow: hidden; height: 8px;">
                    <div id="upload-progress-bar" style="width: 0%; height: 100%; background: var(--admin-primary); transition: width 0.1s linear;"></div>
                </div>
                <div id="upload-progress-text" style="font-size: 0.8rem; color: var(--admin-muted); text-align: center; margin-bottom: 1.5rem;">Mengunggah...</div>
            `;
            const dropzone = document.getElementById('media-dropzone-area');
            dropzone.parentNode.insertBefore(progressContainer, dropzone.nextSibling);
        }
        
        const progressBar = document.getElementById('upload-progress-bar');
        const progressText = document.getElementById('upload-progress-text');
        progressContainer.style.display = 'block';

        let successCount = 0;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        for (let i = 0; i < files.length; i++) {
            const formData = new FormData();
            formData.append('file', files[i]);
            
            progressText.textContent = `Mengunggah ${files[i].name}... (0%)`;
            progressBar.style.width = '0%';

            await new Promise((resolve) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/admin/media', true);
                xhr.setRequestHeader('Accept', 'application/json');
                if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + '%';
                        progressText.textContent = `Mengunggah ${files[i].name}... (${percent}%)`;
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        successCount++;
                    } else {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            Admin.alert(`Gagal mengunggah ${files[i].name}: ${res.message || xhr.statusText}`, true);
                        } catch(err) {
                            Admin.alert(`Gagal mengunggah ${files[i].name}: HTTP ${xhr.status}`, true);
                        }
                    }
                    resolve();
                };

                xhr.onerror = () => {
                    Admin.alert(`Gagal mengunggah ${files[i].name}: Network error`, true);
                    resolve();
                };

                xhr.send(formData);
            });
        }
        
        progressContainer.style.display = 'none';

        if (successCount > 0) {
            Admin.alert(`${successCount} media berhasil diunggah.`);
        }
        
        fileInput.value = '';
        loadGrid();
    }

    fileInput.addEventListener('change', () => {
        handleUpload(fileInput.files);
    });

    // Drag and drop support
    const dropzone = document.getElementById('media-dropzone-area');
    if (dropzone) {
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                handleUpload(e.dataTransfer.files);
            }
        });
    }

    grid.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-delete]');
        if (!btn) return;
        
        const id = btn.dataset.delete;
        if (!await Admin.confirmDelete(btn.dataset.name)) return;

        try {
            await Admin.request('DELETE', `/admin/media/${id}`);
            Admin.alert('Media dihapus.');
            loadGrid();
        } catch (err) {
            Admin.alert(err.message, true);
        }
    });

    loadGrid();
})();
