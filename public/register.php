<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
if (is_logged_in()) { redirect(is_admin() ? admin_url('index.php') : recruiter_url('dashboard.php')); }
$errors = array(); $needsRecaptcha = true;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullName = trim(isset($_POST['full_name']) ? $_POST['full_name'] : '');
    $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    if ($fullName === '') { $errors[] = 'Укажите полное имя.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Укажите корректный email.'; }
    elseif (get_user_by_email($pdo, $email)) { $errors[] = 'Пользователь с таким email уже существует.'; }
    if (strlen($password) < 8) { $errors[] = 'Пароль должен содержать минимум 8 символов.'; }
    if ($password !== $confirm) { $errors[] = 'Пароли не совпадают.'; }
    if (!verify_recaptcha(isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '')) { $errors[] = 'Подтвердите, что вы не робот.'; }
    if (!$errors) {
        $code = generate_referral_code($pdo);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role, referral_code, email_verified) VALUES (?, ?, ?, 'recruiter', ?, 0)");
        $stmt->execute(array($email, password_hash($password, PASSWORD_DEFAULT), $fullName, $code));
        send_verification_code($pdo, $email);
        redirect('verify.php?email=' . urlencode($email));
    }
}
$pageTitle = 'Регистрация рекрутера'; $bodyClass = 'auth-page'; require __DIR__ . '/../includes/header.php';
?>
<main class="auth-shell"><a class="brand" href="index.php"><span class="brand-mark">Я</span>Рекрутинг</a><section class="auth-card"><h1>Регистрация рекрутера</h1><p>Создайте кабинет и подтвердите email кодом.</p><?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><form method="post" data-validate="true"><?= csrf_field() ?><label>Полное имя<input type="text" name="full_name" value="<?= e(isset($_POST['full_name']) ? $_POST['full_name'] : '') ?>" required></label><label>Email<input type="email" name="email" value="<?= e(isset($_POST['email']) ? $_POST['email'] : '') ?>" required></label><label>Пароль<input type="password" name="password" minlength="8" required></label><label>Повторите пароль<input type="password" name="password_confirm" minlength="8" required></label><?php if (recaptcha_site_key()): ?><div class="g-recaptcha" data-sitekey="<?= e(recaptcha_site_key()) ?>"></div><?php endif; ?><button class="button button-full" type="submit">Зарегистрироваться</button></form><p class="auth-switch">Уже есть аккаунт? <a href="login.php">Войти</a></p></section></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
