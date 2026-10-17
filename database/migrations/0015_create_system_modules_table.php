<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute("
            CREATE TABLE system_modules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                directory VARCHAR(100) NOT NULL UNIQUE,
                is_active TINYINT(1) NOT NULL DEFAULT 0,
                version VARCHAR(20) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS system_modules');
    }
};
