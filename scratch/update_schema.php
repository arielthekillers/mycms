<?php
require __DIR__ . '/../vendor/autoload.php';

$app = \App\Core\Application::bootstrap(dirname(__DIR__));
$db = $app->db;

$schema = json_encode([
    'intro' => 'text',
    'card1_title' => 'string',
    'card2_title' => 'string',
    'card3_title' => 'string'
]);

try {
    $db->execute("UPDATE widgets SET settings_schema = ? WHERE key_name = 'aw_card_widget'", [$schema]);
    echo "SUCCESS: Schema diperbarui";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
