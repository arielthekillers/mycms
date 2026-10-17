<?php
// uninstall.php
// Cleanup task when plugin is removed.
$storagePath = storage_path('data/sliders');
// We keep the JSON files by default so users don't lose their sliders if they reinstall the plugin.
// If you want to force delete:
// array_map('unlink', glob("$storagePath/*.*"));
// rmdir($storagePath);
