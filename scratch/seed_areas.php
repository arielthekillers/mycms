<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../app/bootstrap.php';
$db = $app->db();

$areas = [
    ['key_name' => 'homepage_tentang', 'label' => 'Homepage: Tentang Pesantren', 'scope' => 'global', 'theme' => 'lumiere'],
    ['key_name' => 'homepage_profil', 'label' => 'Homepage: Profil Singkat', 'scope' => 'global', 'theme' => 'lumiere'],
    ['key_name' => 'homepage_berita', 'label' => 'Homepage: Berita Terkini', 'scope' => 'global', 'theme' => 'lumiere']
];

foreach ($areas as $a) {
    $exists = $db->selectOne("SELECT id FROM widget_areas WHERE key_name = :k", ['k' => $a['key_name']]);
    if (!$exists) {
        $db->query("INSERT INTO widget_areas (key_name, label, scope, theme) VALUES (:key_name, :label, :scope, :theme)", $a);
        echo "Inserted " . $a['key_name'] . "\n";
    } else {
        echo "Exists " . $a['key_name'] . "\n";
    }
}
