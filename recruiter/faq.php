<?php
require_once __DIR__ . '/../includes/functions.php'; ensure_default_settings($pdo); require_recruiter();
$pageTitle='FAQ'; $bodyClass='app-layout'; $activePage='faq'; $pageHeading='FAQ'; require __DIR__ . '/../includes/header.php'; require __DIR__ . '/../includes/recruiter-sidebar.php';
?>
<section class="panel"><div class="panel-heading"><h2>Вопросы и ответы</h2></div><div class="panel-body faq-list"><details open><summary>Почему курьер не влияет на баланс?</summary><p>Баланс считают только курьеры со статусом «Активный».</p></details><details><summary>Что значит «Не лид»?</summary><p>Администратор отклонил заявку. Причина отображается в таблице курьеров и приходит уведомлением.</p></details><details><summary>Как считается ставка?</summary><p>Приоритет: индивидуальная ставка рекрутера → ставка города → глобальная ставка.</p></details></div></section>
<?php require __DIR__ . '/../includes/recruiter-footer.php'; ?>
