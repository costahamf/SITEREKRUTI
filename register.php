<?php
require_once __DIR__ . '/functions.php';
ensure_default_settings($pdo);
if (is_logged_in()) {
    redirect(is_admin() ? 'admin.php' : 'dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if ($fullName === '') {
        $errors[] = 'Укажите полное имя.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный email.';
    } elseif (get_user_by_email($pdo, $email)) {
        $errors[] = 'Пользователь с таким email уже существует.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (!$errors) {
        $code = generate_referral_code($pdo);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role, referral_code) VALUES (?, ?, ?, 'recruiter', ?)");
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $fullName, $code]);
        $user = get_user_by_email($pdo, $email);
        login_user($user);
        redirect('dashboard.php');
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация рекрутера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<main class="auth-shell">
    <a class="brand" href="index.php"><span class="brand-mark">Я</span> Рекрутинг</a>
    <section class="auth-card">
        <h1>Регистрация рекрутера</h1>
        <p>Создайте кабинет и получите персональную ссылку для курьеров.</p>
        <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <form method="post" data-validate="true" novalidate>
            <?= csrf_field() ?>
            <label>Полное имя<input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>" required></label>
            <label>Email<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
            <label>Пароль<input type="password" name="password" minlength="8" required></label>
            <label>Повторите пароль<input type="password" name="password_confirm" minlength="8" required></label>
            <button class="button button-full" type="submit">Зарегистрироваться</button>
        </form>
        <p class="auth-switch">Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>
