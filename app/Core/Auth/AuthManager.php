<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Database;

final class AuthManager
{
    private const SESSION_KEY = 'auth_user_id';

    private Database $db;
    /** @var array<string, mixed>|null request-scoped cache of the resolved user */
    private ?array $resolvedUser = null;
    private bool $resolvedUserLoaded = false;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Attempt to authenticate by username or email + plain password.
     * On success, starts the session identity and returns true.
     */
    public function attempt(string $identifier, string $password): bool
    {
        $user = $this->db->selectOne(
            'SELECT * FROM users WHERE (username = :username OR email = :email) AND is_active = 1 LIMIT 1',
            ['username' => $identifier, 'email' => $identifier]
        );

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $this->login($user);

        return true;
    }

    public function login(array $user): void
    {
        Session::set(self::SESSION_KEY, (int) $user['id']);
        Session::regenerate();

        $this->resolvedUser = $user;
        $this->resolvedUserLoaded = true;

        $this->db->execute(
            'UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id',
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? null, 'id' => $user['id']]
        );
    }

    public function logout(): void
    {
        Session::remove(self::SESSION_KEY);
        Session::regenerate();
        $this->resolvedUser = null;
        $this->resolvedUserLoaded = true;
    }

    public function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return $id !== null ? (int) $id : null;
    }

    /** @return array<string, mixed>|null */
    public function user(): ?array
    {
        if ($this->resolvedUserLoaded) {
            return $this->resolvedUser;
        }

        $this->resolvedUserLoaded = true;
        $id = $this->id();

        if ($id === null) {
            return $this->resolvedUser = null;
        }

        return $this->resolvedUser = $this->db->selectOne(
            'SELECT * FROM users WHERE id = :id AND is_active = 1 LIMIT 1',
            ['id' => $id]
        );
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }
}
