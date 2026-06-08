<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update these values or set environment variables on hosting.
define('DB_HOST', getenv('DB_HOST') ? getenv('DB_HOST') : '127.0.0.1:3308');
define('DB_NAME', getenv('DB_NAME') ? getenv('DB_NAME') : 'costahamf');
define('DB_USER', getenv('DB_USER') ? getenv('DB_USER') : 'costahamf');
define('DB_PASS', getenv('DB_PASS') ? getenv('DB_PASS') : 'Costa132465');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Яндекс Еда Рекрутинг');
define('APP_BASE_PATH', rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/'));

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');

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