<?php

declare(strict_types=1);

namespace App\Core\Queue;

use App\Core\Database;

interface QueueInterface
{
    /** @param array<string, mixed> $payload */
    public function push(string $jobClass, array $payload, string $queue = 'default'): void;
}

/**
 * Jobs are plain classes with a static handler — no instantiation/serialization
 * complexity needed since payload is just JSON-encodable data.
 */
interface ShouldQueue
{
    /** @param array<string, mixed> $payload */
    public static function handle(Database $db, array $payload): void;
}
