<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute("RENAME TABLE system_modules TO system_plugins");
    }

    public function down(Database $db): void
    {
        $db->execute("RENAME TABLE system_plugins TO system_modules");
    }
};
