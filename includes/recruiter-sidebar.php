<?php
$activePage = isset($activePage) ? $activePage : '';
$unreadCount = is_logged_in() ? unread_notifications_count($pdo, current_user_id()) : 0;
$notifications = is_logged_in() ? latest_notifications($pdo, current_user_id()) : array();
?>
<aside class="app-sidebar" id="appSidebar" data-sidebar>
    <div class="sidebar-brand-row">
        <a class="brand" href="<?= e(recruiter_url('dashboard.php')) ?>"><span class="brand-mark">Я</span><span>Рекрутинг</span></a>
        <button class="sidebar-close" type="button" data-sidebar-close aria-label="Закрыть меню"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <nav class="sidebar-nav" aria-label="Навигация рекрутера">
        <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= e(recruiter_url('dashboard.php')) ?>"><i class="fa-solid fa-chart-line"></i><span>Дашборд</span></a>
        <a class="<?= $activePage === 'add-courier' ? 'active' : '' ?>" href="<?= e(recruiter_url('add-courier.php')) ?>"><i class="fa-solid fa-user-plus"></i><span>Добавить курьера</span></a>
        <a class="<?= $activePage === 'withdraw' ? 'active' : '' ?>" href="<?= e(recruiter_url('withdraw.php')) ?>"><i class="fa-solid fa-wallet"></i><span>Выплаты</span></a>
        <a class="<?= $activePage === 'city-rates' ? 'active' : '' ?>" href="<?= e(recruiter_url('city-rates.php')) ?>"><i class="fa-solid fa-location-dot"></i><span>Ставки по городам</span></a>
        <a class="<?= $activePage === 'faq' ? 'active' : '' ?>" href="<?= e(recruiter_url('faq.php')) ?>"><i class="fa-solid fa-circle-question"></i><span>FAQ</span></a>
    </nav>
    <div class="sidebar-footer"><a class="button button-outline button-full" href="<?= e(url_for('logout.php')) ?>">Выйти</a></div>
</aside>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>
<header class="mobile-topbar">
    <button class="icon-button" type="button" data-sidebar-toggle aria-label="Открыть меню"><i class="fa-solid fa-bars"></i></button>
    <a class="brand" href="<?= e(recruiter_url('dashboard.php')) ?>"><span class="brand-mark">Я</span><span>Рекрутинг</span></a>
    <a class="button small" href="<?= e(url_for('logout.php')) ?>">Выйти</a>
</header>
<div class="app-content">
    <div class="content-topline">
        <div><p class="eyebrow">Кабинет рекрутера</p><h1><?= e($pageHeading) ?></h1></div>
        <div class="topline-actions">
            <div class="notification-wrap">
                <button class="bell-button" type="button" data-notifications-toggle data-read-url="<?= e(url_for('notifications-read.php')) ?>" aria-label="Уведомления">
                    <i class="fa-regular fa-bell"></i>
                    <?php if ($unreadCount > 0): ?><span class="badge"><?= (int) $unreadCount ?></span><?php endif; ?>
                </button>
                <div class="notification-dropdown" hidden>
                    <div class="notification-head">Уведомления</div>
                    <?php if (!$notifications): ?><p class="empty-note">Пока нет уведомлений.</p><?php endif; ?>
                    <?php foreach ($notifications as $note): ?>
                        <article class="notification-item <?= (int) $note['is_read'] === 0 ? 'unread' : '' ?>">
                            <strong><?= e($note['title']) ?></strong>
                            <p><?= e($note['message']) ?></p>
                            <time><?= e(date('d.m.Y H:i', strtotime($note['created_at']))) ?></time>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <span class="user-chip"><?= e($_SESSION['full_name']) ?></span>
        </div>
    </div>
