<?php $activePage = isset($activePage) ? $activePage : 'stats'; ?>
<aside class="app-sidebar admin-sidebar">
    <a class="brand" href="<?= e(admin_url('index.php')) ?>"><span class="brand-mark">Я</span><span>Админ</span></a>
    <nav class="sidebar-nav">
        <a class="<?= $activePage === 'verification' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=verification')) ?>"><i class="fa-solid fa-shield-check"></i> Проверка</a>
        <a class="<?= $activePage === 'stats' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=stats')) ?>"><i class="fa-solid fa-chart-simple"></i> Статистика</a>
        <a class="<?= $activePage === 'recruiters' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=recruiters')) ?>"><i class="fa-solid fa-users"></i> Рекрутеры</a>
        <a class="<?= $activePage === 'couriers' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=couriers')) ?>"><i class="fa-solid fa-motorcycle"></i> Курьеры</a>
        <a class="<?= $activePage === 'city-rates' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=city-rates')) ?>"><i class="fa-solid fa-location-dot"></i> Ставки городов</a>
        <a class="<?= $activePage === 'news' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=news')) ?>"><i class="fa-solid fa-newspaper"></i> Новости</a>
        <a class="<?= $activePage === 'settings' ? 'active' : '' ?>" href="<?= e(admin_url('index.php?tab=settings')) ?>"><i class="fa-solid fa-gear"></i> Настройки</a>
    </nav>
    <div class="sidebar-footer"><a class="button button-outline button-full" href="<?= e(url_for('logout.php')) ?>">Выйти</a></div>
</aside>
<header class="mobile-topbar"><a class="brand" href="<?= e(admin_url('index.php')) ?>"><span class="brand-mark">Я</span>Админ</a><a class="button small" href="<?= e(url_for('logout.php')) ?>">Выйти</a></header>
<div class="app-content">
    <div class="content-topline"><div><p class="eyebrow">Администрирование</p><h1><?= e($pageHeading) ?></h1></div><span class="user-chip"><?= e($_SESSION['email']) ?></span></div>
