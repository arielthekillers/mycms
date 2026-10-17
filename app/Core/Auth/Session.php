<?php

declare(strict_types=1);

namespace App\Core\Auth;

final class Session
{
    private static bool $started = false;

    public static function start(?string $name = null, ?int $lifetimeMinutes = null): void
    {
        if (self::$started) {
            return;
        }

        if ($name !== null) {
            session_name($name);
        }

        if ($lifetimeMinutes !== null) {
            ini_set('session.gc_maxlifetime', (string) ($lifetimeMinutes * 60));
            session_set_cookie_params([
                'lifetime' => $lifetimeMinutes * 60,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        session_start();
        self::$started = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Call after login/logout to prevent session fixation. */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function csrfToken(): string
    {
        if (!self::has('_csrf_token')) {
            self::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('_csrf_token');
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
        self::$started = false;
    }
}
