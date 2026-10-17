<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$widgets = $app->db->select("SELECT * FROM widgets");
print_r($widgets);
