<?php
$activePage = isset($activePage) ? $activePage : '';
$unreadCount = is_logged_in() ? unread_notifications_count($pdo, current_user_id()) : 0;
$notifications = is_logged_in() ? latest_notifications($pdo, current_user_id()) : array();
?>
<aside class="app-sidebar">
    <a class="brand" href="<?= e(recruiter_url('dashboard.php')) ?>"><span class="brand-mark">Я</span><span>Рекрутинг</span></a>
    <nav class="sidebar-nav">
        <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= e(recruiter_url('dashboard.php')) ?>"><i class="fa-solid fa-chart-line"></i> Дашборд</a>
        <a class="<?= $activePage === 'add-courier' ? 'active' : '' ?>" href="<?= e(recruiter_url('add-courier.php')) ?>"><i class="fa-solid fa-user-plus"></i> Добавить курьера</a>
        <a class="<?= $activePage === 'withdraw' ? 'active' : '' ?>" href="<?= e(recruiter_url('withdraw.php')) ?>"><i class="fa-solid fa-wallet"></i> Выплаты</a>
        <a class="<?= $activePage === 'city-rates' ? 'active' : '' ?>" href="<?= e(recruiter_url('city-rates.php')) ?>"><i class="fa-solid fa-location-dot"></i> Ставки по городам</a>
        <a class="<?= $activePage === 'faq' ? 'active' : '' ?>" href="<?= e(recruiter_url('faq.php')) ?>"><i class="fa-solid fa-circle-question"></i> FAQ</a>
    </nav>
    <div class="sidebar-footer"><a class="button button-outline button-full" href="<?= e(url_for('logout.php')) ?>">Выйти</a></div>
</aside>
<header class="mobile-topbar">
    <a class="brand" href="<?= e(recruiter_url('dashboard.php')) ?>"><span class="brand-mark">Я</span>Рекрутинг</a>
    <a class="button small" href="<?= e(url_for('logout.php')) ?>">Выйти</a>
</header>
<div class="app-content">
    <div class="content-topline">
        <div><p class="eyebrow">Кабинет рекрутера</p><h1><?= e($pageHeading) ?></h1></div>
        <div class="topline-actions">
            <div class="notification-wrap">
                <button class="bell-button" type="button" data-notifications-toggle data-read-url="<?= e(url_for('notifications-read.php')) ?>">
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
