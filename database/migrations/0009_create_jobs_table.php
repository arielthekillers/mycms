<?php

declare(strict_types=1);

use App\Core\Database;

return new class {
    public function up(Database $db): void
    {
        $db->execute(<<<SQL
            CREATE TABLE jobs (
                id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                queue           VARCHAR(100) NOT NULL DEFAULT 'default',
                job_class       VARCHAR(255) NOT NULL,
                payload         JSON NOT NULL,
                attempts        INT UNSIGNED DEFAULT 0,
                available_at    TIMESTAMP NOT NULL,
                reserved_at     TIMESTAMP NULL,
                last_error      TEXT NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                INDEX idx_queue_pickup (queue, reserved_at, available_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);
    }

    public function down(Database $db): void
    {
        $db->execute('DROP TABLE IF EXISTS jobs');
    }
};
