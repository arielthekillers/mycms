<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute("ALTER TABLE articles ADD meta_title VARCHAR(255) NULL, ADD meta_description TEXT NULL");
        $db->execute("ALTER TABLE pages ADD meta_title VARCHAR(255) NULL, ADD meta_description TEXT NULL");
        $db->execute("ALTER TABLE module_contents ADD meta_title VARCHAR(255) NULL, ADD meta_description TEXT NULL");
    }

    public function down(Database $db): void
    {
        $db->execute("ALTER TABLE articles DROP COLUMN meta_title, DROP COLUMN meta_description");
        $db->execute("ALTER TABLE pages DROP COLUMN meta_title, DROP COLUMN meta_description");
        $db->execute("ALTER TABLE module_contents DROP COLUMN meta_title, DROP COLUMN meta_description");
    }
};
