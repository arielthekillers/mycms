<?php

declare(strict_types=1);

namespace App\Core\Cache;

interface CacheInterface
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttlSeconds): void;

    public function delete(string $key): void;

    /** Clears everything. Coarse-grained on purpose for now — see README for the trade-off. */
    public function flush(): void;

    /** Get-or-compute: returns the cached value, or computes+stores it via $resolver on a miss. */
    public function remember(string $key, int $ttlSeconds, callable $resolver): mixed;
}
