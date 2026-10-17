<?php
require __DIR__ . '/../vendor/autoload.php';

// Inisialisasi aplikasi agar koneksi database tersedia
$app = \App\Core\Application::bootstrap(dirname(__DIR__));
$db = $app->db;

$widget = [
    'key_name' => 'aw_card_widget',
    'label' => 'Antigravity Card Widget',
    'provider' => \App\Theme\Widgets\AWCardWidget::class,
    'settings_schema' => [
        'title' => 'string', 
        'intro' => 'string|nullable',
        'card1_title' => 'string',
        'card2_title' => 'string',
        'card3_title' => 'string'
    ],
];

try {
    $db->execute(
        'INSERT IGNORE INTO widgets (key_name, label, provider, settings_schema)
         VALUES (:key_name, :label, :provider, :settings_schema)',
        [
            'key_name' => $widget['key_name'],
            'label' => $widget['label'],
            'provider' => $widget['provider'],
            'settings_schema' => json_encode($widget['settings_schema']),
        ]
    );
    echo "SUCCESS: Widget 'Antigravity Card Widget' berhasil didaftarkan ke Database!";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
