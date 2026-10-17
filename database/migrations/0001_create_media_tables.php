<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE media_folders (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                parent_id       BIGINT UNSIGNED NULL,
                name            VARCHAR(150) NOT NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES media_folders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE media (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                folder_id       BIGINT UNSIGNED NULL,
                uploader_id     BIGINT UNSIGNED NULL,

                file_name       VARCHAR(255) NOT NULL,
                disk_path       VARCHAR(500) NOT NULL,
                disk            VARCHAR(50) DEFAULT 'local',
                mime_type       VARCHAR(100) NOT NULL,
                file_size       BIGINT UNSIGNED NOT NULL,

                width           INT NULL,
                height          INT NULL,
                variants        JSON NULL,

                alt_text        VARCHAR(255) NULL,
                caption         VARCHAR(500) NULL,

                usage_count     INT UNSIGNED DEFAULT 0,

                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (folder_id) REFERENCES media_folders(id) ON DELETE SET NULL,
                INDEX idx_mime (mime_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE media_usages (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                media_id        BIGINT UNSIGNED NOT NULL,
                usable_type     VARCHAR(100) NOT NULL,
                usable_id       BIGINT UNSIGNED NOT NULL,
                usage_context   VARCHAR(50) NOT NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE RESTRICT,
                UNIQUE KEY uniq_usage (media_id, usable_type, usable_id, usage_context),
                INDEX idx_usable (usable_type, usable_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS media_usages');
        $db->execute('DROP TABLE IF EXISTS media');
        $db->execute('DROP TABLE IF EXISTS media_folders');
    }
};
