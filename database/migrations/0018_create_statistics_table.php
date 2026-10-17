<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE IF NOT EXISTS statistics_daily (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `date` DATE NOT NULL,
                `path` VARCHAR(255) NOT NULL,
                views INT UNSIGNED DEFAULT 0,
                unique_visitors INT UNSIGNED DEFAULT 0,
                UNIQUE KEY `date_path` (`date`, `path`)
            )
        SQL);

        $db->execute(<<<SQL
            CREATE TABLE IF NOT EXISTS statistics_hits (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `path` VARCHAR(255) NOT NULL,
                ip_hash VARCHAR(64) NOT NULL,
                user_agent VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute("DROP TABLE IF EXISTS statistics_hits");
        $db->execute("DROP TABLE IF EXISTS statistics_daily");
    }
};
