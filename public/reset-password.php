<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
$email = strtolower(trim(isset($_GET['email']) ? $_GET['email'] : (isset($_POST['email']) ? $_POST['email'] : '')));
$errors = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $code = trim(isset($_POST['code']) ? $_POST['code'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    if (strlen($password) < 8) { $errors[] = 'Пароль должен содержать минимум 8 символов.'; }
    if ($password !== $confirm) { $errors[] = 'Пароли не совпадают.'; }
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at >= NOW() ORDER BY id DESC LIMIT 1');
    $stmt->execute(array($email, $code));
    $reset = $stmt->fetch();
    if (!$reset) { $errors[] = 'Неверный или просроченный код.'; }
    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
        $stmt->execute(array(password_hash($password, PASSWORD_DEFAULT), $email));
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
        $stmt->execute(array($email));
        redirect('login.php?reset=1');
    }
}
$pageTitle = 'Новый пароль'; $bodyClass = 'auth-page'; require __DIR__ . '/../includes/header.php';
?>
<main class="auth-shell"><a class="brand" href="index.php"><span class="brand-mark">Я</span>Рекрутинг</a><section class="auth-card"><h1>Введите код и новый пароль</h1><p>Код отправлен на <?= e($email) ?>, если адрес зарегистрирован.</p><?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><input type="hidden" name="email" value="<?= e($email) ?>"><label>Код<input name="code" pattern="[0-9]{6}" maxlength="6" required></label><label>Новый пароль<input type="password" name="password" minlength="8" required></label><label>Повторите пароль<input type="password" name="password_confirm" minlength="8" required></label><button class="button button-full">Сменить пароль</button></form></section></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
