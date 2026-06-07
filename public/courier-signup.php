<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
$ref = strtoupper(trim(isset($_GET['ref']) ? $_GET['ref'] : (isset($_POST['ref']) ? $_POST['ref'] : '')));
$errors = array(); $success = false; $recruiter = null;
if ($ref !== '') {
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE referral_code = ? AND role = 'recruiter' LIMIT 1");
    $stmt->execute(array($ref));
    $recruiter = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $recruiter) {
    verify_csrf();
    $firstName = trim(isset($_POST['first_name']) ? $_POST['first_name'] : '');
    $lastName = trim(isset($_POST['last_name']) ? $_POST['last_name'] : '');
    $city = trim(isset($_POST['city']) ? $_POST['city'] : '');
    $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $deliveryType = isset($_POST['delivery_type']) ? $_POST['delivery_type'] : '';
    if ($firstName === '') { $errors[] = 'Укажите имя.'; }
    if ($lastName === '') { $errors[] = 'Укажите фамилию.'; }
    if ($city === '') { $errors[] = 'Укажите город.'; }
    if (!in_array($deliveryType, array('auto', 'foot', 'bike'), true)) { $errors[] = 'Выберите тип доставки.'; }
    if ($phone !== '' && !preg_match('/^[0-9+()\-\s]{6,30}$/u', $phone)) { $errors[] = 'Укажите корректный телефон или оставьте поле пустым.'; }
    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO couriers (recruiter_id, first_name, last_name, city, delivery_type, phone, status, orders_count, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, 'pending', 0, ?)");
        $stmt->execute(array($recruiter['id'], $firstName, $lastName, $city, $deliveryType, $phone ? $phone : null, isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : null));
        $success = true;
    }
}
$pageTitle = 'Заявка курьера'; $bodyClass = 'auth-page'; require __DIR__ . '/../includes/header.php';
?>
<main class="auth-shell wide-auth"><a class="brand" href="index.php"><span class="brand-mark">Я</span>Рекрутинг</a><section class="auth-card">
<?php if (!$recruiter): ?>
    <div class="alert alert-error">Ссылка недействительна. Попросите рекрутера прислать актуальную ссылку.</div><a class="button button-outline" href="index.php">На главную</a>
<?php elseif ($success): ?>
    <div class="success-state"><div class="success-icon">✓</div><h1>Заявка отправлена</h1><p>Отлично! Курьер на проверке. Администратор рассмотрит заявку в ближайшее время.</p><a class="button" href="index.php">Вернуться на сайт</a></div>
<?php else: ?>
    <h1>Стать курьером</h1><p>Заявка будет привязана к рекрутеру: <strong><?= e($recruiter['full_name']) ?></strong>.</p><?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
    <form method="post" data-validate="true"><?= csrf_field() ?><input type="hidden" name="ref" value="<?= e($ref) ?>"><div class="form-grid two"><label>Имя<input type="text" name="first_name" required></label><label>Фамилия<input type="text" name="last_name" required></label></div><label>Город<input type="text" name="city" required></label><label>Телефон (необязательно)<input type="tel" name="phone" placeholder="+7 999 000-00-00"></label><label>Тип доставки<select name="delivery_type" required><option value="">Выберите</option><option value="auto">Авто</option><option value="foot">Пешим</option><option value="bike">Вело</option></select></label><button class="button button-full" type="submit">Отправить заявку</button></form>
<?php endif; ?>
</section></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
