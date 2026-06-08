<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
$errors = array(); $message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Укажите корректный email.'; }
    else {
        $user = get_user_by_email($pdo, $email);
        if ($user) { create_password_reset($pdo, $email); }
        redirect('reset-password.php?email=' . urlencode($email));
    }
}
$pageTitle = 'Восстановление пароля'; $bodyClass = 'auth-page'; require __DIR__ . '/../includes/header.php';
?>
<main class="auth-shell"><a class="brand" href="index.php"><span class="brand-mark">Я</span>Рекрутинг</a><section class="auth-card"><h1>Забыли пароль?</h1><p>Введите email — если он зарегистрирован, мы отправим код восстановления.</p><?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><label>Email<input type="email" name="email" required></label><button class="button button-full">Получить код</button></form><p class="auth-switch"><a href="login.php">Вернуться ко входу</a></p></section></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
