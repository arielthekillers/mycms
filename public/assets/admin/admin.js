window.Admin = (function () {
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    async function request(method, url, body) {
        const opts = { 
            method, 
            credentials: 'same-origin', 
            headers: {
                'Accept': 'application/json'
            } 
        };

        if (method !== 'GET') {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                opts.headers['X-CSRF-TOKEN'] = token;
            }
        }

        if (body !== undefined) {
            if (!(body instanceof FormData)) {
                opts.headers['Content-Type'] = 'application/json';
                body = JSON.stringify(body);
            }
            opts.body = body;
        }

        const res = await fetch(url, opts);
        let data = null;

        try { data = await res.json(); } catch (e) { /* no JSON body */ }

        if (!res.ok) {
            const message = (data && data.message) || `Request failed (${res.status})`;
            throw new Error(message);
        }

        return data;
    }

    // --- Modern Toast & Modal Elements ---
    const toastContainer = document.createElement('div');
    toastContainer.id = 'admin-toast-container';
    document.body.appendChild(toastContainer);

    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'admin-modal-overlay';
    document.body.appendChild(modalOverlay);

    function alertBox(message, isError) {
        const toast = document.createElement('div');
        toast.className = 'admin-toast ' + (isError ? 'admin-toast-error' : 'admin-toast-success');
        toast.textContent = message;
        toastContainer.appendChild(toast);
        
        // Force reflow
        void toast.offsetWidth;
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    function confirmDelete(label) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'admin-modal';
            modal.innerHTML = `
                <h3>Konfirmasi Hapus</h3>
                <p>Apakah Anda yakin ingin menghapus <strong>${escapeHtml(label)}</strong>?</p>
                <div class="admin-modal-actions">
                    <button class="btn btn-ghost" id="admin-modal-cancel">Batal</button>
                    <button class="btn btn-danger" id="admin-modal-confirm">Hapus</button>
                </div>
            `;
            
            modalOverlay.innerHTML = '';
            modalOverlay.appendChild(modal);
            
            // Reflow
            void modalOverlay.offsetWidth;
            modalOverlay.classList.add('show');
            modal.classList.add('show');

            const close = (result) => {
                modalOverlay.classList.remove('show');
                modal.classList.remove('show');
                setTimeout(() => {
                    modalOverlay.innerHTML = '';
                }, 200);
                resolve(result);
            };

            document.getElementById('admin-modal-cancel').onclick = () => close(false);
            document.getElementById('admin-modal-confirm').onclick = () => close(true);
        });
    }

    function setWysiwygContent(id, content) {
        const el = document.getElementById(id);
        if (el) el.value = content;
        if (window.tinymce) {
            const editor = tinymce.get(id);
            if (editor) editor.setContent(content);
        }
    }

    function initWysiwyg() {
        if (typeof tinymce === 'undefined') return;
        tinymce.init({
            selector: '.wysiwyg',
            menubar: false,
            promotion: false,
            height: 500,
            content_style: "@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap'); body { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 16px; line-height: 1.6; color: #1e293b; } @media (max-width: 768px) { body { font-size: 14px; } }",
            plugins: 'lists link image table code',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image table | code',
            automatic_uploads: true,
            images_upload_handler: async function (blobInfo, progress) {
                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                try {
                    const res = await request('POST', '/admin/media', formData);
                    return '/uploads/' + res.data.disk_path;
                } catch (err) {
                    throw new Error(err.message);
                }
            },
            setup: function (editor) {
                editor.on('change keyup', function () {
                    editor.save();
                    editor.getElement().dispatchEvent(new Event('input', { bubbles: true }));
                });
            }
        });
    }

    // Highlight active nav link + hide links the current user lacks permission for.
    async function initShell() {
        initWysiwyg();
        const path = window.location.pathname;
        document.querySelectorAll('#admin-nav a').forEach((a) => {
            if (a.getAttribute('href') === path) a.classList.add('active');
        });

        try {
            const me = await request('GET', '/admin/me');
            const perms = me.data.permissions;
            const isSuperAdmin = perms.includes('*');
            const userLabel = document.getElementById('admin-current-user');
            if (userLabel) {
                const name = me.data.user.name;
                const defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=0f766e&color=fff&rounded=true&size=32`;
                const avatarUrl = me.data.user.avatar_url || defaultAvatar;
                userLabel.innerHTML = `<img src="${avatarUrl}" alt="${escapeHtml(name)}" class="admin-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;"> <span>${escapeHtml(name)}</span>`;
            }

            document.querySelectorAll('#admin-nav a[data-permission]').forEach((a) => {
                const required = a.getAttribute('data-permission');
                if (!isSuperAdmin && !perms.includes(required)) {
                    a.style.display = 'none';
                }
            });
        } catch (e) {
            // Not authenticated
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initShell();

        // Global Form Loading State (Optimistic UI)
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn && !submitBtn.disabled && !submitBtn.classList.contains('no-global-loading')) {
                submitBtn.style.width = submitBtn.offsetWidth + 'px';
                
                const originalText = submitBtn.textContent;
                submitBtn.dataset.originalText = originalText;
                submitBtn.textContent = 'Menyimpan...';
                
                setTimeout(() => {
                    submitBtn.disabled = true;
                }, 0);

                form.addEventListener('form:restore', () => {
                    submitBtn.textContent = submitBtn.dataset.originalText;
                    submitBtn.disabled = false;
                    submitBtn.style.width = '';
                }, { once: true });
            }
        });

        // Mobile Sidebar Toggle
        const menuBtn = document.getElementById('mobile-menu-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        if (menuBtn && sidebar && overlay) {
            function toggleMenu() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
                if (sidebar.classList.contains('show')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
            
            menuBtn.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
        }
    });

    return { request, escapeHtml, alert: alertBox, confirmDelete, setWysiwygContent };
})();
