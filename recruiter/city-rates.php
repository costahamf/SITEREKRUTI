<?php
require_once __DIR__ . '/../includes/functions.php'; ensure_default_settings($pdo); require_recruiter();
$active = city_rates_active($pdo);
$rates = $active ? $pdo->query('SELECT * FROM city_rates ORDER BY city ASC')->fetchAll() : array();
$pageTitle='Ставки по городам'; $bodyClass='app-layout'; $activePage='city-rates'; $pageHeading='Ставки по городам'; require __DIR__ . '/../includes/header.php'; require __DIR__ . '/../includes/recruiter-sidebar.php';
?>
<?php if (!$active): ?><div class="alert alert-warning">Ставки временно не обновлены, используйте глобальную ставку.</div><?php endif; ?>
<section class="panel"><div class="panel-heading"><h2>Городские ставки</h2></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Город</th><th>Вознаграждение за заказ</th><th>Максимум на курьера</th></tr></thead><tbody><?php if (!$rates): ?><tr><td colspan="3" class="empty-cell">Используется глобальная ставка: <?= format_money(get_setting($pdo,'reward_per_order','30')) ?>.</td></tr><?php endif; ?><?php foreach ($rates as $rate): ?><tr><td><?= e($rate['city']) ?></td><td><?= format_money($rate['reward_per_order']) ?></td><td><?= $rate['max_earnings_per_courier'] === null ? 'Без лимита' : format_money($rate['max_earnings_per_courier']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require __DIR__ . '/../includes/recruiter-footer.php'; ?>
