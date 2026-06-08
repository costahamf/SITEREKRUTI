<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com data:; frame-src https://www.google.com; connect-src 'self'; base-uri 'self'; form-action 'self'");

// Update these values or set environment variables on hosting.
define('DB_HOST', getenv('DB_HOST') ? getenv('DB_HOST') : '127.0.0.1:3308');
define('DB_NAME', getenv('DB_NAME') ? getenv('DB_NAME') : 'costahamf');
define('DB_USER', getenv('DB_USER') ? getenv('DB_USER') : 'costahamf');
define('DB_PASS', getenv('DB_PASS') ? getenv('DB_PASS') : 'Costa132465');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Яндекс Еда Рекрутинг');
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('NEWS_UPLOADS_PATH', UPLOADS_PATH . '/news');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
);

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Ошибка подключения к базе данных. Проверьте настройки в includes/config.php.');
}
