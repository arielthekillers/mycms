<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE content_modules (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                key_name        VARCHAR(100) NOT NULL UNIQUE,
                label_singular  VARCHAR(150) NOT NULL,
                label_plural    VARCHAR(150) NOT NULL,
                base_route      VARCHAR(150) NOT NULL UNIQUE,
                provider        VARCHAR(150) NOT NULL,

                has_archive     BOOLEAN DEFAULT TRUE,
                has_detail      BOOLEAN DEFAULT TRUE,
                has_taxonomy    BOOLEAN DEFAULT TRUE,
                fields_schema   JSON NULL,

                icon            VARCHAR(100) NULL,
                sort_order      INT DEFAULT 0,
                is_active       BOOLEAN DEFAULT TRUE,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE module_categories (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                module_id       BIGINT UNSIGNED NOT NULL,
                parent_id       BIGINT UNSIGNED NULL,
                name            VARCHAR(150) NOT NULL,
                slug            VARCHAR(150) NOT NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                FOREIGN KEY (module_id) REFERENCES content_modules(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES module_categories(id) ON DELETE CASCADE,
                UNIQUE KEY uniq_module_slug (module_id, slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE module_contents (
                id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                module_id           BIGINT UNSIGNED NOT NULL,
                category_id         BIGINT UNSIGNED NULL,

                title               VARCHAR(255) NOT NULL,
                slug                VARCHAR(255) NOT NULL,
                excerpt             VARCHAR(500) NULL,
                content             LONGTEXT NULL,
                featured_media_id   BIGINT UNSIGNED NULL,

                meta                JSON NULL,

                status              ENUM('draft','published','archived') DEFAULT 'draft',
                published_at        TIMESTAMP NULL,
                sort_order          INT DEFAULT 0,

                created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (module_id) REFERENCES content_modules(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES module_categories(id) ON DELETE SET NULL,
                FOREIGN KEY (featured_media_id) REFERENCES media(id) ON DELETE SET NULL,
                UNIQUE KEY uniq_module_content_slug (module_id, slug),
                INDEX idx_module_status (module_id, status, published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        // Auto-generate baseline permissions whenever a module is registered.
        // Note: DELIMITER is a mysql-CLI-only directive and is intentionally
        // omitted — PDO sends the whole CREATE TRIGGER statement as one query.
        $db->execute(<<<SQL
            CREATE TRIGGER trg_module_permissions_generate
            AFTER INSERT ON content_modules
            FOR EACH ROW
            BEGIN
                INSERT INTO permissions (key_name, group_name, label) VALUES
                    (CONCAT('module.', NEW.key_name, '.view'),    NEW.label_plural, CONCAT('Lihat ', NEW.label_plural)),
                    (CONCAT('module.', NEW.key_name, '.create'),  NEW.label_plural, CONCAT('Tambah ', NEW.label_singular)),
                    (CONCAT('module.', NEW.key_name, '.edit'),    NEW.label_plural, CONCAT('Edit ', NEW.label_singular)),
                    (CONCAT('module.', NEW.key_name, '.delete'),  NEW.label_plural, CONCAT('Hapus ', NEW.label_singular)),
                    (CONCAT('module.', NEW.key_name, '.publish'), NEW.label_plural, CONCAT('Publish ', NEW.label_singular));
            END
        SQL);

        $db->execute(<<<SQL
            CREATE TRIGGER trg_module_permissions_cleanup
            BEFORE DELETE ON content_modules
            FOR EACH ROW
            BEGIN
                DELETE FROM permissions WHERE key_name LIKE CONCAT('module.', OLD.key_name, '.%');
            END
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TRIGGER IF EXISTS trg_module_permissions_cleanup');
        $db->execute('DROP TRIGGER IF EXISTS trg_module_permissions_generate');
        $db->execute('DROP TABLE IF EXISTS module_contents');
        $db->execute('DROP TABLE IF EXISTS module_categories');
        $db->execute('DROP TABLE IF EXISTS content_modules');
    }
};
