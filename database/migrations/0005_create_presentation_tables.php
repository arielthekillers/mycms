<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE layouts (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                key_name        VARCHAR(100) NOT NULL UNIQUE,
                label           VARCHAR(150) NOT NULL,
                structure       JSON NOT NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE templates (
                id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                theme               VARCHAR(100) NOT NULL,
                key_name            VARCHAR(100) NOT NULL,
                label               VARCHAR(150) NOT NULL,
                default_layout_id   BIGINT UNSIGNED NULL,
                created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_theme_template (theme, key_name),
                FOREIGN KEY (default_layout_id) REFERENCES layouts(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE widget_areas (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                key_name        VARCHAR(100) NOT NULL UNIQUE,
                label           VARCHAR(150) NOT NULL,
                scope           ENUM('global','layout') DEFAULT 'global',
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE widgets (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                key_name        VARCHAR(100) NOT NULL UNIQUE,
                label           VARCHAR(150) NOT NULL,
                provider        VARCHAR(150) NOT NULL,
                settings_schema JSON NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE widget_instances (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                widget_id       BIGINT UNSIGNED NOT NULL,
                widget_area_id  BIGINT UNSIGNED NOT NULL,
                title_override  VARCHAR(150) NULL,
                settings        JSON NULL,
                sort_order      INT DEFAULT 0,
                is_active       BOOLEAN DEFAULT TRUE,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE,
                FOREIGN KEY (widget_area_id) REFERENCES widget_areas(id) ON DELETE CASCADE,
                INDEX idx_area_order (widget_area_id, sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS widget_instances');
        $db->execute('DROP TABLE IF EXISTS widgets');
        $db->execute('DROP TABLE IF EXISTS widget_areas');
        $db->execute('DROP TABLE IF EXISTS templates');
        $db->execute('DROP TABLE IF EXISTS layouts');
    }
};
