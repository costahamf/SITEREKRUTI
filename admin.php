<?php
require_once __DIR__ . '/functions.php';
ensure_default_settings($pdo);
require_admin();

$tab = $_GET['tab'] ?? 'stats';
$allowedTabs = ['stats', 'recruiters', 'couriers', 'settings'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'stats';
}

$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_setting') {
            $reward = (float) str_replace(',', '.', $_POST['reward_per_order'] ?? '0');
            if ($reward < 0) {
                throw new RuntimeException('Ставка не может быть отрицательной.');
            }
            set_setting($pdo, 'reward_per_order', (string) $reward);
            $message = 'Настройки сохранены.';
            $tab = 'settings';
        }

        if ($action === 'update_recruiter') {
            $id = (int) ($_POST['id'] ?? 0);
            $fullName = trim($_POST['full_name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Проверьте имя и email рекрутера.');
            }
            if ($password !== '') {
                if (strlen($password) < 8) {
                    throw new RuntimeException('Новый пароль должен содержать минимум 8 символов.');
                }
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password_hash = ? WHERE id = ? AND role = 'recruiter'");
                $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ? AND role = 'recruiter'");
                $stmt->execute([$fullName, $email, $id]);
            }
            $message = 'Рекрутер обновлён.';
            $tab = 'recruiters';
        }

        if ($action === 'delete_recruiter') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'recruiter'");
            $stmt->execute([$id]);
            $message = 'Рекрутер удалён.';
            $tab = 'recruiters';
        }

        if ($action === 'add_courier' || $action === 'update_courier') {
            $courierId = (int) ($_POST['id'] ?? 0);
            $recruiterId = (int) ($_POST['recruiter_id'] ?? 0);
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $orders = max(0, (int) ($_POST['orders_count'] ?? 0));
            $utmCampaign = trim($_POST['utm_campaign'] ?? '');

            if ($recruiterId <= 0 || $firstName === '' || $lastName === '' || $city === '') {
                throw new RuntimeException('Заполните рекрутера, имя, фамилию и город.');
            }
            if (!in_array($status, ['active', 'paused', 'blocked'], true)) {
                $status = 'active';
            }

            if ($action === 'add_courier') {
                $stmt = $pdo->prepare('INSERT INTO couriers (recruiter_id, first_name, last_name, city, phone, status, orders_count, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$recruiterId, $firstName, $lastName, $city, $phone ?: null, $status, $orders, $utmCampaign ?: null]);
                $message = 'Курьер добавлен.';
            } else {
                $stmt = $pdo->prepare('UPDATE couriers SET recruiter_id = ?, first_name = ?, last_name = ?, city = ?, phone = ?, status = ?, orders_count = ?, utm_campaign = ? WHERE id = ?');
                $stmt->execute([$recruiterId, $firstName, $lastName, $city, $phone ?: null, $status, $orders, $utmCampaign ?: null, $courierId]);
                $message = 'Курьер обновлён.';
            }
            $tab = 'couriers';
        }

        if ($action === 'delete_courier') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM couriers WHERE id = ?');
            $stmt->execute([$id]);
            $message = 'Курьер удалён.';
            $tab = 'couriers';
        }
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

$reward = (float) get_setting($pdo, 'reward_per_order', '30');
$recruiters = get_recruiters($pdo);
$courierStmt = $pdo->query("SELECT c.*, u.full_name AS recruiter_name, u.email AS recruiter_email FROM couriers c JOIN users u ON u.id = c.recruiter_id ORDER BY c.registered_at DESC");
$couriers = $courierStmt->fetchAll();
$stats = [
    'recruiters' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'recruiter'")->fetchColumn(),
    'couriers' => (int) $pdo->query('SELECT COUNT(*) FROM couriers')->fetchColumn(),
    'orders' => (int) $pdo->query('SELECT COALESCE(SUM(orders_count), 0) FROM couriers')->fetchColumn(),
];
$stats['paid'] = $stats['orders'] * $reward;
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="admin-body">
<aside class="sidebar">
    <a class="brand" href="admin.php"><span class="brand-mark">Я</span> Админ</a>
    <nav class="side-nav">
        <a class="<?= $tab === 'stats' ? 'active' : '' ?>" href="admin.php?tab=stats"><i class="fa-solid fa-chart-simple"></i> Статистика</a>
        <a class="<?= $tab === 'recruiters' ? 'active' : '' ?>" href="admin.php?tab=recruiters"><i class="fa-solid fa-users"></i> Рекрутеры</a>
        <a class="<?= $tab === 'couriers' ? 'active' : '' ?>" href="admin.php?tab=couriers"><i class="fa-solid fa-motorcycle"></i> Курьеры</a>
        <a class="<?= $tab === 'settings' ? 'active' : '' ?>" href="admin.php?tab=settings"><i class="fa-solid fa-gear"></i> Настройки</a>
    </nav>
    <a class="button button-outline button-full" href="logout.php">Выйти</a>
</aside>

