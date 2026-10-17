<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE IF NOT EXISTS comments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                article_id BIGINT UNSIGNED NOT NULL,
                author_name VARCHAR(100) NOT NULL,
                author_email VARCHAR(150) NOT NULL,
                content TEXT NOT NULL,
                `status` ENUM('pending', 'approved', 'spam', 'trash') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
            )
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute("DROP TABLE IF EXISTS comments");
    }
};
