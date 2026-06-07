<?php
require_once __DIR__ . '/functions.php';
ensure_default_settings($pdo);
if (is_logged_in()) {
    redirect(is_admin() ? 'admin.php' : 'dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $user = get_user_by_email($pdo, $email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Неверный email или пароль.';
    } else {
        login_user($user);
        redirect($user['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<main class="auth-shell">
    <a class="brand" href="index.php"><span class="brand-mark">Я</span> Рекрутинг</a>
    <section class="auth-card">
        <h1>Вход в кабинет</h1>
        <p>Введите email и пароль, чтобы продолжить.</p>
        <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <form method="post" data-validate="true" novalidate>
            <?= csrf_field() ?>
            <label>Email<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
            <label>Пароль<input type="password" name="password" required></label>
            <button class="button button-full" type="submit">Войти</button>
        </form>
        <p class="auth-switch">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>