<main class="admin-main">
    <header class="page-header compact">
        <div><p class="eyebrow">Администрирование</p><h1>Панель управления</h1></div>
        <span class="user-chip"><?= e($_SESSION['email'] ?? '') ?></span>
    </header>

    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

    <?php if ($tab === 'stats'): ?>
        <section class="stats-grid">
            <article class="stat-card"><span>Рекрутеров</span><strong><?= $stats['recruiters'] ?></strong></article>
            <article class="stat-card"><span>Курьеров</span><strong><?= $stats['couriers'] ?></strong></article>
            <article class="stat-card"><span>Всего заказов</span><strong><?= $stats['orders'] ?></strong></article>
            <article class="stat-card"><span>Начислено</span><strong><?= format_money($stats['paid']) ?></strong></article>
        </section>
    <?php endif; ?>

    <?php if ($tab === 'settings'): ?>
        <section class="panel narrow-panel">
            <div class="panel-heading"><h2>Глобальная ставка</h2></div>
            <form method="post" class="form-card inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_setting">
                <label>Вознаграждение за заказ, ₽<input type="number" name="reward_per_order" min="0" step="0.01" value="<?= e((string) $reward) ?>" required></label>
                <button class="button" type="submit">Сохранить</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($tab === 'recruiters'): ?>
        <section class="panel">
            <div class="panel-heading"><h2>Рекрутеры</h2></div>
            <div class="table-wrap">
                <table class="data-table editable-table">
                    <thead><tr><th>Имя</th><th>Email</th><th>Код</th><th>Создан</th><th>Новый пароль</th><th>Действия</th></tr></thead>
                    <tbody>
                    <?php foreach ($recruiters as $recruiter): ?>
                        <tr>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="update_recruiter">
                                <input type="hidden" name="id" value="<?= (int) $recruiter['id'] ?>">
                                <td><input type="text" name="full_name" value="<?= e($recruiter['full_name']) ?>" required></td>
                                <td><input type="email" name="email" value="<?= e($recruiter['email']) ?>" required></td>
                                <td><code><?= e($recruiter['referral_code']) ?></code></td>
                                <td><?= e(date('d.m.Y', strtotime($recruiter['created_at']))) ?></td>
                                <td><input type="password" name="password" placeholder="Оставить без изменений"></td>
                                <td class="actions-cell"><button class="button small" type="submit">Сохранить</button>
                            </form>
                            <form method="post" data-confirm="Удалить рекрутера и всех его курьеров?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_recruiter">
                                <input type="hidden" name="id" value="<?= (int) $recruiter['id'] ?>">
                                <button class="button small danger" type="submit">Удалить</button>
                            </form></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($tab === 'couriers'): ?>
        <section class="panel">
            <div class="panel-heading"><h2>Добавить курьера вручную</h2></div>
            <form method="post" class="form-card admin-add-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_courier">
                <div class="form-grid four">
                    <label>Рекрутер<select name="recruiter_id" required><option value="">Выберите</option><?php foreach ($recruiters as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e($r['full_name']) ?></option><?php endforeach; ?></select></label>
                    <label>Имя<input type="text" name="first_name" required></label>
                    <label>Фамилия<input type="text" name="last_name" required></label>
                    <label>Город<input type="text" name="city" required></label>
                    <label>Телефон<input type="tel" name="phone"></label>
                    <label>Статус<select name="status"><option value="active">Активен</option><option value="paused">На паузе</option><option value="blocked">Заблокирован</option></select></label>
                    <label>Заказы<input type="number" name="orders_count" min="0" value="0"></label>
                    <label>UTM<input type="text" name="utm_campaign"></label>
                </div>
                <button class="button" type="submit">Добавить</button>
            </form>
        </section>

        <section class="panel">
            <div class="panel-heading"><h2>Все курьеры</h2></div>
            <div class="table-wrap">
                <table class="data-table editable-table wide-table">
                    <thead><tr><th>Рекрутер</th><th>Имя</th><th>Фамилия</th><th>Город</th><th>Телефон</th><th>Статус</th><th>Заказы</th><th>Вознаграждение</th><th>Действия</th></tr></thead>
                    <tbody>
                    <?php foreach ($couriers as $courier): ?>
                        <tr>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="update_courier">
                                <input type="hidden" name="id" value="<?= (int) $courier['id'] ?>">
                                <td><select name="recruiter_id"><?php foreach ($recruiters as $r): ?><option value="<?= (int) $r['id'] ?>" <?= (int) $r['id'] === (int) $courier['recruiter_id'] ? 'selected' : '' ?>><?= e($r['full_name']) ?></option><?php endforeach; ?></select></td>
                                <td><input type="text" name="first_name" value="<?= e($courier['first_name']) ?>" required></td>
                                <td><input type="text" name="last_name" value="<?= e($courier['last_name']) ?>" required></td>
                                <td><input type="text" name="city" value="<?= e($courier['city']) ?>" required></td>
                                <td><input type="tel" name="phone" value="<?= e($courier['phone']) ?>"></td>
                                <td><select name="status"><option value="active" <?= $courier['status'] === 'active' ? 'selected' : '' ?>>Активен</option><option value="paused" <?= $courier['status'] === 'paused' ? 'selected' : '' ?>>На паузе</option><option value="blocked" <?= $courier['status'] === 'blocked' ? 'selected' : '' ?>>Заблокирован</option></select></td>
                                <td><input type="number" name="orders_count" min="0" value="<?= (int) $courier['orders_count'] ?>"></td>
                                <td><?= format_money((int) $courier['orders_count'] * $reward) ?></td>
                                <td class="actions-cell"><button class="button small" type="submit">Сохранить</button>
                            </form>
                            <form method="post" data-confirm="Удалить курьера?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_courier">
                                <input type="hidden" name="id" value="<?= (int) $courier['id'] ?>">
                                <button class="button small danger" type="submit">Удалить</button>
                            </form></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</main>
<script src="script.js"></script>
</body>
</html>
