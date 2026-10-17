<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            ALTER TABLE comments 
            ADD COLUMN parent_id BIGINT UNSIGNED NULL AFTER article_id,
            ADD CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute("ALTER TABLE comments DROP FOREIGN KEY fk_comments_parent");
        $db->execute("ALTER TABLE comments DROP COLUMN parent_id");
    }
};
