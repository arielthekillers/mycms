<?php

declare(strict_types=1);

namespace App\Core\Queue;

use App\Core\Database;
use Throwable;

final class DatabaseQueue implements QueueInterface
{
    public function __construct(private Database $db, private int $maxAttempts = 3)
    {
    }

    public function push(string $jobClass, array $payload, string $queue = 'default'): void
    {
        $this->db->insert(
            'INSERT INTO jobs (queue, job_class, payload, available_at) VALUES (:queue, :job_class, :payload, NOW())',
            [
                'queue' => $queue,
                'job_class' => $jobClass,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]
        );
    }

    /** Process a single pending job. Returns false if the queue was empty. */
    public function workOnce(string $queue = 'default'): bool
    {
        $job = $this->db->selectOne(
            'SELECT * FROM jobs
             WHERE queue = :queue AND reserved_at IS NULL AND available_at <= NOW()
             ORDER BY id ASC LIMIT 1',
            ['queue' => $queue]
        );

        if ($job === null) {
            return false;
        }

        $this->db->execute('UPDATE jobs SET reserved_at = NOW() WHERE id = :id', ['id' => $job['id']]);

        try {
            /** @var class-string<ShouldQueue> $class */
            $class = $job['job_class'];
            $payload = json_decode($job['payload'], true) ?: [];

            $class::handle($this->db, $payload);

            $this->db->execute('DELETE FROM jobs WHERE id = :id', ['id' => $job['id']]);
        } catch (Throwable $e) {
            $attempts = (int) $job['attempts'] + 1;

            if ($attempts >= $this->maxAttempts) {
                // Give up: keep the row (with reserved_at set) as a dead-letter record for inspection.
                $this->db->execute(
                    'UPDATE jobs SET attempts = :attempts, last_error = :error WHERE id = :id',
                    ['attempts' => $attempts, 'error' => $e->getMessage(), 'id' => $job['id']]
                );
            } else {
                $this->db->execute(
                    'UPDATE jobs SET reserved_at = NULL, attempts = :attempts, last_error = :error WHERE id = :id',
                    ['attempts' => $attempts, 'error' => $e->getMessage(), 'id' => $job['id']]
                );
            }
        }

        return true;
    }

    /** Drain up to $limit pending jobs right now (used for CLI one-shot runs). */
    public function workBatch(string $queue = 'default', int $limit = 50): int
    {
        $processed = 0;

        while ($processed < $limit && $this->workOnce($queue)) {
            $processed++;
        }

        return $processed;
    }
}
