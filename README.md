# Яндекс Еда Рекрутинг — модернизированная версия

Платформа для рекрутеров курьеров с личным кабинетом, админ-панелью, графиками, выплатами, FAQ, подтверждением почты и усиленной защитой.

## Новая структура

- `public/` — публичные страницы: `index.php`, `login.php`, `register.php`, `verify.php`, `forgot-password.php`, `reset-password.php`, `logout.php`.
- `admin/index.php` — единая админ-панель с вкладками статистики, проверки, рекрутеров, курьеров, ставок, новостей и настроек.
- `recruiter/` — кабинет рекрутера: дашборд, добавление курьера, городские ставки, FAQ, выплаты.
- `includes/` — общие функции, header/footer, сайдбары, безопасность.
- `assets/css/style.css` — весь CSS сайта.
- `assets/js/script.js` и `assets/js/admin.js` — общая логика и админские графики/таблицы.
- `assets/icons/` — будущие WebP-иконки меню.
- `uploads/news/` — изображения новостей.
- `config/mail.php` — SMTP и reCAPTCHA.

## Установка базы данных

1. Создайте базу MySQL/MariaDB.
2. Импортируйте `database.sql`.
3. Для существующей базы выполните ALTER-команды из нижней части `database.sql` или откройте сайт: `ensure_default_settings()` попытается добавить недостающие поля автоматически.
4. Проверьте настройки подключения в `includes/config.php` или задайте переменные окружения: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

Новые поля и таблицы:

- `users.email_verified`, `users.email_verification_code`, `users.email_verification_expires_at`, `users.last_verification_sent_at`.
- `users.balance_correction`.
- `city_rates.max_earnings_per_courier`.
- `news.image_path`.
- `password_resets`.
- `login_attempts`.
- `balance_history`.

## Создание первого администратора

Главная страница больше не показывает форму создания администратора. Если в базе нет администратора, лендинг остаётся обычным. Первого администратора создавайте только через существующий `setup.php` — файл не изменялся.

## Настройка SMTP для support@partner-yaedalavka.ru

Откройте `config/mail.php` и заполните:

```php
return array(
    'host' => 'smtp.your-provider.ru',
    'port' => 587,
    'username' => 'support@partner-yaedalavka.ru',
    'password' => 'SMTP_PASSWORD',
    'encryption' => 'tls',
    'from_email' => 'support@partner-yaedalavka.ru',
    'from_name' => 'Поддержка партнёров Яндекс Еды',
);
```

Рекомендуемые параметры:

- порт `587` + `tls`;
- порт `465` + `ssl`, если так требует почтовый провайдер;
- логин обычно равен полному адресу `support@partner-yaedalavka.ru`;
- пароль — пароль SMTP/пароль приложения, а не пароль от панели хостинга.

Для отправки через PHPMailer установите зависимости Composer так, чтобы появился `vendor/autoload.php`. Если PHPMailer недоступен, код использует PHP `mail()` как fallback, но для production лучше PHPMailer + SMTP.

## reCAPTCHA v2 checkbox

В `config/mail.php` заполните:

- `recaptcha_site_key` — публичный ключ;
- `recaptcha_secret_key` — секретный ключ.

Если ключи пустые, проверка пропускается, чтобы локальная разработка не блокировалась.

## Основные функции

- Лендинг без упоминания создания администратора.
- Lazy loading для изображений через `loading="lazy"`, включая меню-иконки и картинки лендинга/новостей.
- Админские графики Chart.js: новые рекрутеры, новые курьеры, динамика вознаграждений за 30 дней.
- Ручная корректировка баланса рекрутера с историей в `balance_history`.
- Прямое редактирование `orders_count` у курьеров.
- Городские ставки с лимитом `max_earnings_per_courier` и глобальным периодом действия.
- Накопительное редактирование ставок: изменения сохраняются только кнопкой «Сохранить все изменения».
- Новости с изображением WebP до 2 МБ, загрузка в `uploads/news/`, миниатюра и удаление изображения.
- Подтверждение почты при регистрации 6-значным кодом.
- Восстановление пароля 6-значным кодом.
- Лимит входа: 5 неудачных попыток за 15 минут.
- CSRF-токены на формах.
- XSS-защита через `htmlspecialchars` в функции `e()`.
- Security headers: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, базовый `Content-Security-Policy`.
- Современные карточки, hover-анимации, sticky table headers, zebra rows, мобильное burger-меню.

## Иконки меню WebP: промпты для генерации

Временно используется Font Awesome. В сайдбарах уже предусмотрены `<img src="/assets/icons/..." loading="lazy">`; когда добавите WebP-файлы, они начнут отображаться автоматически.

1. `dashboard-icon.webp` — `3D isometric clay icon, analytics dashboard with yellow chart, soft rounded shapes, non photorealistic, transparent background, 64x64`.
2. `couriers-icon.webp` — `3D isometric clay icon, friendly courier backpack and scooter, yellow black accents, non photorealistic, transparent background, 64x64`.
3. `city-rates-icon.webp` — `3D isometric clay icon, city map pin with coin, warm yellow palette, non photorealistic, transparent background, 64x64`.
4. `faq-icon.webp` — `3D isometric clay icon, question mark speech bubble, soft yellow and cream, non photorealistic, transparent background, 64x64`.
5. `withdraw-icon.webp` — `3D isometric clay icon, wallet with ruble coin, yellow black details, non photorealistic, transparent background, 64x64`.
6. `support-icon.webp` — `3D isometric clay icon, Telegram paper plane and headset, yellow accent, non photorealistic, transparent background, 64x64`.
7. `admin-stats-icon.webp` — `3D isometric clay icon, admin statistics bars and line chart, premium yellow dark palette, transparent background, 64x64`.
8. `admin-recruiters-icon.webp` — `3D isometric clay icon, group of recruiters people avatars, friendly rounded style, transparent background, 64x64`.
9. `admin-news-icon.webp` — `3D isometric clay icon, newspaper card with image placeholder, yellow badge, transparent background, 64x64`.
10. `admin-verification-icon.webp` — `3D isometric clay icon, shield with check mark, secure verification, yellow and dark accents, transparent background, 64x64`.
11. `settings-icon.webp` — `3D isometric clay icon, gear and sliders, warm yellow black cream palette, transparent background, 64x64`.
12. `add-courier-icon.webp` — `3D isometric clay icon, user plus sign and delivery bag, non photorealistic, transparent background, 64x64`.

## Какие ненужные файлы удалить из репозитория GitHub

После переноса структуры можно удалить устаревшие дубликаты, если они не используются вашим хостингом как entrypoint:

- `style.css` — перенесён в `assets/css/style.css`.
- `script.js` — перенесён в `assets/js/script.js`.
- `public/style.css` — больше не используется.
- `public/script.js` — больше не используется.
- Корневые дубликаты страниц `index.php`, `login.php`, `register.php`, `logout.php`, `dashboard.php`, `admin.php`, `courier-signup.php`, `functions.php`, если они не являются специальными прокси/алиасами хостинга.

Перед удалением корневых PHP-дубликатов проверьте маршрутизацию на хостинге. Основные рабочие файлы теперь находятся в `public/`, `admin/`, `recruiter/`, `includes/`, `assets/`, `config/`.
