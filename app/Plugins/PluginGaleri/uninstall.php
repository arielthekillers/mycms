<?php

declare(strict_types=1);

/** @var \App\Core\Database $db */
if (isset($db)) {
    $db->execute("DROP TABLE IF EXISTS plugin_galeri_items");
}
