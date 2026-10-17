const UserProfile = {
    init() {
        this.fetchProfile();
        const uploadEl = document.getElementById('profile-avatar-upload');
        if (uploadEl) {
            uploadEl.addEventListener('change', (e) => this.handleAvatarUpload(e));
        }
    },

    async fetchProfile() {
        try {
            const res = await Admin.request('GET', '/admin/api/profile');
            const p = res.data;
            
            document.getElementById('profile-name').value = p.name || '';
            document.getElementById('profile-username').value = p.username || '';
            document.getElementById('profile-email').value = p.email || '';
            document.getElementById('profile-phone').value = p.phone || '';
            document.getElementById('profile-avatar-id').value = p.avatar_media_id || '';
            
            const defaultAvatar = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(p.name) + '&background=f1f5f9&color=475569&rounded=true';
            
            if (p.avatar_url) {
                document.getElementById('profile-avatar-preview').src = p.avatar_url;
            } else {
                document.getElementById('profile-avatar-preview').src = defaultAvatar;
            }
            
            // update header user info specifically
            const headerAvatar = document.querySelector('#admin-current-user img');
            const headerName = document.querySelector('#admin-current-user span');
            if (headerAvatar) {
                headerAvatar.src = p.avatar_url || defaultAvatar;
            }
            if (headerName) {
                headerName.textContent = p.name;
            }
        } catch (e) {
            Admin.alert('Gagal memuat profil', true);
        }
    },

    async saveProfile(e) {
        e.preventDefault();
        const form = e.target;
        
        const payload = {
            name: document.getElementById('profile-name').value,
            email: document.getElementById('profile-email').value,
            phone: document.getElementById('profile-phone').value,
            old_password: document.getElementById('profile-old-password').value,
            password: document.getElementById('profile-password').value,
            avatar_media_id: document.getElementById('profile-avatar-id').value
        };

        if (payload.password && !payload.old_password) {
            Admin.alert('Masukkan kata sandi lama untuk mengubah kata sandi.', true);
            form.dispatchEvent(new Event('form:restore'));
            return;
        }

        try {
            await Admin.request('PUT', '/admin/api/profile', payload);
            Admin.alert('Profil berhasil diperbarui', false);
            document.getElementById('profile-old-password').value = '';
            document.getElementById('profile-password').value = '';
            this.fetchProfile(); // reload to update topbar
        } catch (err) {
            Admin.alert(err.message || 'Gagal menyimpan profil', true);
        } finally {
            form.dispatchEvent(new Event('form:restore'));
        }
    },

    async handleAvatarUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        try {
            const res = await Admin.request('POST', '/admin/media', formData);
            document.getElementById('profile-avatar-id').value = res.data.id;
            document.getElementById('profile-avatar-preview').src = '/uploads/' + res.data.disk_path;
            Admin.alert('Avatar berhasil diunggah. Silakan Simpan Profil.', false);
        } catch (err) {
            Admin.alert(err.message, true);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => UserProfile.init());
