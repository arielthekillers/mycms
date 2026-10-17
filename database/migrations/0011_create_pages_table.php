<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE pages (
                id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title               VARCHAR(255) NOT NULL,
                slug                VARCHAR(255) NOT NULL UNIQUE,
                content             LONGTEXT NULL,
                featured_media_id   BIGINT UNSIGNED NULL,
                status              ENUM('draft','published') DEFAULT 'draft',
                created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (featured_media_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS pages');
    }
};
