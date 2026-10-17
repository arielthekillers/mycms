<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Database;

/**
 * Granular RBAC on top of: roles, permissions, role_permissions,
 * model_has_roles, model_has_permissions (see migrations 0006).
 *
 * 'super-admin' is treated as an implicit all-access role rather than
 * requiring every auto-generated module permission (see the
 * trg_module_permissions_generate trigger) to be re-synced to it —
 * this keeps adding a new module a one-step operation.
 */
final class Rbac
{
    private const SUPER_ADMIN_ROLE = 'super-admin';
    private const MODEL_TYPE_USER = 'user';

    private Database $db;

    /** @var array<int, string[]> per-user resolved permission key cache */
    private array $permissionCache = [];

    /** @var array<int, string[]> per-user resolved role name cache */
    private array $roleCache = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function can(int $userId, string $permissionKey): bool
    {
        if ($this->hasRole($userId, self::SUPER_ADMIN_ROLE)) {
            return true;
        }

        return in_array($permissionKey, $this->permissionsFor($userId), true);
    }

    /**
     * Effective permission keys for a user, for the admin UI to decide what
     * to show. Returns ['*'] for super-admin instead of enumerating every
     * permission — a new module's auto-generated permissions should count
     * as "granted" here too without this list going stale.
     *
     * @return string[]
     */
    public function permissionKeysFor(int $userId): array
    {
        if ($this->hasRole($userId, self::SUPER_ADMIN_ROLE)) {
            return ['*'];
        }

        return $this->permissionsFor($userId);
    }

    public function hasRole(int $userId, string $roleName): bool
    {
        return in_array($roleName, $this->rolesFor($userId), true);
    }

    public function assignRole(int $userId, string $roleName): void
    {
        $role = $this->db->selectOne('SELECT id FROM roles WHERE name = :name', ['name' => $roleName]);

        if ($role === null) {
            throw new \RuntimeException("Role '{$roleName}' does not exist.");
        }

        $this->db->execute(
            'INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id) VALUES (:role_id, :type, :model_id)',
            ['role_id' => $role['id'], 'type' => self::MODEL_TYPE_USER, 'model_id' => $userId]
        );

        unset($this->roleCache[$userId]);
    }

    public function revokeRole(int $userId, string $roleName): void
    {
        $this->db->execute(
            'DELETE mhr FROM model_has_roles mhr
             JOIN roles r ON r.id = mhr.role_id
             WHERE r.name = :name AND mhr.model_type = :type AND mhr.model_id = :model_id',
            ['name' => $roleName, 'type' => self::MODEL_TYPE_USER, 'model_id' => $userId]
        );

        unset($this->roleCache[$userId]);
    }

    /** @return string[] */
    private function rolesFor(int $userId): array
    {
        if (!isset($this->roleCache[$userId])) {
            $rows = $this->db->select(
                'SELECT r.name FROM roles r
                 JOIN model_has_roles mhr ON mhr.role_id = r.id
                 WHERE mhr.model_type = :type AND mhr.model_id = :model_id',
                ['type' => self::MODEL_TYPE_USER, 'model_id' => $userId]
            );

            $this->roleCache[$userId] = array_column($rows, 'name');
        }

        return $this->roleCache[$userId];
    }

    /** @return string[] */
    private function permissionsFor(int $userId): array
    {
        if (!isset($this->permissionCache[$userId])) {
            $rows = $this->db->select(
                <<<SQL
                SELECT p.key_name FROM permissions p
                JOIN role_permissions rp ON rp.permission_id = p.id
                JOIN model_has_roles mhr ON mhr.role_id = rp.role_id
                WHERE mhr.model_type = :type1 AND mhr.model_id = :model_id1

                UNION

                SELECT p.key_name FROM permissions p
                JOIN model_has_permissions mp ON mp.permission_id = p.id
                WHERE mp.model_type = :type2 AND mp.model_id = :model_id2
                SQL,
                [
                    'type1' => self::MODEL_TYPE_USER, 'model_id1' => $userId,
                    'type2' => self::MODEL_TYPE_USER, 'model_id2' => $userId,
                ]
            );

            $this->permissionCache[$userId] = array_column($rows, 'key_name');
        }

        return $this->permissionCache[$userId];
    }
}
