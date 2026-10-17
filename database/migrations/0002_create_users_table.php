<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE users (
                id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name                        VARCHAR(150) NOT NULL,
                username                    VARCHAR(100) NOT NULL UNIQUE,
                email                       VARCHAR(255) NOT NULL UNIQUE,
                phone                       VARCHAR(30) NULL,
                password_hash               VARCHAR(255) NOT NULL,
                avatar_media_id             BIGINT UNSIGNED NULL,

                is_active                   BOOLEAN DEFAULT TRUE,
                email_verified_at           TIMESTAMP NULL,

                two_factor_enabled          BOOLEAN DEFAULT FALSE,
                two_factor_secret           VARCHAR(255) NULL,
                two_factor_recovery_codes   JSON NULL,

                last_login_at               TIMESTAMP NULL,
                last_login_ip               VARCHAR(45) NULL,

                created_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (avatar_media_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE password_reset_tokens (
                email           VARCHAR(255) NOT NULL PRIMARY KEY,
                token           VARCHAR(255) NOT NULL,
                expires_at      TIMESTAMP NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE user_sessions (
                id              VARCHAR(100) PRIMARY KEY,
                user_id         BIGINT UNSIGNED NOT NULL,
                ip_address      VARCHAR(45) NULL,
                user_agent      VARCHAR(255) NULL,
                last_active_at  TIMESTAMP NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS user_sessions');
        $db->execute('DROP TABLE IF EXISTS password_reset_tokens');
        $db->execute('DROP TABLE IF EXISTS users');
    }
};
