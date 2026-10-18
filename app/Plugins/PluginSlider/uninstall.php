<?php
// uninstall.php
$app = \App\Core\Application::getInstance();
try {
    $app->db->execute("DELETE FROM widgets WHERE key_name = 'slider_showcase'");
} catch (\Throwable $e) {}
