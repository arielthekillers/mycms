<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '');
$pdo->exec('DROP DATABASE IF EXISTS `pesantren_cms`');
@unlink(__DIR__ . '/../.env');
echo "Reset OK\n";
