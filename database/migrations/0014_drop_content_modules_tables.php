<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS module_contents');
        $db->execute('DROP TABLE IF EXISTS module_categories');
        $db->execute('DROP TABLE IF EXISTS content_modules');
    }

    public function down(Database $db): void
    {
        // One-way migration. In a real scenario, we might dump the schema, but we are discarding this feature.
        throw new \RuntimeException('Cannot reverse content module deletion.');
    }
};
