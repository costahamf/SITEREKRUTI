<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
if (is_logged_in()) { redirect(is_admin() ? admin_url('index.php') : recruiter_url('dashboard.php')); }
$errors = array(); $needsRecaptcha = true;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (failed_login_count($pdo, $email) >= 5) { $errors[] = 'Слишком много попыток входа. Попробуйте через 15 минут.'; }
    if (!$errors && !verify_recaptcha(isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '')) { $errors[] = 'Подтвердите, что вы не робот.'; }
    $user = !$errors ? get_user_by_email($pdo, $email) : null;
    if (!$errors && (!$user || !password_verify($password, $user['password_hash']))) { record_login_attempt($pdo, $email, false); $errors[] = 'Неверный email или пароль.'; }
    if (!$errors && isset($user['email_verified']) && (int)$user['email_verified'] === 0) { redirect('verify.php?email=' . urlencode($email)); }
    if (!$errors) { record_login_attempt($pdo, $email, true); login_user($user); redirect($user['role'] === 'admin' ? admin_url('index.php') : recruiter_url('dashboard.php')); }
}
$pageTitle = 'Вход'; $bodyClass = 'auth-page'; require __DIR__ . '/../includes/header.php';
?>
<main class="auth-shell"><a class="brand" href="index.php"><span class="brand-mark">Я</span>Рекрутинг</a><section class="auth-card"><h1>Вход</h1><p>Введите email и пароль.</p><?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><form method="post" data-validate="true"><?= csrf_field() ?><label>Email<input type="email" name="email" value="<?= e(isset($_POST['email']) ? $_POST['email'] : '') ?>" required></label><label>Пароль<input type="password" name="password" required></label><?php if (recaptcha_site_key()): ?><div class="g-recaptcha" data-sitekey="<?= e(recaptcha_site_key()) ?>"></div><?php endif; ?><button class="button button-full" type="submit">Войти</button></form><p class="auth-switch"><a href="forgot-password.php">Забыли пароль?</a></p><p class="auth-switch">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p></section></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
