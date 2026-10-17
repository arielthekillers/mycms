<?php
require __DIR__ . '/../vendor/autoload.php';

$app = \App\Core\Application::bootstrap(dirname(__DIR__));
$db = $app->db;

try {
    $db->execute(
        "UPDATE widgets SET label = 'Card Widget' WHERE key_name = 'aw_card_widget'"
    );
    echo "SUCCESS: Nama widget berhasil diubah menjadi 'Card Widget'!";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
