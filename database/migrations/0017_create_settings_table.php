<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE IF NOT EXISTS settings (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(100) NOT NULL UNIQUE,
                `value` TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        SQL);

        // Insert default settings
        $db->insert("INSERT INTO settings (`key`, `value`) VALUES ('site_name', 'Pesantren CMS')");
        $db->insert("INSERT INTO settings (`key`, `value`) VALUES ('site_description', 'Sistem Manajemen Konten untuk Pesantren')");
        $db->insert("INSERT INTO settings (`key`, `value`) VALUES ('site_address', '')");
        $db->insert("INSERT INTO settings (`key`, `value`) VALUES ('contact_email', 'admin@example.com')");
    }

    public function down(Database $db): void
    {
        $db->execute("DROP TABLE IF EXISTS settings");
    }
};
