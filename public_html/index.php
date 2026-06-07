<?php
require_once __DIR__ . '/functions.php';
ensure_default_settings($pdo);

$setupMode = !admin_exists($pdo);
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $setupMode) {
    verify_csrf();
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if ($fullName === '') {
        $errors[] = 'Укажите имя администратора.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный email.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role, referral_code) VALUES (?, ?, ?, 'admin', NULL)");
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $fullName]);
        $user = get_user_by_email($pdo, $email);
        login_user($user);
        redirect('admin.php');
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Рекрутинг курьеров Яндекс Еды</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<nav class="topbar">
    <a class="brand" href="index.php"><span class="brand-mark">Я</span> Рекрутинг</a>
    <div class="nav-actions">
        <?php if (is_logged_in()): ?>
            <a class="nav-link" href="<?= is_admin() ? 'admin.php' : 'dashboard.php' ?>">Кабинет</a>
            <a class="button button-outline" href="logout.php">Выйти</a>
        <?php else: ?>
            <a class="nav-link" href="login.php">Войти</a>
            <a class="button" href="register.php">Зарегистрироваться</a>
        <?php endif; ?>
    </div>
</nav>

<main>
    <section class="hero">
        <div class="hero-content">
            <p class="eyebrow">Партнёрская система для рекрутеров</p>
            <h1>Привлекайте курьеров и прозрачно считайте вознаграждение.</h1>
            <p class="hero-text">Каждый рекрутер получает персональную ссылку, курьеры регистрируются без лишних шагов, а кабинет автоматически считает заказы и выплаты.</p>
            <div class="hero-actions">
                <a class="button button-large" href="register.php">Начать работу</a>
                <a class="button button-large button-outline" href="login.php">Войти в кабинет</a>
            </div>
        </div>
        <div class="hero-card" aria-label="Краткая статистика">
            <div class="mini-stat"><i class="fa-solid fa-link"></i><span>Личная ссылка</span></div>
            <div class="mini-stat"><i class="fa-solid fa-user-plus"></i><span>Заявка курьера</span></div>
            <div class="mini-stat"><i class="fa-solid fa-chart-line"></i><span>Заказы и выплаты</span></div>
        </div>
    </section>

    <?php if ($setupMode): ?>
        <section class="setup-panel" id="setup">
            <div class="section-heading">
                <p class="eyebrow">Первый запуск</p>
                <h2>Создайте администратора</h2>
                <p>Администратор создаётся один раз, после чего вход будет доступен через обычную форму.</p>
            </div>
            <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
            <form class="form-card" method="post" novalidate>
                <?= csrf_field() ?>
                <label>Имя администратора<input type="text" name="full_name" required></label>
                <label>Email<input type="email" name="email" required></label>
                <label>Пароль<input type="password" name="password" minlength="8" required></label>
                <label>Повторите пароль<input type="password" name="password_confirm" minlength="8" required></label>
                <button class="button" type="submit">Создать администратора</button>
            </form>
        </section>
    <?php endif; ?>

    <section class="steps">
        <div class="section-heading">
            <p class="eyebrow">Как это работает</p>
            <h2>Три понятных шага</h2>
        </div>
        <div class="card-grid three">
            <article class="card"><i class="fa-solid fa-id-badge"></i><h3>Рекрутер регистрируется</h3><p>Система выдаёт уникальный код и ссылку для приглашения курьеров.</p></article>
            <article class="card"><i class="fa-solid fa-mobile-screen"></i><h3>Курьер оставляет заявку</h3><p>Форма занимает меньше минуты: имя, город и телефон.</p></article>
            <article class="card"><i class="fa-solid fa-ruble-sign"></i><h3>Начисляется доход</h3><p>Администратор обновляет заказы, кабинет считает выплаты автоматически.</p></article>
        </div>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>
