<?php

// Mendaftarkan menu navigasi khusus untuk tema ini di sistem
$app->events->listen('menu.register', function ($menus) {
    $menus['lumiere_header'] = 'Navigasi Atas (Lumière)';
    $menus['lumiere_footer'] = 'Tautan Bawah (Lumière)';
    return $menus;
});

// Registrasi 3 Area Footer secara otomatis
try {
    $areas = [
        'footer_1' => 'Footer Kiri (Lumière)',
        'footer_2' => 'Footer Tengah (Lumière)',
        'footer_3' => 'Footer Kanan (Lumière)',
        'homepage_hero' => 'Beranda - Hero Section',
        'homepage_tentang' => 'Beranda - Tentang',
        'homepage_profil' => 'Beranda - Profil Singkat',
        'homepage_berita' => 'Beranda - Berita Terkini'
    ];
    foreach ($areas as $key => $label) {
        $app->db->execute(
            "INSERT IGNORE INTO widget_areas (key_name, label, scope) VALUES (:k, :l, 'global')",
            ['k' => $key, 'l' => $label]
        );
    }
} catch (\Exception $e) {
    // Abaikan jika tabel belum ada saat instalasi
}

// Menambahkan label pada footer artikel
$app->events->listen('article.before_render', function ($content) use ($app) {
    $year = date('Y');
    $content = $content . "<hr style='margin-top: 3rem; border: none; border-top: 1px solid #e2e8f0;'><p style='font-size: 0.85rem; color: #64748b;'><em>&copy; {$year} Dibuat dengan tema Lumière.</em></p>";
    return $content;
});

