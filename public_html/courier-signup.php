<?php
require_once __DIR__ . '/functions.php';
ensure_default_settings($pdo);

$ref = strtoupper(trim($_GET['ref'] ?? $_POST['ref'] ?? ''));
$errors = [];
$success = false;
$recruiter = null;

if ($ref !== '') {
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE referral_code = ? AND role = 'recruiter' LIMIT 1");
    $stmt->execute([$ref]);
    $recruiter = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $recruiter) {
    verify_csrf();
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $utmCampaign = trim($_POST['utm_campaign'] ?? '');

    if ($firstName === '') {
        $errors[] = 'Укажите имя.';
    }
    if ($lastName === '') {
        $errors[] = 'Укажите фамилию.';
    }
    if ($city === '') {
        $errors[] = 'Укажите город.';
    }
    if ($phone !== '' && !preg_match('/^[0-9+()\-\s]{6,30}$/u', $phone)) {
        $errors[] = 'Укажите корректный телефон или оставьте поле пустым.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO couriers (recruiter_id, first_name, last_name, city, phone, status, orders_count, utm_campaign) VALUES (?, ?, ?, ?, ?, 'active', 0, ?)");
        $stmt->execute([$recruiter['id'], $firstName, $lastName, $city, $phone ?: null, $utmCampaign ?: null]);
        $success = true;
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Заявка курьера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<main class="auth-shell courier-shell">
    <a class="brand" href="index.php"><span class="brand-mark">Я</span> Рекрутинг</a>
    <section class="auth-card wide-card">
        <?php if (!$recruiter): ?>
            <div class="alert alert-error">Ссылка недействительна или устарела. Попросите рекрутера отправить актуальную ссылку.</div>
            <a class="button button-outline" href="index.php">На главную</a>
        <?php elseif ($success): ?>
            <div class="success-state">
                <div class="success-icon">✓</div>
                <h1>Заявка отправлена</h1>
                <p>Спасибо! Ваши данные переданы рекрутеру <?= e($recruiter['full_name']) ?>.</p>
                <a class="button" href="index.php">Вернуться на сайт</a>
            </div>
        <?php else: ?>
            <h1>Стать курьером</h1>
            <p>Заполните короткую форму. Заявка будет привязана к рекрутеру: <strong><?= e($recruiter['full_name']) ?></strong>.</p>
            <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
            <form method="post" data-validate="true" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="ref" value="<?= e($ref) ?>">
                <input type="hidden" name="utm_campaign" value="<?= e($_GET['utm_campaign'] ?? '') ?>">
                <div class="form-grid two">
                    <label>Имя<input type="text" name="first_name" value="<?= e($_POST['first_name'] ?? '') ?>" required></label>
                    <label>Фамилия<input type="text" name="last_name" value="<?= e($_POST['last_name'] ?? '') ?>" required></label>
                </div>
                <label>Город<input type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" required></label>
                <label>Телефон (необязательно)<input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+7 999 000-00-00"></label>
                <button class="button button-full" type="submit">Отправить заявку</button>
            </form>
        <?php endif; ?>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>
