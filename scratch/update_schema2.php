<?php
require __DIR__ . '/../vendor/autoload.php';
$app = \App\Core\Application::bootstrap(dirname(__DIR__));
$schema = json_encode([
    'builder_id' => 'string',
    'note' => 'text'
]);
$app->db->execute("UPDATE widgets SET settings_schema = ? WHERE key_name = 'aw_card_widget'", [$schema]);
echo "SUCCESS";
