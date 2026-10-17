<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE categories (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                parent_id       BIGINT UNSIGNED NULL,
                name            VARCHAR(150) NOT NULL,
                slug            VARCHAR(150) NOT NULL UNIQUE,
                description     VARCHAR(500) NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE articles (
                id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title               VARCHAR(255) NOT NULL,
                slug                VARCHAR(255) NOT NULL UNIQUE,
                excerpt             VARCHAR(500) NULL,
                content             LONGTEXT,
                featured_media_id   BIGINT UNSIGNED NULL,
                author_id           BIGINT UNSIGNED NULL,
                status              ENUM('draft','published','archived','deleted') DEFAULT 'draft',
                published_at        TIMESTAMP NULL,
                created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (featured_media_id) REFERENCES media(id) ON DELETE SET NULL,
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_status (status, published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE article_categories (
                article_id      BIGINT UNSIGNED NOT NULL,
                category_id     BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (article_id, category_id),
                FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS article_categories');
        $db->execute('DROP TABLE IF EXISTS articles');
        $db->execute('DROP TABLE IF EXISTS categories');
    }
};
