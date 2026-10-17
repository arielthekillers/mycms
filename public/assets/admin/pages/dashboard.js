(async function () {
    const el = document.getElementById('dashboard-recent');

    try {
        const res = await Admin.request('GET', '/admin/articles?limit=5');
        const articles = res.data;

        if (articles.length === 0) {
            el.innerHTML = '<p>Belum ada artikel. <a href="/admin/panel/articles">Buat artikel pertama</a>.</p>';
            return;
        }

        el.innerHTML = `
            <h3>Artikel Terbaru</h3>
            <table class="admin-table">
                <thead><tr><th>Judul</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    ${articles.map((a) => `
                        <tr>
                            <td>${Admin.escapeHtml(a.title)}</td>
                            <td><span class="badge badge-${a.status}">${Admin.escapeHtml(a.status)}</span></td>
                            <td><a href="/admin/panel/articles?edit=${a.id}" class="btn btn-sm">Edit</a></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    } catch (e) {
        el.innerHTML = `<p>Gagal memuat data: ${Admin.escapeHtml(e.message)}</p>`;
    }
})();
