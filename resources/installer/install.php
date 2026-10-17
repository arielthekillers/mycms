<?php

declare(strict_types=1);

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dbHost = $_POST['db_host'] ?? '127.0.0.1';
        $dbPort = $_POST['db_port'] ?? '3306';
        $dbName = $_POST['db_name'] ?? '';
        $dbUser = $_POST['db_user'] ?? '';
        $dbPass = $_POST['db_pass'] ?? '';

        $adminName = $_POST['admin_name'] ?? '';
        $adminUsername = $_POST['admin_username'] ?? '';
        $adminEmail = $_POST['admin_email'] ?? '';
        $adminPass = $_POST['admin_password'] ?? '';

        if (empty($dbName) || empty($dbUser) || empty($adminName) || empty($adminUsername) || empty($adminEmail) || empty($adminPass)) {
            throw new \Exception("Mohon lengkapi semua field yang wajib diisi.");
        }

        // Test Database Connection
        $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $dbHost, $dbPort);
        $pdo = new \PDO($dsn, $dbUser, $dbPass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

        // Create Database if not exists
        $pdo->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', str_replace('`', '``', $dbName)));
        $pdo->exec(sprintf('USE `%s`', str_replace('`', '``', $dbName)));

        // Write .env file
        $envContent = <<<ENV
APP_URL=http://localhost:8000
APP_ENV=production

DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

SESSION_NAME=pesantren_session
SESSION_LIFETIME=120

CACHE_ENABLED=true
CACHE_TTL=300
ENV;
        $basePath = dirname(__DIR__, 2);
        file_put_contents($basePath . '/.env', $envContent);

        // Load env for the current process
        $_ENV['DB_HOST'] = $dbHost;
        $_ENV['DB_PORT'] = $dbPort;
        $_ENV['DB_DATABASE'] = $dbName;
        $_ENV['DB_USERNAME'] = $dbUser;
        $_ENV['DB_PASSWORD'] = $dbPass;
        $_ENV['SESSION_NAME'] = 'pesantren_session';
        $_ENV['SESSION_LIFETIME'] = 120;
        
        // Initialize Core Application
        $app = \App\Core\Application::bootstrap($basePath);

        // Run Migrations
        $migrator = new \App\Core\Migrator($app->db, $app->migrationsPath());
        $migrator->migrate();

        // Run Seeders
        require_once $basePath . '/database/Seeders/DefaultModulesSeeder.php';
        require_once $basePath . '/database/Seeders/RolePermissionSeeder.php';
        require_once $basePath . '/database/Seeders/ThemeSeeder.php';

        (new \Database\Seeders\DefaultModulesSeeder())->run($app->db);
        (new \Database\Seeders\RolePermissionSeeder())->run($app->db);
        (new \Database\Seeders\ThemeSeeder())->run($app->db);

        // Create Admin
        $userId = $app->db->insert(
            'INSERT INTO users (name, username, email, password_hash, is_active)
             VALUES (:name, :username, :email, :password_hash, 1)',
            [
                'name' => $adminName,
                'username' => $adminUsername,
                'email' => $adminEmail,
                'password_hash' => \App\Core\Auth\AuthManager::hashPassword($adminPass),
            ]
        );
        $app->rbac->assignRole($userId, 'super-admin');

        $success = true;

    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalasi Pesantren CMS</title>
    <style>
        :root {
            --primary: #0f766e;
            --primary-hover: #0d9488;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --border: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 2rem 1rem;
            display: flex;
            justify-content: center;
        }
        .container {
            background: var(--card-bg);
            max-width: 600px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            padding: 2rem;
        }
        h1 {
            margin-top: 0;
            color: var(--primary);
            text-align: center;
            font-size: 1.8rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .alert-error {
            background: #fef2f2;
            color: var(--danger);
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #ecfdf5;
            color: var(--success);
            border: 1px solid #a7f3d0;
            text-align: center;
        }
        .section-title {
            border-bottom: 2px solid var(--border);
            padding-bottom: 0.5rem;
            margin: 2rem 0 1rem;
            font-size: 1.2rem;
            color: #475569;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.95rem;
        }
        input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
        }
        .row {
            display: flex;
            gap: 1rem;
        }
        .col {
            flex: 1;
        }
        button {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 1rem;
            transition: background 0.2s;
        }
        button:hover {
            background: var(--primary-hover);
        }
        .btn-link {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Instalasi Pesantren CMS</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">Gagal: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <h3>Instalasi Berhasil! 🎉</h3>
                <p>Website Pesantren CMS Anda siap digunakan.</p>
                <a href="/admin/login" class="btn-link">Masuk ke Panel Admin</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="section-title">1. Konfigurasi Database</div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Host Database</label>
                            <input type="text" name="db_host" value="127.0.0.1" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Port</label>
                            <input type="number" name="db_port" value="3306" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nama Database</label>
                    <input type="text" name="db_name" value="pesantren_cms" required>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Username Database</label>
                            <input type="text" name="db_user" value="root" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Password Database</label>
                            <input type="password" name="db_pass">
                        </div>
                    </div>
                </div>

                <div class="section-title">2. Profil Administrator</div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="admin_name" required>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="admin_username" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="admin_email" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password Login</label>
                    <input type="password" name="admin_password" required>
                </div>

                <button type="submit">Install Pesantren CMS Sekarang</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
