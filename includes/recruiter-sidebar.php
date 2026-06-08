<?php
require_once __DIR__ . '/sidebar.php';
$activePage = isset($activePage) ? $activePage : '';
$unreadCount = is_logged_in() ? unread_notifications_count($pdo, current_user_id()) : 0;
$notifications = is_logged_in() ? latest_notifications($pdo, current_user_id()) : array();
$supportUrl = get_setting($pdo, 'support_bot_url', 'https://t.me/ваш_бот');
?>
<aside class="app-sidebar" id="appSidebar" data-sidebar>
    <div class="sidebar-brand-row">
        <a class="brand" href="<?= e(recruiter_url('dashboard.php')) ?>"><span class="brand-mark">Я</span><span>Рекрутинг</span></a>
        <button class="sidebar-close" type="button" data-sidebar-close aria-label="Закрыть меню"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <nav class="sidebar-nav" aria-label="Навигация рекрутера">
        <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= e(recruiter_url('dashboard.php')) ?>"><?= menu_icon('dashboard-icon.webp','fa-solid fa-chart-line') ?><span>Дашборд</span></a>
        <a class="<?= $activePage === 'add-courier' ? 'active' : '' ?>" href="<?= e(recruiter_url('add-courier.php')) ?>"><?= menu_icon('couriers-icon.webp','fa-solid fa-user-plus') ?><span>Добавить курьера</span></a>
        <a class="<?= $activePage === 'withdraw' ? 'active' : '' ?>" href="<?= e(recruiter_url('withdraw.php')) ?>"><?= menu_icon('withdraw-icon.webp','fa-solid fa-wallet') ?><span>Выплаты</span></a>
        <a class="<?= $activePage === 'city-rates' ? 'active' : '' ?>" href="<?= e(recruiter_url('city-rates.php')) ?>"><?= menu_icon('city-rates-icon.webp','fa-solid fa-location-dot') ?><span>Ставки</span></a>
        <a class="<?= $activePage === 'faq' ? 'active' : '' ?>" href="<?= e(recruiter_url('faq.php')) ?>"><?= menu_icon('faq-icon.webp','fa-solid fa-circle-question') ?><span>FAQ</span></a>
        <a href="<?= e($supportUrl) ?>" target="_blank" rel="noopener"><?= menu_icon('support-icon.webp','fa-brands fa-telegram') ?><span>Тех.поддержка</span></a>
    </nav>
    <div class="notifications-widget">
        <button class="notification-toggle" type="button" data-notifications-toggle><i class="fa-solid fa-bell"></i> Уведомления <?php if ($unreadCount): ?><b><?= (int)$unreadCount ?></b><?php endif; ?></button>
        <div class="notifications-list" data-notifications-list>
            <?php if (!$notifications): ?><p>Новых уведомлений нет.</p><?php endif; ?>
            <?php foreach ($notifications as $note): ?><article class="<?= $note['is_read'] ? '' : 'unread' ?>"><strong><?= e($note['title']) ?></strong><p><?= e($note['message']) ?></p><time><?= e(date('d.m.Y H:i', strtotime($note['created_at']))) ?></time></article><?php endforeach; ?>
        </div>
    </div>
    <div class="sidebar-footer"><a class="button button-outline button-full" href="<?= e(url_for('logout.php')) ?>">Выйти</a></div>
</aside>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>
<header class="mobile-topbar">
    <button class="icon-button" type="button" data-sidebar-toggle aria-label="Открыть меню"><i class="fa-solid fa-bars"></i></button>
    <a class="brand" href="<?= e(recruiter_url('dashboard.php')) ?>"><span class="brand-mark">Я</span><span>Рекрутинг</span></a>
    <a class="button small" href="<?= e(url_for('logout.php')) ?>">Выйти</a>
</header>
<div class="app-content">
    <div class="content-topline"><div><p class="eyebrow">Личный кабинет</p><h1><?= e($pageHeading) ?></h1></div><span class="user-chip"><?= e($_SESSION['email']) ?></span></div>
