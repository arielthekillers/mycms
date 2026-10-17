<?php

declare(strict_types=1);

namespace App\Core\Cache;

final class NullCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        return null;
    }

    public function set(string $key, mixed $value, int $ttlSeconds): void
    {
        // no-op
    }

    public function delete(string $key): void
    {
        // no-op
    }

    public function flush(): void
    {
        // no-op
    }

    public function remember(string $key, int $ttlSeconds, callable $resolver): mixed
    {
        return $resolver();
    }
}
