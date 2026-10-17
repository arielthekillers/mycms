<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE roles (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name            VARCHAR(100) NOT NULL UNIQUE,
                label           VARCHAR(150) NOT NULL,
                is_system       BOOLEAN DEFAULT FALSE,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE permissions (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                key_name        VARCHAR(150) NOT NULL UNIQUE,
                group_name      VARCHAR(100) NOT NULL,
                label           VARCHAR(150) NOT NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE role_permissions (
                role_id         BIGINT UNSIGNED NOT NULL,
                permission_id   BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (role_id, permission_id),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE model_has_roles (
                role_id         BIGINT UNSIGNED NOT NULL,
                model_type      VARCHAR(100) NOT NULL,
                model_id        BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (role_id, model_type, model_id),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                INDEX idx_model (model_type, model_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE model_has_permissions (
                permission_id   BIGINT UNSIGNED NOT NULL,
                model_type      VARCHAR(100) NOT NULL,
                model_id        BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (permission_id, model_type, model_id),
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
                INDEX idx_model (model_type, model_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS model_has_permissions');
        $db->execute('DROP TABLE IF EXISTS model_has_roles');
        $db->execute('DROP TABLE IF EXISTS role_permissions');
        $db->execute('DROP TABLE IF EXISTS permissions');
        $db->execute('DROP TABLE IF EXISTS roles');
    }
};
