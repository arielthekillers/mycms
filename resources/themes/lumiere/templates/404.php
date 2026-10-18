<div class="error-page-wrapper">
    <div class="error-card">
        <div class="error-badge">
            <i class="fas fa-compass"></i> Error 404
        </div>
        <h1 class="error-code">404</h1>
        <h2 class="error-heading">Halaman Tidak Ditemukan</h2>
        <p class="error-desc">
            Mohon maaf, tautan yang Anda tuju mungkin salah ketik, telah dihapus, atau sedang dipindahkan ke alamat baru.
        </p>

        <div class="error-actions">
            <a href="/" class="btn-error-primary">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
            <a href="/kategori/kegiatan-santri" class="btn-error-secondary">
                <i class="far fa-newspaper"></i> Jelajahi Warta
            </a>
        </div>

        <div class="error-quicklinks">
            <span>Tautan Populer:</span>
            <div class="quicklinks-row">
                <a href="/">Beranda</a>
                <span>&bull;</span>
                <a href="/halaman/profil">Profil Pesantren</a>
                <span>&bull;</span>
                <a href="/kategori/kegiatan-santri">Kegiatan Santri</a>
                <span>&bull;</span>
                <a href="/admin/login">Area Pengelola</a>
            </div>
        </div>
    </div>
</div>

<style>
    .error-page-wrapper {
        min-height: 75vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5rem 5%;
        background: radial-gradient(circle at 50% 20%, rgba(27, 178, 166, 0.08) 0%, var(--bg-light, #f8fafc) 70%);
    }
    .error-card {
        background: white;
        border-radius: 28px;
        padding: 4.5rem 3.5rem;
        max-width: 650px;
        width: 100%;
        text-align: center;
        box-shadow: 0 15px 45px rgba(0,0,0,0.06);
        border: 1px solid #edf2f7;
        position: relative;
    }
    .error-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(244, 180, 26, 0.15);
        color: #b45309;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.4rem 1.15rem;
        border-radius: 50px;
        letter-spacing: 0.03em;
        margin-bottom: 1.5rem;
    }
    .error-code {
        font-size: 6.5rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.05em;
        background: linear-gradient(135deg, var(--teal) 0%, var(--gold) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }
    .error-heading {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-main);
        margin: 1rem 0 0.85rem;
        letter-spacing: -0.02em;
    }
    .error-desc {
        color: var(--text-muted);
        font-size: 1.05rem;
        line-height: 1.7;
        margin-bottom: 2.5rem;
    }
    .error-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 3rem;
    }
    .btn-error-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 2rem;
        border-radius: 50px;
        background: var(--teal);
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        box-shadow: 0 8px 20px rgba(27, 178, 166, 0.25);
        transition: all 0.25s ease;
    }
    .btn-error-primary:hover {
        background: var(--teal-dark);
        transform: translateY(-2px);
    }
    .btn-error-secondary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 2rem;
        border-radius: 50px;
        background: #f1f5f9;
        color: var(--text-main);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.25s ease;
    }
    .btn-error-secondary:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
    }
    .error-quicklinks {
        border-top: 1px solid #f1f5f9;
        padding-top: 2rem;
        font-size: 0.88rem;
        color: var(--text-muted);
    }
    .quicklinks-row {
        margin-top: 0.5rem;
        display: flex;
        justify-content: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }
    .quicklinks-row a {
        color: var(--teal);
        text-decoration: none;
        font-weight: 500;
    }
    .quicklinks-row a:hover {
        text-decoration: underline;
    }
    @media (max-width: 640px) {
        .error-code { font-size: 5rem; }
        .error-card { padding: 3rem 1.5rem; }
    }
</style>
