<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE menus (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name            VARCHAR(100) NOT NULL,
                slug            VARCHAR(100) NOT NULL UNIQUE,
                location        VARCHAR(50) NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE menu_items (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                menu_id         BIGINT UNSIGNED NOT NULL,
                parent_id       BIGINT UNSIGNED NULL,
                label           VARCHAR(150) NOT NULL,
                icon            VARCHAR(100) NULL,

                target_type     ENUM('article','category','page','archive','module','system','custom_url') NOT NULL,
                target_id       BIGINT UNSIGNED NULL,
                target_meta     JSON NULL,
                custom_url      VARCHAR(500) NULL,

                open_new_tab    BOOLEAN DEFAULT FALSE,
                sort_order      INT DEFAULT 0,
                is_active       BOOLEAN DEFAULT TRUE,

                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
                INDEX idx_target (target_type, target_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS menu_items');
        $db->execute('DROP TABLE IF EXISTS menus');
    }
};
