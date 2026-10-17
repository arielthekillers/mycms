<?php

declare(strict_types=1);

namespace App\Core\Cache;

final class FileCache implements CacheInterface
{
    public function __construct(private string $directory)
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    public function get(string $key): mixed
    {
        $path = $this->pathFor($key);

        if (!is_file($path)) {
            return null;
        }

        $payload = @unserialize((string) file_get_contents($path));

        if (!is_array($payload) || !isset($payload['expires_at'], $payload['value'])) {
            @unlink($path);

            return null;
        }

        if ($payload['expires_at'] < time()) {
            @unlink($path);

            return null;
        }

        return $payload['value'];
    }

    public function set(string $key, mixed $value, int $ttlSeconds): void
    {
        $payload = ['expires_at' => time() + $ttlSeconds, 'value' => $value];
        file_put_contents($this->pathFor($key), serialize($payload), LOCK_EX);
    }

    public function delete(string $key): void
    {
        @unlink($this->pathFor($key));
    }

    public function flush(): void
    {
        foreach (glob($this->directory . '/*.cache') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function remember(string $key, int $ttlSeconds, callable $resolver): mixed
    {
        $cached = $this->get($key);

        if ($cached !== null) {
            return $cached;
        }

        $value = $resolver();
        $this->set($key, $value, $ttlSeconds);

        return $value;
    }

    private function pathFor(string $key): string
    {
        return $this->directory . '/' . hash('sha256', $key) . '.cache';
    }
}
