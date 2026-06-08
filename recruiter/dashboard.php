<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
require_recruiter();
if (isset($_GET['read_notifications'])) { mark_notifications_read($pdo, current_user_id()); redirect('dashboard.php'); }
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute(array(current_user_id()));
$user = $stmt->fetch();
$stats = get_recruiter_stats($pdo, current_user_id());
$referralLink = app_base_url() . '/courier-signup.php?ref=' . urlencode($user['referral_code']);
$pageTitle = 'Кабинет рекрутера'; $bodyClass = 'app-layout'; $activePage = 'dashboard'; $pageHeading = 'Ваши курьеры и баланс';
require __DIR__ . '/../includes/header.php'; require __DIR__ . '/../includes/recruiter-sidebar.php';
?>
<section class="referral-box"><div><h2>Персональная ссылка</h2><p>Кандидаты по этой ссылке автоматически привяжутся к вашему кабинету.</p></div><div class="copy-row"><input id="referralLink" type="text" readonly value="<?= e($referralLink) ?>"><button class="button" type="button" data-copy-target="referralLink"><i class="fa-regular fa-copy"></i> Скопировать</button></div></section>
<div class="dashboard-actions"><a class="button" href="add-courier.php"><i class="fa-solid fa-plus"></i> Добавить курьера</a></div>
<section class="stats-grid"><article class="stat-card"><span>Курьеров</span><strong><?= (int) $stats['total_couriers'] ?></strong></article><article class="stat-card"><span>Активных заказов</span><strong><?= (int) $stats['total_orders'] ?></strong></article><article class="stat-card"><span>Начислено</span><strong><?= format_money($stats['total_earnings']) ?></strong></article><article class="stat-card"><span>Доступно</span><strong><?= format_money($stats['available']) ?></strong></article></section>
<section class="panel"><div class="panel-heading"><h2>Мои курьеры</h2></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Курьер</th><th>Город</th><th>Тип</th><th>Дата</th><th>Статус</th><th>Причина</th><th>Кол-во заказов</th><th>Вознаграждение</th></tr></thead><tbody>
<?php if (!$stats['couriers']): ?><tr><td class="empty-cell" colspan="8">Пока нет курьеров. Добавьте первого вручную или отправьте ссылку.</td></tr><?php endif; ?>
<?php foreach ($stats['couriers'] as $courier): $rate = $courier['status'] === 'active' ? reward_for_courier($pdo, current_user_id(), $courier['city']) : 0; ?>
<tr><td><?= e($courier['first_name'] . ' ' . $courier['last_name']) ?></td><td><?= e($courier['city']) ?></td><td><?= e(delivery_type_label($courier['delivery_type'])) ?></td><td><?= e(date('d.m.Y', strtotime($courier['registered_at']))) ?></td><td><span class="status-pill <?= e(status_class($courier['status'])) ?>"><?= e(status_label($courier['status'])) ?></span></td><td><?= $courier['status'] === 'rejected' ? '<span class="reason" title="' . e($courier['rejection_reason']) . '">' . e($courier['rejection_reason']) . '</span>' : '—' ?></td><td><?= $courier['status'] === 'active' ? (int) $courier['orders_count'] : 0 ?></td><td><?= format_money(((int) $courier['orders_count']) * $rate) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/../includes/recruiter-footer.php'; ?>
