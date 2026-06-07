<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
require_recruiter();
$errors = array(); $success = false; $cities = get_cities($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $firstName = trim(isset($_POST['first_name']) ? $_POST['first_name'] : '');
    $lastName = trim(isset($_POST['last_name']) ? $_POST['last_name'] : '');
    $city = trim(isset($_POST['city']) ? $_POST['city'] : '');
    $deliveryType = isset($_POST['delivery_type']) ? $_POST['delivery_type'] : '';
    if ($firstName === '') { $errors[] = 'Укажите имя.'; }
    if ($lastName === '') { $errors[] = 'Укажите фамилию.'; }
    if ($city === '') { $errors[] = 'Укажите город.'; }
    if (!in_array($deliveryType, array('auto', 'foot', 'bike'), true)) { $errors[] = 'Выберите тип доставки.'; }
    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO couriers (recruiter_id, first_name, last_name, city, delivery_type, status, orders_count) VALUES (?, ?, ?, ?, ?, 'pending', 0)");
        $stmt->execute(array(current_user_id(), $firstName, $lastName, $city, $deliveryType));
        $success = true;
    }
}
$pageTitle = 'Добавить курьера'; $bodyClass = 'app-layout'; $activePage = 'add-courier'; $pageHeading = 'Добавить курьера';
require __DIR__ . '/../includes/header.php'; require __DIR__ . '/../includes/recruiter-sidebar.php';
?>
<section class="panel narrow-panel"><div class="panel-heading"><h2>Новая заявка</h2></div><div class="panel-body">
<?php if ($success): ?><div class="alert alert-success">Отлично! Курьер на проверке. Администратор рассмотрит заявку в ближайшее время.</div><a class="button" href="dashboard.php">Вернуться к курьерам</a><?php else: ?>
<?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
<form method="post" data-validate="true" class="form-card plain-form"><?= csrf_field() ?><div class="form-grid two"><label>Имя<input type="text" name="first_name" required></label><label>Фамилия<input type="text" name="last_name" required></label></div><label>Город<input list="cities" type="text" name="city" required><datalist id="cities"><?php foreach ($cities as $city): ?><option value="<?= e($city) ?>"><?php endforeach; ?></datalist></label><label>Тип доставки<select name="delivery_type" required><option value="">Выберите</option><option value="auto">Авто</option><option value="foot">Пешим</option><option value="bike">Вело</option></select></label><button class="button" type="submit">Отправить на проверку</button></form>
<?php endif; ?></div></section>
<?php require __DIR__ . '/../includes/recruiter-footer.php'; ?>
