<?php
require_once __DIR__ . '/sidebar.php';
$activePage = isset($activePage) ? $activePage : 'stats';
?>
<aside class="app-sidebar admin-sidebar" id="appSidebar" data-sidebar>
    <div class="sidebar-brand-row">
        <a class="brand" href="<?= e(admin_url('index.php')) ?>"><span class="brand-mark">Я</span><span>Админ</span></a>
        <button class="sidebar-close" type="button" data-sidebar-close aria-label="Закрыть меню"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <nav class="sidebar-nav" aria-label="Навигация администратора">
        <a class="<?= $activePage === 'verification' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=verification')) ?>"><?= menu_icon('admin-verification-icon.webp','fa-solid fa-shield-halved') ?><span>Проверка</span></a>
        <a class="<?= $activePage === 'stats' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=stats')) ?>"><?= menu_icon('admin-stats-icon.webp','fa-solid fa-chart-simple') ?><span>Статистика</span></a>
        <a class="<?= $activePage === 'recruiters' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=recruiters')) ?>"><?= menu_icon('admin-recruiters-icon.webp','fa-solid fa-users') ?><span>Рекрутеры</span></a>
        <a class="<?= $activePage === 'couriers' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=couriers')) ?>"><?= menu_icon('couriers-icon.webp','fa-solid fa-motorcycle') ?><span>Курьеры</span></a>
        <a class="<?= $activePage === 'city-rates' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=city-rates')) ?>"><?= menu_icon('city-rates-icon.webp','fa-solid fa-location-dot') ?><span>Ставки городов</span></a>
        <a class="<?= $activePage === 'news' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=news')) ?>"><?= menu_icon('admin-news-icon.webp','fa-solid fa-newspaper') ?><span>Новости</span></a>
        <a class="<?= $activePage === 'settings' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=settings')) ?>"><?= menu_icon('settings-icon.webp','fa-solid fa-gear') ?><span>Настройки</span></a>
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
