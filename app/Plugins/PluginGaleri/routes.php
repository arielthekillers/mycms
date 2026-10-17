<?php

declare(strict_types=1);

use App\Core\Application;
use App\Http\Middleware\AuthMiddleware;
use App\Core\Request;
use App\Core\Response;

/** @var \App\Core\Router $router */
$app = Application::getInstance();
$db = $app->db;

// 1. Inisialisasi Database
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS plugin_galeri_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            media_id INT NOT NULL,
            caption VARCHAR(255) DEFAULT '',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
} catch (\PDOException $e) {
    // Ignore if table already exists or other minor issues during boot
}

// 2. Tambahkan Menu ke Admin Sidebar (Melalui Hook - tapi sistem hook belum ada yang global untuk admin menu, 
// jadi kita akan buat halaman terpisah dan mengaksesnya via URL untuk contoh ini).
// Dalam praktiknya, kita bisa memodifikasi layout.php untuk membaca menu dinamis dari plugins.

// 3. Rute Halaman Admin (UI)
$router->get('/admin/panel/plugin-galeri', function (Request $request, Response $response) use ($app) {
    ob_start();
    ?>
    <div class="toolbar" style="justify-content: space-between;">
        <h2>Kelola Galeri Foto</h2>
        <button id="galeri-add-btn" class="btn btn-primary">+ Tambah Foto (Dari Media)</button>
    </div>

    <div id="galeri-list-view" style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
        <!-- Akan dirender via JS -->
    </div>

    <!-- Modal Media Picker Sederhana -->
    <div id="galeri-media-modal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div class="modal-content" style="background: #fff; padding: 1.5rem; border-radius: 8px; width: 90%; max-width: 800px; max-height: 80vh; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0;">Pilih Gambar dari Media</h3>
                <button id="galeri-modal-close" class="btn btn-sm btn-ghost">&times; Tutup</button>
            </div>
            <div id="galeri-media-grid" style="flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.5rem; border: 1px solid var(--admin-border); padding: 0.5rem; background: #f8fafc;">
                <!-- Memuat Media... -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const listContainer = document.getElementById('galeri-list-view');
            const addBtn = document.getElementById('galeri-add-btn');
            const modal = document.getElementById('galeri-media-modal');
            const modalClose = document.getElementById('galeri-modal-close');
            const mediaGrid = document.getElementById('galeri-media-grid');

            // Load gallery items
            function loadGallery() {
                Admin.request('GET', '/admin/api/plugin-galeri')
                    .then(res => {
                        listContainer.innerHTML = '';
                        if(res.data.length === 0) {
                            listContainer.innerHTML = '<p style="grid-column: 1/-1; color: var(--admin-muted);">Galeri masih kosong.</p>';
                            return;
                        }

                        res.data.forEach(item => {
                            const div = document.createElement('div');
                            div.style.cssText = 'border: 1px solid var(--admin-border); border-radius: 4px; overflow: hidden; background: #fff; display: flex; flex-direction: column;';
                            div.innerHTML = `
                                <div style="height: 150px; background-image: url(/uploads/${item.disk_path}); background-size: cover; background-position: center;"></div>
                                <div style="padding: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.85rem; color: var(--admin-muted); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="${item.file_name}">${item.file_name}</span>
                                    <button class="btn btn-sm btn-ghost del-item-btn" data-id="${item.id}" style="color: var(--admin-danger); padding: 0.2rem 0.5rem;">Hapus</button>
                                </div>
                            `;
                            listContainer.appendChild(div);
                        });

                        document.querySelectorAll('.del-item-btn').forEach(b => {
                            b.addEventListener('click', async (e) => {
                                if(await Admin.confirmDelete('Hapus foto ini dari galeri?')) {
                                    Admin.request('DELETE', '/admin/api/plugin-galeri/' + e.target.dataset.id)
                                        .then(() => loadGallery())
                                        .catch(err => Admin.alert(err.message, true));
                                }
                            });
                        });
                    });
            }

            // Load media for picker
            function openMediaPicker() {
                modal.style.display = 'flex';
                mediaGrid.innerHTML = 'Memuat data...';
                
                Admin.request('GET', '/admin/media')
                    .then(res => {
                        mediaGrid.innerHTML = '';
                        if (!res || !res.data) {
                            mediaGrid.innerHTML = '<p style="color:var(--admin-danger);grid-column: 1/-1;">Gagal memuat media. Pastikan rute valid.</p>';
                            return;
                        }
                        const images = res.data.filter(m => m.mime_type.startsWith('image/'));
                        if(images.length === 0) {
                            mediaGrid.innerHTML = '<p style="grid-column: 1/-1;">Belum ada gambar di media.</p>';
                            return;
                        }

                        images.forEach(media => {
                            const imgDiv = document.createElement('div');
                            imgDiv.style.cssText = `height: 100px; background-image: url(/uploads/${media.disk_path}); background-size: cover; background-position: center; cursor: pointer; border-radius: 4px; border: 2px solid transparent;`;
                            imgDiv.title = media.file_name;
                            
                            imgDiv.addEventListener('click', () => {
                                Admin.request('POST', '/admin/api/plugin-galeri', { media_id: media.id })
                                    .then(() => {
                                        modal.style.display = 'none';
                                        Admin.alert('Gambar ditambahkan ke galeri', false);
                                        loadGallery();
                                    })
                                    .catch(err => Admin.alert(err.message, true));
                            });

                            imgDiv.addEventListener('mouseenter', () => imgDiv.style.borderColor = 'var(--admin-primary)');
                            imgDiv.addEventListener('mouseleave', () => imgDiv.style.borderColor = 'transparent');

                            mediaGrid.appendChild(imgDiv);
                        });
                    });
            }

            addBtn.addEventListener('click', openMediaPicker);
            modalClose.addEventListener('click', () => modal.style.display = 'none');

            loadGallery();
        });
    </script>
    <?php
    $content = ob_get_clean();
    $title = 'Plugin Galeri';
    $page = 'plugin-galeri'; // for active sidebar link if we added one
    
    ob_start();
    require $app->basePath . '/resources/admin/layout.php';
    return $response->html(ob_get_clean());
}, [new AuthMiddleware($app->auth)]);

