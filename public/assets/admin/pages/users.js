const UserList = {
    users: [],
    roles: [],

    init() {
        this.fetchUsers();
        this.fetchRoles();
        this.setupCheckers();
    },

    setupCheckers() {
        let timeout = null;
        const check = (type, val) => {
            clearTimeout(timeout);
            const fb = document.getElementById(type + '-feedback');
            if (!val) {
                if (fb) fb.innerHTML = '';
                return;
            }
            
            if (type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(val)) {
                    if (fb) fb.innerHTML = '<span style="color:var(--admin-danger);">Format email tidak valid ✗</span>';
                    return;
                }
            }
            
            if (type === 'username') {
                const userRegex = /^[a-zA-Z0-9_.-]+$/;
                if (val.length < 3) {
                    if (fb) fb.innerHTML = '<span style="color:var(--admin-danger);">Minimal 3 karakter ✗</span>';
                    return;
                }
                if (!userRegex.test(val)) {
                    if (fb) fb.innerHTML = '<span style="color:var(--admin-danger);">Hanya boleh huruf, angka, _, -, atau . ✗</span>';
                    return;
                }
            }

            timeout = setTimeout(async () => {
                const id = document.getElementById('user-id').value;
                try {
                    if (fb) fb.innerHTML = '<span style="color:var(--admin-muted);">Memeriksa...</span>';
                    const res = await Admin.request('GET', `/admin/api/users/check?${type}=${encodeURIComponent(val)}&exclude_id=${id}`);
                    if (fb) {
                        if (res.available) {
                            fb.innerHTML = `<span style="color:var(--admin-success);">${type === 'username' ? 'Username' : 'Email'} tersedia ✓</span>`;
                        } else {
                            fb.innerHTML = `<span style="color:var(--admin-danger);">${type === 'username' ? 'Username' : 'Email'} sudah digunakan ✗</span>`;
                        }
                    }
                } catch (e) {
                    if (fb) fb.innerHTML = '';
                }
            }, 500);
        };
        
        document.getElementById('user-username')?.addEventListener('input', (e) => check('username', e.target.value));
        document.getElementById('user-email')?.addEventListener('input', (e) => check('email', e.target.value));
    },

    generatePassword() {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        let pass = '';
        for (let i = 0; i < 12; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
        const input = document.getElementById('user-password');
        input.value = pass;
        input.type = 'text';
        setTimeout(() => {
            if (input) input.type = 'password';
        }, 5000);
    },

    async fetchRoles() {
        try {
            const res = await Admin.request('GET', '/admin/api/roles');
            this.roles = res.data;
        } catch (e) {
            console.error('Failed to load roles');
        }
    },

    async fetchUsers() {
        try {
            const res = await Admin.request('GET', '/admin/api/users');
            this.users = res.data;
            this.render();
        } catch (e) {
            Admin.alert('Gagal memuat pengguna', true);
        }
    },

    render() {
        const grid = document.getElementById('users-grid');
        if (!this.users.length) {
            grid.innerHTML = `<div style="grid-column: 1 / -1; text-align:center; color:var(--admin-muted); padding:2rem; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px;">Belum ada pengguna</div>`;
            return;
        }

        grid.innerHTML = this.users.map(u => `
            <div style="background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; padding: 1.5rem; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
                <div style="position: absolute; top: 1rem; right: 1rem;">
                    <div class="dropdown-wrapper" style="position: relative;">
                        <button class="btn-icon" onclick="UserList.toggleDropdown(event, ${u.id})" style="padding: 0.25rem;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                        </button>
                        <div id="dropdown-${u.id}" class="user-dropdown" style="display: none; position: absolute; right: 0; top: 100%; background: #fff; border: 1px solid var(--admin-border); border-radius: 6px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10; min-width: 120px; overflow: hidden; margin-top: 0.25rem;">
                            <button onclick="UserList.editUser(${u.id})" style="display: block; width: 100%; text-align: left; padding: 0.5rem 1rem; border: none; background: transparent; cursor: pointer; font-size: 0.85rem;">Edit</button>
                            <button onclick="UserList.deleteUser(${u.id})" style="display: block; width: 100%; text-align: left; padding: 0.5rem 1rem; border: none; background: transparent; cursor: pointer; font-size: 0.85rem; color: var(--admin-danger);">Hapus</button>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <img src="${u.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name) + '&background=f1f5f9&color=475569&rounded=true'}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0;">
                    <div>
                        <div style="font-weight: 600; color: #0f172a; font-size: 1.05rem;">${Admin.escapeHtml(u.name)}</div>
                        <div style="color: #64748b; font-size: 0.85rem;">@${Admin.escapeHtml(u.username)}</div>
                    </div>
                </div>
                
                <div style="font-size: 0.85rem; color: #475569; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${Admin.escapeHtml(u.email)}</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--admin-border); padding-top: 1rem; margin-top: auto;">
                    <span style="display:inline-block; padding:0.25rem 0.6rem; border-radius:9999px; font-size:0.75rem; font-weight:600; background: ${u.is_active ? '#dcfce7' : '#f1f5f9'}; color: ${u.is_active ? '#166534' : '#475569'};">
                        ${u.is_active ? 'Aktif' : 'Nonaktif'}
                    </span>
                    <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;">${u.roles && u.roles.length ? Admin.escapeHtml(u.roles.join(', ')) : 'User'}</span>
                </div>
            </div>
        `).join('');
    },

    toggleDropdown(e, id) {
        e.stopPropagation();
        document.querySelectorAll('.user-dropdown').forEach(el => {
            if (el.id !== 'dropdown-' + id) el.style.display = 'none';
        });
        const d = document.getElementById('dropdown-' + id);
        if (d) {
            d.style.display = d.style.display === 'none' ? 'block' : 'none';
        }
    },

    openForm() {
        document.getElementById('user-form-title').textContent = 'Tambah Pengguna';
        document.getElementById('user-id').value = '';
        document.getElementById('user-name').value = '';
        document.getElementById('user-username').value = '';
        document.getElementById('user-email').value = '';
        document.getElementById('user-password').value = '';
        document.getElementById('user-password').required = true;
        document.getElementById('user-active').checked = true;
        
        const pwdHelp = document.getElementById('password-help');
        if (pwdHelp) pwdHelp.textContent = 'Wajib diisi (minimal 6 karakter).';
        
        const uf = document.getElementById('username-feedback');
        if (uf) uf.innerHTML = '';
        const ef = document.getElementById('email-feedback');
        if (ef) ef.innerHTML = '';
        
        const rolesContainer = document.getElementById('user-roles-container');
        if (rolesContainer) {
            rolesContainer.innerHTML = this.roles.map(r => `
                <label style="display:flex; align-items:center; gap:0.5rem; margin-bottom: 0.25rem;">
                    <input type="checkbox" name="user_roles[]" value="${Admin.escapeHtml(r.name)}" class="custom-checkbox user-role-cb">
                    <span>${Admin.escapeHtml(r.name)} <small style="color:var(--admin-muted); font-weight:normal;">- ${Admin.escapeHtml(r.label || '')}</small></span>
                </label>
            `).join('');
        }
        
        document.getElementById('users-list-view').hidden = true;
        document.getElementById('users-form-view').hidden = false;
    },

    closeForm() {
        document.getElementById('users-form-view').hidden = true;
        document.getElementById('users-list-view').hidden = false;
    },

    editUser(id) {
        const u = this.users.find(x => x.id === id);
        if (!u) return;

        document.getElementById('user-form-title').textContent = 'Edit Pengguna';
        document.getElementById('user-id').value = u.id;
        document.getElementById('user-name').value = u.name;
        document.getElementById('user-username').value = u.username;
        document.getElementById('user-email').value = u.email;
        document.getElementById('user-password').value = '';
        document.getElementById('user-password').required = false;
        document.getElementById('user-active').checked = !!u.is_active;

        const pwdHelp = document.getElementById('password-help');
        if (pwdHelp) pwdHelp.textContent = 'Kosongkan jika tidak ingin mengubah password saat edit.';

        const uf = document.getElementById('username-feedback');
        if (uf) uf.innerHTML = '';
        const ef = document.getElementById('email-feedback');
        if (ef) ef.innerHTML = '';

        const rolesContainer = document.getElementById('user-roles-container');
        if (rolesContainer) {
            rolesContainer.innerHTML = this.roles.map(r => {
                const isChecked = u.roles && u.roles.includes(r.name) ? 'checked' : '';
                return `
                <label style="display:flex; align-items:center; gap:0.5rem; margin-bottom: 0.25rem;">
                    <input type="checkbox" name="user_roles[]" value="${Admin.escapeHtml(r.name)}" class="custom-checkbox user-role-cb" ${isChecked}>
                    <span>${Admin.escapeHtml(r.name)} <small style="color:var(--admin-muted); font-weight:normal;">- ${Admin.escapeHtml(r.label || '')}</small></span>
                </label>
                `;
            }).join('');
        }

        document.getElementById('users-list-view').hidden = true;
        document.getElementById('users-form-view').hidden = false;
    },

    async saveUser(e) {
        e.preventDefault();
        const form = e.target;
        
        const id = document.getElementById('user-id').value;
        const payload = {
            name: document.getElementById('user-name').value,
            username: document.getElementById('user-username').value,
            email: document.getElementById('user-email').value,
            password: document.getElementById('user-password').value,
            is_active: document.getElementById('user-active').checked ? 1 : 0,
            roles: Array.from(document.querySelectorAll('.user-role-cb:checked')).map(cb => cb.value)
        };

        try {
            if (id) {
                await Admin.request('PUT', `/admin/api/users/${id}`, payload);
                Admin.alert('Pengguna berhasil diperbarui', false);
            } else {
                await Admin.request('POST', '/admin/api/users', payload);
                Admin.alert('Pengguna berhasil ditambahkan', false);
            }
            this.closeForm();
            this.fetchUsers();
        } catch (err) {
            Admin.alert(err.message || 'Gagal menyimpan', true);
        } finally {
            form.dispatchEvent(new Event('form:restore'));
        }
    },

    async deleteUser(id) {
        if (!confirm('Yakin ingin menghapus pengguna ini?')) return;
        try {
            await Admin.request('DELETE', `/admin/api/users/${id}`);
            Admin.alert('Pengguna berhasil dihapus', false);
            this.fetchUsers();
        } catch (err) {
            Admin.alert(err.message || 'Gagal menghapus', true);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    UserList.init();
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        document.querySelectorAll('.user-dropdown').forEach(el => el.style.display = 'none');
    });
});
