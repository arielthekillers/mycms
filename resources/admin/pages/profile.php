<div class="toolbar-modern">
    <div style="font-size: 1.25rem; font-weight: 600;">Profil Saya</div>
</div>

<div style="max-width: 600px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 8px; border: 1px solid var(--admin-border);">
    <form id="profile-form" onsubmit="UserProfile.saveProfile(event)">
        <div style="text-align: center; margin-bottom: 2rem;">
            <img id="profile-avatar-preview" src="/assets/admin/default-avatar.png" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem; border: 1px solid var(--admin-border);">
            <div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('profile-avatar-upload').click()">Pilih Avatar Baru</button>
                <input type="file" id="profile-avatar-upload" accept="image/*" style="display: none;">
                <input type="hidden" id="profile-avatar-id">
            </div>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Nama Lengkap</label>
            <input type="text" id="profile-name" class="admin-input" style="width:100%; padding:0.5rem;" required>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Username</label>
            <input type="text" id="profile-username" class="admin-input" style="width:100%; padding:0.5rem; background-color: #f8fafc; cursor: not-allowed; color: var(--admin-muted);" readonly>
            <small style="color: var(--admin-muted); display: block; margin-top: 0.25rem;">Username tidak dapat diubah.</small>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Email</label>
            <input type="email" id="profile-email" class="admin-input" style="width:100%; padding:0.5rem;" required>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Nomor Telepon</label>
            <input type="text" id="profile-phone" class="admin-input" style="width:100%; padding:0.5rem;">
        </div>
        
        <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--admin-border);">
        
        <h4 style="margin-bottom: 0.5rem;">Ubah Kata Sandi</h4>
        <p style="color: var(--admin-muted); font-size: 0.85rem; margin-bottom: 1rem;">Kosongkan jika tidak ingin mengubah kata sandi.</p>
        
        <div style="margin-bottom: 1rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Kata Sandi Lama</label>
            <input type="password" id="profile-old-password" class="admin-input" style="width:100%; padding:0.5rem;" placeholder="Masukkan kata sandi lama">
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Kata Sandi Baru</label>
            <input type="password" id="profile-password" class="admin-input" style="width:100%; padding:0.5rem;" placeholder="Masukkan kata sandi baru">
        </div>
        
        <div style="text-align: right;">
            <button type="submit" class="btn btn-primary">Simpan Profil</button>
        </div>
    </form>
</div>
