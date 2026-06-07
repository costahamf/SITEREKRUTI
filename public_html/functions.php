<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function is_logged_in(): bool
{
    return current_user_id() !== null;
}

function is_admin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_recruiter(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'recruiter') {
        redirect('admin.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Доступ запрещён.');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Сессия устарела. Обновите страницу и попробуйте снова.');
    }
}

function admin_exists(PDO $pdo): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();

    return (int) $stmt->fetchColumn() > 0;
}

function ensure_default_settings(PDO $pdo): void
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = 'reward_per_order'");
    $stmt->execute();
    if ((int) $stmt->fetchColumn() === 0) {
        $insert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('reward_per_order', '30')");
        $insert->execute();
    }
}

function get_setting(PDO $pdo, string $key, string $default = ''): string
{
    $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();

    return $value !== false ? (string) $value : $default;
}

function set_setting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}

function generate_referral_code(PDO $pdo): string
{
    do {
        $code = strtoupper(bin2hex(random_bytes(4)));
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE referral_code = ?');
        $stmt->execute([$code]);
    } while ((int) $stmt->fetchColumn() > 0);

    return $code;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
}

function get_user_by_email(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function app_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    $path = $path === '' || $path === '.' ? '' : $path;

    return $scheme . '://' . $host . $path;
}

function format_money(float $amount): string
{
    return number_format($amount, 0, ',', ' ') . ' ₽';
}

function status_label(string $status): string
{
    switch ($status) {
        case 'paused':
            return 'На паузе';
        case 'blocked':
            return 'Заблокирован';
        default:
            return 'Активен';
    }
}

function get_recruiters(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id, full_name, email, role, referral_code, created_at FROM users WHERE role = 'recruiter' ORDER BY created_at DESC");

    return $stmt->fetchAll();
}