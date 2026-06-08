<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_default_settings($pdo);
$setupMode = !admin_exists($pdo);
$errors = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $setupMode) {
    verify_csrf();
    $fullName = trim(isset($_POST['full_name']) ? $_POST['full_name'] : '');
    $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    if ($fullName === '') { $errors[] = 'Укажите имя администратора.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Укажите корректный email.'; }
    if (strlen($password) < 8) { $errors[] = 'Пароль должен содержать минимум 8 символов.'; }
    if ($password !== $confirm) { $errors[] = 'Пароли не совпадают.'; }
    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role, referral_code) VALUES (?, ?, ?, 'admin', NULL)");
        $stmt->execute(array($email, password_hash($password, PASSWORD_DEFAULT), $fullName));
        login_user(get_user_by_email($pdo, $email));
        redirect(admin_url('index.php'));
    }
}
$pageTitle = 'Рекрутинг курьеров Яндекс Еды';
$bodyClass = 'landing-page';
require __DIR__ . '/../includes/header.php';
?>
<nav class="marketing-nav" aria-label="Главная навигация">
    <a class="brand" href="index.php"><span class="brand-mark">Я</span><span>Рекрутинг</span></a>
    <div class="nav-actions">
        <?php if (is_logged_in()): ?>
            <a class="nav-link" href="<?= e(is_admin() ? admin_url('index.php') : recruiter_url('dashboard.php')) ?>">Кабинет</a>
            <a class="button button-outline" href="logout.php">Выйти</a>
        <?php else: ?>
            <a class="nav-link" href="login.php">Войти</a>
            <a class="button" href="register.php">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>

<main>
    <section class="landing-hero">
        <div class="hero-orb hero-orb-one" aria-hidden="true"></div>
        <div class="hero-orb hero-orb-two" aria-hidden="true"></div>
        <div class="hero-dots" aria-hidden="true"></div>
        <p class="hero-kicker">Партнёрская платформа для рекрутеров</p>
        <h1>Привлекайте курьеров в Яндекс Еду и зарабатывайте</h1>
        <p class="hero-subtitle">Вы получаете вознаграждение за каждого активного курьера. Прозрачная статистика, быстрые выплаты</p>
        <div class="hero-actions">
            <a class="button button-large" href="register.php">Начать зарабатывать</a>
            <a class="button button-large button-outline" href="login.php">Войти</a>
        </div>
        <div class="hero-metrics" aria-label="Ключевые возможности">
            <div><strong>1 мин</strong><span>на заявку курьера</span></div>
            <div><strong>24/7</strong><span>доступ к статистике</span></div>
            <div><strong>3 ставки</strong><span>глобальная, городская, личная</span></div>
        </div>
    </section>

    <?php if ($setupMode): ?>
    <section class="setup-panel">
        <div>
            <p class="eyebrow">Первый запуск</p>
            <h2>Создайте администратора</h2>
            <p>Форма доступна только пока в базе нет администратора. После создания вы сразу попадёте в админ-панель.</p>
        </div>
        <div>
            <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
            <form method="post" class="form-card setup-form" data-validate="true">
                <?= csrf_field() ?>
                <label>Имя администратора<input type="text" name="full_name" required></label>
                <label>Email<input type="email" name="email" required></label>
                <label>Пароль<input type="password" name="password" minlength="8" required></label>
                <label>Повторите пароль<input type="password" name="password_confirm" minlength="8" required></label>
                <button class="button" type="submit">Создать администратора</button>
            </form>
        </div>
    </section>
    <?php endif; ?>

    <section class="section-block benefits-section">
        <div class="section-heading centered">
            <p class="eyebrow">Преимущества</p>
            <h2>Всё, что нужно для понятной работы с лидами</h2>
        </div>
        <div class="benefits-grid">
            <article class="benefit-card">
                <i class="fas fa-wallet benefit-icon"></i>
                <h3>Мгновенные выплаты</h3>
                <p>Подайте заявку и получайте деньги быстро и предсказуемо.</p>
            </article>
            <article class="benefit-card">
                <i class="fas fa-chart-line benefit-icon"></i>
                <h3>Прозрачная статистика</h3>
                <p>Вся аналитика в одном месте: статусы, начисления и динамика.</p>
            </article>
            <article class="benefit-card">
                <i class="fas fa-mobile-alt benefit-icon"></i>
                <h3>Работа из любой точки</h3>
                <p>Используйте ноутбук или смартфон — платформа всегда под рукой.</p>
            </article>
        </div>
    </section>

    <section class="section-block process-section">
        <div class="section-heading centered">
            <p class="eyebrow">Как это работает</p>
            <h2>Чистый процесс без лишних действий</h2>
        </div>
        <div class="process-grid">
            <article class="process-card"><span>01</span><h3>Рекрутер получает ссылку</h3><p>После регистрации система создаёт персональный код для приглашения курьеров.</p></article>
            <article class="process-card"><span>02</span><h3>Курьер проходит проверку</h3><p>Заявки попадают администратору, который принимает активных лидов или указывает причину отказа.</p></article>
            <article class="process-card"><span>03</span><h3>Баланс считается автоматически</h3><p>Доход начисляется только за активных курьеров по личной, городской или глобальной ставке.</p></article>
        </div>
    </section>

    <section class="section-block faq-section">
        <div class="section-heading centered">
            <p class="eyebrow">FAQ</p>
            <h2>Ответы на частые вопросы</h2>
        </div>
        <div class="faq-accordion">
            <div class="faq-item">
                <button class="faq-question" type="button">Сколько можно заработать в месяц?</button>
                <div class="faq-answer"><p>Доход зависит от активности: от 0 до 50 000₽+ при регулярном потоке приглашений.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button">Когда я получу выплату?</button>
                <div class="faq-answer"><p>После подтверждения заявки администратором. Статус выплаты всегда виден в личном кабинете.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button">Как курьер привязывается ко мне?</button>
                <div class="faq-answer"><p>Курьер заполняет форму по вашей персональной ссылке или вы добавляете его вручную в кабинете.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button">Почему курьер может быть отклонён?</button>
                <div class="faq-answer"><p>Если заявка не подходит требованиям, администратор укажет причину, а вы получите уведомление.</p></div>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <p>© <?= date('Y') ?> Яндекс Еда Рекрутинг. Все права защищены.</p>
    <a href="mailto:partners@example.com">partners@example.com</a>
</footer>
<?php require __DIR__ . '/../includes/footer.php'; ?>
