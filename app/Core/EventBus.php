<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal hook/event system so modules and the theme layer can extend
 * core behaviour without the core knowing about them (e.g. module
 * registration, media attach/detach, widget registration).
 */
final class EventBus
{
    private static ?EventBus $instance = null;

    /** @var array<string, array<int, callable>> */
    private array $listeners = [];

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function listen(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    /**
     * Dispatch an event to all listeners in registration order.
     * Returns the (possibly mutated) payload so listeners can act as filters too.
     */
    public function dispatch(string $event, mixed $payload = null): mixed
    {
        foreach ($this->listeners[$event] ?? [] as $listener) {
            $result = $listener($payload);
            if ($result !== null) {
                $payload = $result;
            }
        }

        return $payload;
    }

    public function hasListeners(string $event): bool
    {
        return !empty($this->listeners[$event]);
    }
}
