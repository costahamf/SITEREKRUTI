<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
$pageTitle = 'Партнёрская платформа для рекрутеров';
$bodyClass = 'landing-page';
require __DIR__ . '/../includes/header.php';
?>
<header class="landing-header">
    <a class="brand" href="index.php"><span class="brand-mark">Я</span>Рекрутинг</a>
    <nav><a href="#benefits">Преимущества</a><a href="#process">Процесс</a><a href="login.php">Войти</a><a class="button small" href="register.php">Стать партнёром</a></nav>
</header>
<main class="landing-main">
    <section class="hero-section reveal">
        <div class="hero-copy"><p class="eyebrow">Партнёрская сеть Яндекс Еды</p><h1>Привлекайте курьеров и управляйте доходом в удобном кабинете</h1><p>Профессиональная платформа для рекрутеров: прозрачные начисления, статусы курьеров, заявки на выплаты, новости и актуальные городские ставки.</p><div class="hero-actions"><a class="button" href="register.php">Начать работу</a><a class="button button-outline" href="login.php">Войти в кабинет</a></div></div>
        <div class="hero-card"><img src="<?= e(asset_url('img/landing-dashboard.webp')) ?>" alt="Дашборд рекрутера" loading="lazy" onerror="this.style.display='none'"><div class="metric"><b>30 дней</b><span>видимость динамики</span></div><div class="metric"><b>24/7</b><span>доступ к кабинету</span></div></div>
    </section>
    <section id="benefits" class="section-block reveal"><div class="section-heading centered"><p class="eyebrow">Преимущества</p><h2>Инструменты, которые экономят время рекрутера</h2></div><div class="benefits-grid"><article class="benefit-card"><i class="fas fa-wallet benefit-icon"></i><h3>Прозрачный баланс</h3><p>Доход считается по активным курьерам, городским ставкам и одобренным выплатам.</p></article><article class="benefit-card"><i class="fas fa-bell benefit-icon"></i><h3>Новости и уведомления</h3><p>Важные объявления видны в кабинете и помогают быстро реагировать на изменения.</p></article><article class="benefit-card"><i class="fas fa-shield-halved benefit-icon"></i><h3>Безопасность</h3><p>CSRF-защита, безопасный вывод данных и подтверждение почты повышают надёжность.</p></article></div></section>
    <section id="process" class="section-block process-section reveal"><div class="section-heading centered"><p class="eyebrow">Как это работает</p><h2>Три шага до стабильной воронки</h2></div><div class="process-grid"><article class="process-card"><span>01</span><h3>Регистрируетесь</h3><p>Подтверждаете email и получаете персональную реферальную ссылку.</p></article><article class="process-card"><span>02</span><h3>Приглашаете курьеров</h3><p>Кандидаты попадают в систему, а администратор подтверждает их статус.</p></article><article class="process-card"><span>03</span><h3>Получаете выплаты</h3><p>Отправляете заявку на вывод, когда баланс достигает минимальной суммы.</p></article></div></section>
    <section class="section-block reveal"><div class="split-card"><div><p class="eyebrow">Аналитика</p><h2>Все ключевые показатели под рукой</h2><p>Курьеры, заказы, ставка города, доступный баланс и история выплат собраны в единой панели с аккуратным мобильным интерфейсом.</p></div><img src="<?= e(asset_url('img/analytics.webp')) ?>" alt="Аналитика" loading="lazy" onerror="this.style.display='none'"></div></section>
    <section class="section-block faq-section reveal"><div class="section-heading centered"><p class="eyebrow">FAQ</p><h2>Частые вопросы</h2></div><div class="faq-accordion"><div class="faq-item"><button class="faq-question" type="button">Когда можно запросить выплату?</button><div class="faq-answer"><p>После достижения минимальной суммы, указанной в настройках платформы.</p></div></div><div class="faq-item"><button class="faq-question" type="button">Как курьер привязывается к рекрутеру?</button><div class="faq-answer"><p>Через персональную ссылку или ручное добавление в личном кабинете рекрутера.</p></div></div></div></section>
</main>
<footer class="site-footer"><p>© <?= date('Y') ?> Яндекс Еда Рекрутинг. Все права защищены.</p><a href="mailto:support@partner-yaedalavka.ru">support@partner-yaedalavka.ru</a></footer>
<?php require __DIR__ . '/../includes/footer.php'; ?>
