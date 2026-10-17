<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE tags (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name            VARCHAR(150) NOT NULL,
                slug            VARCHAR(150) NOT NULL UNIQUE,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE article_tags (
                article_id      BIGINT UNSIGNED NOT NULL,
                tag_id          BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (article_id, tag_id),
                FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE article_revisions (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                article_id      BIGINT UNSIGNED NOT NULL,
                title           VARCHAR(255) NOT NULL,
                excerpt         VARCHAR(500) NULL,
                content         LONGTEXT,
                author_id       BIGINT UNSIGNED NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS article_revisions');
        $db->execute('DROP TABLE IF EXISTS article_tags');
        $db->execute('DROP TABLE IF EXISTS tags');
    }
};
