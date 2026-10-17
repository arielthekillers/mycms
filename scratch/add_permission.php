<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Application;

$app = Application::bootstrap(dirname(__DIR__));

$app->db->execute("INSERT IGNORE INTO permissions (key_name, group_name, label) VALUES ('settings.manage', 'Settings', 'Kelola Pengaturan')");

$app->db->execute("
    INSERT IGNORE INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id
    FROM roles r
    CROSS JOIN permissions p
    WHERE r.name = 'super-admin' AND p.key_name = 'settings.manage'
");

echo "Permission added.\n";
