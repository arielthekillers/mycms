<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Hand-rolled migration runner (native PHP, no Doctrine/Phinx).
 *
 * Convention: each file in database/migrations/ is named
 * "NNNN_description.php" and returns an anonymous class exposing
 * up(Database $db) and down(Database $db).
 */
final class Migrator
{
    private Database $db;
    private string $migrationsPath;

    public function __construct(Database $db, string $migrationsPath)
    {
        $this->db = $db;
        $this->migrationsPath = rtrim($migrationsPath, '/');
        $this->ensureMigrationsTableExists();
    }

    private function ensureMigrationsTableExists(): void
    {
        $this->db->execute(<<<SQL
            CREATE TABLE IF NOT EXISTS migrations (
                id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration   VARCHAR(255) NOT NULL UNIQUE,
                batch       INT UNSIGNED NOT NULL,
                applied_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        SQL);
    }

    /** @return string[] migration file basenames, sorted */
    private function allMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php') ?: [];
        sort($files);

        return $files;
    }

    /** @return string[] migration names already applied */
    private function appliedMigrations(): array
    {
        $rows = $this->db->select('SELECT migration FROM migrations ORDER BY id');

        return array_column($rows, 'migration');
    }

    private function nextBatchNumber(): int
    {
        $row = $this->db->selectOne('SELECT MAX(batch) AS max_batch FROM migrations');

        return (int) ($row['max_batch'] ?? 0) + 1;
    }

    private function loadMigration(string $file): object
    {
        $migration = require $file;

        if (!is_object($migration) || !method_exists($migration, 'up') || !method_exists($migration, 'down')) {
            throw new RuntimeException("Invalid migration file: {$file} must return an object with up()/down().");
        }

        return $migration;
    }

    /**
     * Run all pending migrations. Returns list of migration names that were applied.
     */
    public function migrate(): array
    {
        $applied = $this->appliedMigrations();
        $batch = $this->nextBatchNumber();
        $ran = [];

        foreach ($this->allMigrationFiles() as $file) {
            $name = basename($file, '.php');

            if (in_array($name, $applied, true)) {
                continue;
            }

            $migration = $this->loadMigration($file);

            $this->db->transaction(function () use ($migration, $name, $batch) {
                $migration->up($this->db);
                $this->db->insert(
                    'INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)',
                    ['migration' => $name, 'batch' => $batch]
                );
            });

            $ran[] = $name;
        }

        return $ran;
    }

    /**
     * Roll back the most recent batch. Returns list of migration names that were reverted.
     */
    public function rollback(): array
    {
        $lastBatchRow = $this->db->selectOne('SELECT MAX(batch) AS batch FROM migrations');
        $lastBatch = (int) ($lastBatchRow['batch'] ?? 0);

        if ($lastBatch === 0) {
            return [];
        }

        $rows = $this->db->select(
            'SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC',
            ['batch' => $lastBatch]
        );

        $reverted = [];

        foreach ($rows as $row) {
            $name = $row['migration'];
            $file = $this->migrationsPath . '/' . $name . '.php';

            if (!file_exists($file)) {
                throw new RuntimeException("Cannot rollback: migration file missing for {$name}");
            }

            $migration = $this->loadMigration($file);

            $this->db->transaction(function () use ($migration, $name) {
                $migration->down($this->db);
                $this->db->execute('DELETE FROM migrations WHERE migration = :migration', ['migration' => $name]);
            });

            $reverted[] = $name;
        }

        return $reverted;
    }

    /** @return array<int, array{migration: string, applied: bool}> */
    public function status(): array
    {
        $applied = $this->appliedMigrations();
        $status = [];

        foreach ($this->allMigrationFiles() as $file) {
            $name = basename($file, '.php');
            $status[] = ['migration' => $name, 'applied' => in_array($name, $applied, true)];
        }

        return $status;
    }
}
