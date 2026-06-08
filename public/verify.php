<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
$email = strtolower(trim(isset($_GET['email']) ? $_GET['email'] : (isset($_POST['email']) ? $_POST['email'] : '')));
$errors = array(); $message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) ? $_POST['action'] : 'verify';
    $user = get_user_by_email($pdo, $email);
    if (!$user) { $errors[] = 'Пользователь не найден.'; }
    elseif ($action === 'resend') {
        $last = isset($user['last_verification_sent_at']) ? strtotime($user['last_verification_sent_at']) : 0;
        if ($last && time() - $last < 60) { $errors[] = 'Повторная отправка доступна через 60 секунд.'; }
        else { send_verification_code($pdo, $email); $message = 'Код отправлен повторно.'; }
    } else {
        $code = trim(isset($_POST['code']) ? $_POST['code'] : '');
        if ($code !== $user['email_verification_code'] || strtotime($user['email_verification_expires_at']) < time()) { $errors[] = 'Неверный или просроченный код.'; }
        else { $stmt = $pdo->prepare('UPDATE users SET email_verified = 1, email_verification_code = NULL, email_verification_expires_at = NULL WHERE email = ?'); $stmt->execute(array($email)); redirect('login.php?verified=1'); }
    }
}
$pageTitle = 'Подтверждение почты'; $bodyClass = 'auth-page'; require __DIR__ . '/../includes/header.php';
?>
<main class="auth-shell"><a class="brand" href="index.php"><span class="brand-mark">Я</span>Рекрутинг</a><section class="auth-card"><h1>Подтвердите email</h1><p>Введите 6-значный код, отправленный на <?= e($email) ?>.</p><?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><input type="hidden" name="email" value="<?= e($email) ?>"><input type="hidden" name="action" value="verify"><label>Код<input name="code" pattern="[0-9]{6}" maxlength="6" required></label><button class="button button-full">Подтвердить</button></form><form method="post" class="inline-form resend-form" data-resend-delay="60"><?= csrf_field() ?><input type="hidden" name="email" value="<?= e($email) ?>"><input type="hidden" name="action" value="resend"><button class="button button-outline button-full">Отправить код повторно</button></form></section></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
