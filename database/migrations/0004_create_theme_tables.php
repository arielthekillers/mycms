<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE themes (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                key_name        VARCHAR(100) NOT NULL UNIQUE,
                name            VARCHAR(150) NOT NULL,
                version         VARCHAR(20) NULL,
                is_active       BOOLEAN DEFAULT FALSE,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE theme_options (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                theme_id        BIGINT UNSIGNED NOT NULL,
                option_key      VARCHAR(150) NOT NULL,
                option_value    JSON NULL,

                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE,
                UNIQUE KEY uniq_theme_option (theme_id, option_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS theme_options');
        $db->execute('DROP TABLE IF EXISTS themes');
    }
};
