<?php
require_once __DIR__ . '/functions.php';
ensure_default_settings($pdo);
require_recruiter();

$reward = (float) get_setting($pdo, 'reward_per_order', '30');
$stmt = $pdo->prepare('SELECT id, full_name, email, referral_code FROM users WHERE id = ? AND role = \'recruiter\' LIMIT 1');
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();
if (!$user) {
    redirect('logout.php');
}

$stmt = $pdo->prepare('SELECT * FROM couriers WHERE recruiter_id = ? ORDER BY registered_at DESC');
$stmt->execute([$user['id']]);
$couriers = $stmt->fetchAll();
$totalCouriers = count($couriers);
$totalOrders = array_sum(array_map(function($courier) {
    return (int) $courier['orders_count'];
}, $couriers));
$totalEarnings = $totalOrders * $reward;
$referralLink = app_base_url() . '/courier-signup.php?ref=' . urlencode($user['referral_code']);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Кабинет рекрутера</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<nav class="topbar dashboard-topbar">
    <a class="brand" href="dashboard.php"><span class="brand-mark">Я</span> Рекрутинг</a>
    <div class="nav-actions"><span class="user-chip"><?= e($user['full_name']) ?></span><a class="button button-outline" href="logout.php">Выйти</a></div>
</nav>
<main class="page-container">
    <section class="page-header">
        <div>
            <p class="eyebrow">Кабинет рекрутера</p>
            <h1>Ваши курьеры и вознаграждение</h1>
        </div>
    </section>

    <section class="referral-box">
        <div>
            <h2>Персональная ссылка</h2>
            <p>Отправьте её кандидату — заявка автоматически привяжется к вашему кабинету.</p>
        </div>
        <div class="copy-row">
            <input id="referralLink" type="text" readonly value="<?= e($referralLink) ?>">
            <button class="button" type="button" data-copy-target="referralLink"><i class="fa-regular fa-copy"></i> Скопировать</button>
        </div>
    </section>

    <section class="stats-grid">
        <article class="stat-card"><span>Курьеров</span><strong><?= $totalCouriers ?></strong></article>
        <article class="stat-card"><span>Кол-во заказов</span><strong><?= $totalOrders ?></strong></article>
        <article class="stat-card"><span>Ставка за заказ</span><strong><?= format_money($reward) ?></strong></article>
        <article class="stat-card"><span>Вознаграждение</span><strong><?= format_money($totalEarnings) ?></strong></article>
    </section>

    <section class="panel">
        <div class="panel-heading"><h2>Приглашённые курьеры</h2></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Город</th>
                        <th>Дата регистрации</th>
                        <th>Статус</th>
                        <th>Кол-во заказов</th>
                        <th>Вознаграждение</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$couriers): ?>
                    <tr><td colspan="6" class="empty-cell">Пока нет зарегистрированных курьеров.</td></tr>
                <?php endif; ?>
                <?php foreach ($couriers as $courier): ?>
                    <tr>
                        <td><?= e($courier['first_name'] . ' ' . $courier['last_name']) ?></td>
                        <td><?= e($courier['city']) ?></td>
                        <td><?= e(date('d.m.Y', strtotime($courier['registered_at']))) ?></td>
                        <td><span class="status-pill status-<?= e($courier['status']) ?>"><?= e(status_label($courier['status'])) ?></span></td>
                        <td><?= (int) $courier['orders_count'] ?></td>
                        <td><?= format_money((int) $courier['orders_count'] * $reward) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>