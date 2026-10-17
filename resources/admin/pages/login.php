<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Masuk — Pesantren CMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/admin/admin.css">
</head>
<body class="login-body">
    <form class="login-card" method="post" action="/admin/login">
        <h1>Pesantren CMS</h1>
        <p class="login-subtitle">Masuk ke panel admin</p>

        <input type="hidden" name="_token" value="<?= \App\Core\Auth\Session::csrfToken() ?>">

        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label>
            Username atau Email
            <input type="text" name="identifier" required autofocus>
        </label>

        <label>
            Kata Sandi
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>
</body>
</html>
