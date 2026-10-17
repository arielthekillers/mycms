<div id="users-list-view">
    <div class="toolbar-modern" style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Manajemen Pengguna</h2>
            <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Kelola pengguna, profil, peran, dan akses ke admin panel.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="UserList.openForm()">+ Tambah Pengguna</button>
    </div>

    <div id="users-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
        <div style="grid-column: 1 / -1; text-align:center; color:var(--admin-muted); padding:2rem;">Memuat...</div>
    </div>
</div>

<div id="users-form-view" hidden>
    <div class="toolbar-modern">
        <div style="display: flex; align-items: center; gap: 1rem; width: 100%;">
            <button type="button" class="btn btn-ghost" onclick="UserList.closeForm()" style="padding-left: 0;">&larr; Kembali</button>
            <h2 id="user-form-title" style="margin: 0; font-size: 1.25rem; font-weight: 600;">Tambah Pengguna</h2>
            <div style="margin-left: auto;">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('user-form').dispatchEvent(new Event('submit'))">Simpan</button>
            </div>
        </div>
    </div>
    
    <div style="max-width: 600px; margin: 2rem 0; background: #fff; padding: 2rem; border-radius: 8px; border: 1px solid var(--admin-border);">
        <form id="user-form" onsubmit="UserList.saveUser(event)">
            <input type="hidden" id="user-id">
            
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Nama Lengkap</label>
                <input type="text" id="user-name" class="admin-input" style="width:100%; padding:0.5rem;" required>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Username</label>
                <input type="text" id="user-username" class="admin-input" style="width:100%; padding:0.5rem;" required>
                <small id="username-feedback" style="display:block; margin-top:0.25rem; min-height:1rem;"></small>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Email</label>
                <input type="email" id="user-email" class="admin-input" style="width:100%; padding:0.5rem;" required>
                <small id="email-feedback" style="display:block; margin-top:0.25rem; min-height:1rem;"></small>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Password</label>
                <div style="display:flex; gap:0.5rem;">
                    <input type="password" id="user-password" class="admin-input" style="flex:1; padding:0.5rem;">
                    <button type="button" class="btn btn-ghost" onclick="UserList.generatePassword()" style="padding:0.5rem 1rem; border: 1px solid var(--admin-border);">Generate</button>
                </div>
                <small id="password-help" style="display:block; margin-top:0.25rem; color:var(--admin-muted); min-height:1rem;"></small>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Role / Peran</label>
                <div id="user-roles-container">
                    <span style="color: var(--admin-muted); font-size: 0.85rem;">Memuat role...</span>
                </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:500;">
                    <input type="checkbox" id="user-active" value="1" checked class="custom-checkbox"> Aktif
                </label>
            </div>
            
            <button type="submit" hidden></button>
        </form>
    </div>
</div>
