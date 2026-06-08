<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
if (is_logged_in()) { redirect(is_admin() ? admin_url('index.php') : recruiter_url('dashboard.php')); }
$errors = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $user = get_user_by_email($pdo, $email);
    if (!$user || !password_verify($password, $user['password_hash'])) { $errors[] = 'Неверный email или пароль.'; }
    else { login_user($user); redirect($user['role'] === 'admin' ? admin_url('index.php') : recruiter_url('dashboard.php')); }
}
$pageTitle = 'Вход'; $bodyClass = 'auth-page'; require __DIR__ . '/../includes/header.php';
?>
<main class="auth-shell"><a class="brand" href="index.php"><span class="brand-mark">Я</span>Рекрутинг</a><section class="auth-card"><h1>Вход</h1><p>Введите email и пароль.</p><?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><form method="post" data-validate="true"><?= csrf_field() ?><label>Email<input type="email" name="email" value="<?= e(isset($_POST['email']) ? $_POST['email'] : '') ?>" required></label><label>Пароль<input type="password" name="password" required></label><button class="button button-full" type="submit">Войти</button></form><p class="auth-switch">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p></section></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
