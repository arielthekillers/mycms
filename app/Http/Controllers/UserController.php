<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth\AuthManager;
use App\Core\Auth\Rbac;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class UserController
{
    public function __construct(
        private Database $db,
        private AuthManager $auth,
        private Rbac $rbac
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $userId = (int) $this->auth->user()['id'];
        if (!$this->rbac->can($userId, 'users.manage')) {
            return $response->status(403)->json(['error' => 'Forbidden']);
        }

        $users = $this->db->select("
            SELECT u.id, u.name, u.username, u.email, u.phone, u.is_active, u.created_at, m.disk_path as avatar_url
            FROM users u
            LEFT JOIN media m ON u.avatar_media_id = m.id
            ORDER BY u.created_at DESC
        ");

        // get roles
        foreach ($users as &$u) {
            $roles = $this->db->select("
                SELECT r.name FROM roles r
                JOIN model_has_roles mhr ON r.id = mhr.role_id
                WHERE mhr.model_type = 'user' AND mhr.model_id = ?
            ", [$u['id']]);
            $u['roles'] = array_column($roles, 'name');
            if (isset($u['avatar_url'])) {
                $u['avatar_url'] = '/uploads/' . $u['avatar_url'];
            }
        }

        return $response->json(['data' => $users]);
    }

    public function store(Request $request, Response $response): Response
    {
        $userId = (int) $this->auth->user()['id'];
        if (!$this->rbac->can($userId, 'users.manage')) {
            return $response->status(403)->json(['error' => 'Forbidden']);
        }

        $input = $request->json();
        
        if (empty($input['name']) || empty($input['username']) || empty($input['email']) || empty($input['password'])) {
            return $response->status(400)->json(['error' => 'Nama, username, email, dan password wajib diisi.']);
        }

        $exists = $this->db->selectOne("SELECT id FROM users WHERE username = ? OR email = ?", [$input['username'], $input['email']]);
        if ($exists) {
            return $response->status(400)->json(['error' => 'Username atau email sudah digunakan.']);
        }

        $hash = password_hash($input['password'], PASSWORD_DEFAULT);
        
        $this->db->execute(
            "INSERT INTO users (name, username, email, phone, password_hash, is_active) VALUES (?, ?, ?, ?, ?, ?)",
            [$input['name'], $input['username'], $input['email'], $input['phone'] ?? null, $hash, $input['is_active'] ?? 1]
        );

        $newUserId = (int) $this->db->pdo()->lastInsertId();

        if (!empty($input['roles']) && is_array($input['roles'])) {
            foreach ($input['roles'] as $roleName) {
                $this->rbac->assignRole($newUserId, $roleName);
            }
        }

        return $response->status(201)->json(['success' => true]);
    }

    public function update(Request $request, Response $response): Response
    {
        $userId = (int) $this->auth->user()['id'];
        if (!$this->rbac->can($userId, 'users.manage')) {
            return $response->status(403)->json(['error' => 'Forbidden']);
        }

        $id = (int) $request->param('id');
        $input = $request->json();

        if (empty($input['name']) || empty($input['username']) || empty($input['email'])) {
            return $response->status(400)->json(['error' => 'Nama, username, dan email wajib diisi.']);
        }

        $exists = $this->db->selectOne("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?", [$input['username'], $input['email'], $id]);
        if ($exists) {
            return $response->status(400)->json(['error' => 'Username atau email sudah digunakan.']);
        }

        $this->db->execute(
            "UPDATE users SET name = ?, username = ?, email = ?, phone = ?, is_active = ? WHERE id = ?",
            [$input['name'], $input['username'], $input['email'], $input['phone'] ?? null, $input['is_active'] ?? 1, $id]
        );

        if (!empty($input['password'])) {
            $hash = password_hash($input['password'], PASSWORD_DEFAULT);
            $this->db->execute("UPDATE users SET password_hash = ? WHERE id = ?", [$hash, $id]);
        }

        if (isset($input['roles']) && is_array($input['roles'])) {
            $this->db->execute("DELETE FROM model_has_roles WHERE model_type = 'user' AND model_id = ?", [$id]);
            foreach ($input['roles'] as $roleName) {
                $this->rbac->assignRole($id, $roleName);
            }
        }

        return $response->json(['success' => true]);
    }

    public function destroy(Request $request, Response $response): Response
    {
        $userId = (int) $this->auth->user()['id'];
        if (!$this->rbac->can($userId, 'users.manage')) {
            return $response->status(403)->json(['error' => 'Forbidden']);
        }

        $id = (int) $request->param('id');
        
        if ($id === $userId) {
            return $response->status(400)->json(['error' => 'Anda tidak bisa menghapus diri sendiri.']);
        }

        $this->db->execute("DELETE FROM model_has_roles WHERE model_type = 'user' AND model_id = ?", [$id]);
        $this->db->execute("DELETE FROM model_has_permissions WHERE model_type = 'user' AND model_id = ?", [$id]);
        $this->db->execute("DELETE FROM users WHERE id = ?", [$id]);

        return $response->json(['success' => true]);
    }

    public function check(Request $request, Response $response): Response
    {
        $userId = (int) $this->auth->user()['id'];
        if (!$this->rbac->can($userId, 'users.manage')) {
            return $response->status(403)->json(['error' => 'Forbidden']);
        }

        $username = $request->query('username');
        $email = $request->query('email');
        $excludeId = (int) $request->query('exclude_id', 0);

        if ($username) {
            $exists = $this->db->selectOne("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $excludeId]);
            return $response->json(['available' => !$exists]);
        }
        if ($email) {
            $exists = $this->db->selectOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $excludeId]);
            return $response->json(['available' => !$exists]);
        }

        return $response->json(['available' => true]);
    }

    public function roles(Request $request, Response $response): Response
    {
        $userId = (int) $this->auth->user()['id'];
        if (!$this->rbac->can($userId, 'users.manage')) {
            return $response->status(403)->json(['error' => 'Forbidden']);
        }

        $roles = $this->db->select("SELECT name, label FROM roles ORDER BY id ASC");
        return $response->json(['data' => $roles]);
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $userId = (int) $this->auth->user()['id'];
        $user = $this->db->selectOne("
            SELECT u.id, u.name, u.username, u.email, u.phone, u.avatar_media_id, m.disk_path as avatar_url
            FROM users u
            LEFT JOIN media m ON u.avatar_media_id = m.id
            WHERE u.id = ?
        ", [$userId]);

        if (isset($user['avatar_url'])) {
            $user['avatar_url'] = '/uploads/' . $user['avatar_url'];
        }

        return $response->json(['data' => $user]);
    }

    public function updateProfile(Request $request, Response $response): Response
    {
        $userId = (int) $this->auth->user()['id'];
        $input = $request->json();

        if (empty($input['name']) || empty($input['email'])) {
            return $response->status(400)->json(['error' => 'Nama dan email wajib diisi.']);
        }

        $exists = $this->db->selectOne("SELECT id FROM users WHERE email = ? AND id != ?", [$input['email'], $userId]);
        if ($exists) {
            return $response->status(400)->json(['error' => 'Email sudah digunakan oleh orang lain.']);
        }
        
        $currentUser = $this->db->selectOne("SELECT password_hash FROM users WHERE id = ?", [$userId]);

        $avatarId = !empty($input['avatar_media_id']) ? (int) $input['avatar_media_id'] : null;

        $this->db->execute(
            "UPDATE users SET name = ?, email = ?, phone = ?, avatar_media_id = ? WHERE id = ?",
            [$input['name'], $input['email'], $input['phone'] ?? null, $avatarId, $userId]
        );

        if (!empty($input['password'])) {
            if (empty($input['old_password'])) {
                return $response->status(400)->json(['error' => 'Kata sandi lama wajib diisi.']);
            }
            if (!password_verify($input['old_password'], $currentUser['password_hash'])) {
                return $response->status(400)->json(['error' => 'Kata sandi lama salah.']);
            }
            if (strlen($input['password']) < 6) {
                return $response->status(400)->json(['error' => 'Password minimal 6 karakter.']);
            }
            $hash = password_hash($input['password'], PASSWORD_DEFAULT);
            $this->db->execute("UPDATE users SET password_hash = ? WHERE id = ?", [$hash, $userId]);
        }

        return $response->json(['success' => true]);
    }
}
