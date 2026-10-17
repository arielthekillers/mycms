<?php
require __DIR__ . '/../vendor/autoload.php';

$app = \App\Core\Application::bootstrap(dirname(__DIR__));
$db = $app->db;

try {
    // 1. Tabel aw_widgets
    $db->execute("
        CREATE TABLE IF NOT EXISTS aw_widgets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            shortcode_id VARCHAR(100) NOT NULL UNIQUE,
            title VARCHAR(255) NULL,
            intro TEXT NULL,
            extro TEXT NULL,
            layout VARCHAR(50) DEFAULT 'grid',
            columns INT DEFAULT 3,
            design_mode VARCHAR(50) DEFAULT 'light',
            card_radius VARCHAR(50) DEFAULT 'rounded',
            card_shadow TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Tabel aw_widget_items
    $db->execute("
        CREATE TABLE IF NOT EXISTS aw_widget_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            widget_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            image_url VARCHAR(255) NULL,
            badge_text VARCHAR(100) NULL,
            badge_color VARCHAR(50) DEFAULT 'red',
            badge_position VARCHAR(50) DEFAULT 'left',
            button_text VARCHAR(100) NULL,
            button_url VARCHAR(255) NULL,
            button_style VARCHAR(50) DEFAULT 'solid',
            sort_order INT DEFAULT 0,
            FOREIGN KEY (widget_id) REFERENCES aw_widgets(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "SUCCESS: Tabel aw_widgets dan aw_widget_items berhasil dibuat!";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
