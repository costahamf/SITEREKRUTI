<?php $activePage = isset($activePage) ? $activePage : 'stats'; ?>
<aside class="app-sidebar admin-sidebar" id="appSidebar" data-sidebar>
    <div class="sidebar-brand-row">
        <a class="brand" href="<?= e(admin_url('index.php')) ?>"><span class="brand-mark">Я</span><span>Админ</span></a>
        <button class="sidebar-close" type="button" data-sidebar-close aria-label="Закрыть меню"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <nav class="sidebar-nav" aria-label="Навигация администратора">
        <a class="<?= $activePage === 'verification' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=verification')) ?>"><i class="fa-solid fa-shield-check"></i><span>Проверка</span></a>
        <a class="<?= $activePage === 'stats' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=stats')) ?>"><i class="fa-solid fa-chart-simple"></i><span>Статистика</span></a>
        <a class="<?= $activePage === 'recruiters' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=recruiters')) ?>"><i class="fa-solid fa-users"></i><span>Рекрутеры</span></a>
        <a class="<?= $activePage === 'couriers' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=couriers')) ?>"><i class="fa-solid fa-motorcycle"></i><span>Курьеры</span></a>
        <a class="<?= $activePage === 'city-rates' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=city-rates')) ?>"><i class="fa-solid fa-location-dot"></i><span>Ставки городов</span></a>
        <a class="<?= $activePage === 'news' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=news')) ?>"><i class="fa-solid fa-newspaper"></i><span>Новости</span></a>
        <a class="<?= $activePage === 'settings' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=settings')) ?>"><i class="fa-solid fa-gear"></i><span>Настройки</span></a>
    </nav>
    <div class="sidebar-footer"><a class="button button-outline button-full" href="<?= e(url_for('logout.php')) ?>">Выйти</a></div>
</aside>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>
<header class="mobile-topbar">
    <button class="icon-button" type="button" data-sidebar-toggle aria-label="Открыть меню"><i class="fa-solid fa-bars"></i></button>
    <a class="brand" href="<?= e(admin_url('index.php')) ?>"><span class="brand-mark">Я</span><span>Админ</span></a>
    <a class="button small" href="<?= e(url_for('logout.php')) ?>">Выйти</a>
</header>
<div class="app-content">
    <div class="content-topline"><div><p class="eyebrow">Администрирование</p><h1><?= e($pageHeading) ?></h1></div><span class="user-chip"><?= e($_SESSION['email']) ?></span></div>