// 4. API Rute (Backend Galeri)
$router->get('/admin/api/plugin-galeri', function (Request $request, Response $response) use ($db) {
    // JOIN dengan tabel media untuk mengambil disk_path
    $items = $db->select("
        SELECT g.id, g.media_id, g.caption, m.file_name, m.disk_path 
        FROM plugin_galeri_items g
        JOIN media m ON g.media_id = m.id
        ORDER BY g.id DESC
    ");
    return $response->json(['data' => $items]);
}, [new AuthMiddleware($app->auth)]);

$router->post('/admin/api/plugin-galeri', function (Request $request, Response $response) use ($db) {
    $mediaId = null;
    if ($request->isJson()) {
        $mediaId = $request->json()['media_id'] ?? null;
    } else {
        $mediaId = $request->input('media_id');
    }
    
    if (!$mediaId) return $response->status(400)->json(['message' => 'Media ID required.']);
    
    $db->execute("INSERT INTO plugin_galeri_items (media_id) VALUES (:mid)", ['mid' => $mediaId]);
    return $response->json(['message' => 'Tersimpan.']);
}, [new AuthMiddleware($app->auth)]);

$router->delete('/admin/api/plugin-galeri/{id}', function (Request $request, Response $response) use ($db) {
    $db->execute("DELETE FROM plugin_galeri_items WHERE id = :id", ['id' => $request->param('id')]);
    return $response->json(['message' => 'Terhapus.']);
}, [new AuthMiddleware($app->auth)]);

// 5. Rute Halaman Publik (/galeri)
$router->get('/galeri', function (Request $request, Response $response) use ($app, $db) {
    $items = $db->select("
        SELECT g.caption, m.disk_path 
        FROM plugin_galeri_items g
        JOIN media m ON g.media_id = m.id
        ORDER BY g.id DESC
    ");

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Galeri Foto</title>
        <style>
            body { font-family: system-ui, sans-serif; background: #f1f5f9; margin: 0; padding: 2rem; }
            .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
            h1 { text-align: center; color: #334155; }
            .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; margin-top: 2rem; }
            .card { border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            .card img { width: 100%; height: 250px; object-fit: cover; display: block; }
            .back-btn { display: inline-block; margin-bottom: 1rem; text-decoration: none; color: #2563eb; font-weight: 500; }
        </style>
    </head>
    <body>
        <div class="container">
            <a href="/" class="back-btn">&larr; Kembali ke Beranda</a>
            <h1>Galeri Foto Kami</h1>
            <?php if(empty($items)): ?>
                <p style="text-align:center; color:#64748b;">Belum ada foto di galeri.</p>
            <?php else: ?>
                <div class="grid">
                    <?php foreach($items as $item): ?>
                        <div class="card">
                            <img src="/uploads/<?= htmlspecialchars($item['disk_path']) ?>" alt="Galeri">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    return $response->html(ob_get_clean());
});
