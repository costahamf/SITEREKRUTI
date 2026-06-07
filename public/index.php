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
<nav class="marketing-nav">
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
    <section class="hero-section">
        <div class="hero-copy">
            <p class="eyebrow">Партнёрская платформа</p>
            <h1>Привлекайте курьеров в Яндекс Еду и контролируйте выплаты без таблиц.</h1>
            <p class="hero-text">Персональные ссылки, проверка курьеров администратором, городские ставки, индивидуальные вознаграждения и понятный баланс в одном кабинете.</p>
            <div class="hero-actions"><a class="button button-large" href="register.php">Стать рекрутером</a><a class="button button-large button-outline" href="#how">Как работает</a></div>
        </div>
        <div class="hero-visual">
            <img src="../assets/img/hero-image.webp" alt="Дашборд рекрутера" onerror="this.style.display='none'">
            <div class="visual-card"><strong>+ Добавить курьера</strong><span>Заявка сразу попадает на проверку</span></div>
            <div class="visual-card muted"><strong>30 ₽ / заказ</strong><span>или индивидуальная ставка</span></div>
        </div>
    </section>

    <?php if ($setupMode): ?>
    <section class="setup-panel">
        <div><p class="eyebrow">Первый запуск</p><h2>Создайте администратора</h2><p>Форма доступна только пока в базе нет администратора.</p></div>
        <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <form method="post" class="form-card setup-form">
            <?= csrf_field() ?>
            <label>Имя администратора<input type="text" name="full_name" required></label>
            <label>Email<input type="email" name="email" required></label>
            <label>Пароль<input type="password" name="password" minlength="8" required></label>
            <label>Повторите пароль<input type="password" name="password_confirm" minlength="8" required></label>
            <button class="button" type="submit">Создать администратора</button>
        </form>
    </section>
    <?php endif; ?>

    <section id="how" class="section-block">
        <div class="section-heading"><p class="eyebrow">Процесс</p><h2>От лида до выплаты — прозрачно</h2></div>
        <div class="feature-grid three">
            <article class="feature-card"><i class="fa-solid fa-link"></i><h3>1. Рекрутер получает ссылку</h3><p>Код привязки создаётся автоматически после регистрации.</p></article>
            <article class="feature-card"><i class="fa-solid fa-user-check"></i><h3>2. Курьер проходит проверку</h3><p>Заявки попадают в раздел «Проверка», где администратор принимает или отклоняет лида.</p></article>
            <article class="feature-card"><i class="fa-solid fa-wallet"></i><h3>3. Считается баланс</h3><p>Активные курьеры приносят доход по индивидуальной, городской или глобальной ставке.</p></article>
        </div>
    </section>

    <section class="section-block split-block">
        <div><p class="eyebrow">Для рекрутеров</p><h2>Кабинет с фокусом на работу</h2><p>Добавляйте офлайн-кандидатов, отслеживайте статусы, смотрите причины отклонения и создавайте заявки на выплату.</p></div>
        <ul class="check-list"><li>Уведомления о новостях и отклонённых курьерах</li><li>Таблица курьеров с заказами и вознаграждением</li><li>Ставки по городам и FAQ внутри кабинета</li></ul>
    </section>

    <section class="section-block faq-block">
        <div class="section-heading"><p class="eyebrow">FAQ</p><h2>Частые вопросы</h2></div>
        <details open><summary>Когда курьер начинает приносить доход?</summary><p>Только после статуса «Активный». Курьеры на проверке и «Не лид» не участвуют в расчёте.</p></details>
        <details><summary>Какая ставка применяется?</summary><p>Сначала индивидуальная ставка рекрутера, затем ставка города, затем глобальная ставка.</p></details>
        <details><summary>Можно ли добавить курьера без ссылки?</summary><p>Да, рекрутер добавляет его в кабинете, а администратор проверяет заявку.</p></details>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
