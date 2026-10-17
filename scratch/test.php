<?php
require __DIR__ . '/../vendor/autoload.php';
$app = \App\Core\Application::bootstrap(__DIR__ . '/..');
$res = $app->db->select('SELECT * FROM system_plugins');
print_r($res);
