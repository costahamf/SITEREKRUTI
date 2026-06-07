<?php
require_once __DIR__ . '/../includes/functions.php'; ensure_default_settings($pdo); require_recruiter();
$stmt = $pdo->query('SELECT * FROM city_rates ORDER BY city ASC'); $rates = $stmt->fetchAll();
$pageTitle='Ставки по городам'; $bodyClass='app-layout'; $activePage='city-rates'; $pageHeading='Ставки по городам'; require __DIR__ . '/../includes/header.php'; require __DIR__ . '/../includes/recruiter-sidebar.php';
?>
<section class="panel"><div class="panel-heading"><h2>Городские ставки</h2></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Город</th><th>Вознаграждение за заказ</th></tr></thead><tbody><?php if (!$rates): ?><tr><td colspan="2" class="empty-cell">Пока городские ставки не заданы. Используется глобальная ставка.</td></tr><?php endif; ?><?php foreach ($rates as $rate): ?><tr><td><?= e($rate['city']) ?></td><td><?= format_money($rate['reward_per_order']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require __DIR__ . '/../includes/recruiter-footer.php'; ?>
