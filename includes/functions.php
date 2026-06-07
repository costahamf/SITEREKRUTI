<?php
require_once __DIR__ . '/config.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url_for($path)
{
    $path = ltrim($path, '/');
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $pos = strpos($script, '/public/');
    if ($pos !== false) {
        $base = substr($script, 0, $pos + 7);
    } else {
        $base = rtrim(dirname($script), '/');
        if (basename($base) === 'admin' || basename($base) === 'recruiter') {
            $parent = dirname($base);
            $base = ($parent === '/') ? '/public' : rtrim($parent, '/') . '/public';
        }
    }
    return rtrim($base, '/') . '/' . $path;
}

function admin_url($path)
{
    return '../admin/' . ltrim($path, '/');
}

function recruiter_url($path)
{
    return '../recruiter/' . ltrim($path, '/');
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function current_user_id()
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function is_logged_in()
{
    return current_user_id() !== null;
}

function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        redirect(url_for('login.php'));
    }
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Доступ запрещён.');
    }
}

function require_recruiter()
{
    require_login();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recruiter') {
        redirect(admin_url('index.php'));
    }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!is_string($token) || !hash_equals(isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '', $token)) {
        http_response_code(419);
        exit('Сессия устарела. Обновите страницу и попробуйте снова.');
    }
}

function app_base_url()
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443');
    $scheme = $https ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $public = url_for('');
    return rtrim($scheme . '://' . $host . $public, '/');
}

function admin_exists($pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    return (int) $stmt->fetchColumn() > 0;
}

function ensure_default_settings($pdo)
{
    $settings = array('reward_per_order' => '30', 'min_withdrawal' => '500');
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = setting_value');
        $stmt->execute(array($key, $value));
    }
}

function get_setting($pdo, $key, $default)
{
    $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute(array($key));
    $value = $stmt->fetchColumn();
    return $value !== false ? (string) $value : $default;
}

function set_setting($pdo, $key, $value)
{
    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute(array($key, $value));
}

function generate_referral_code($pdo)
{
    do {
        $code = strtoupper(bin2hex(random_bytes(4)));
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE referral_code = ?');
        $stmt->execute(array($code));
    } while ((int) $stmt->fetchColumn() > 0);
    return $code;
}

function get_user_by_email($pdo, $email)
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute(array($email));
    $user = $stmt->fetch();
    return $user ? $user : null;
}

function login_user($user)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
}

function format_money($amount)
{
    return number_format((float) $amount, 0, ',', ' ') . ' ₽';
}

function delivery_type_label($type)
{
    switch ($type) {
        case 'auto': return 'Авто';
        case 'bike': return 'Вело';
        case 'foot': return 'Пешим';
        default: return 'Не указан';
    }
}

function status_label($status)
{
    switch ($status) {
        case 'pending': return 'Проверка';
        case 'active': return 'Активный';
        case 'rejected': return 'Не лид';
        case 'paused': return 'На паузе';
        case 'blocked': return 'Заблокирован';
        default: return 'Проверка';
    }
}

function status_class($status)
{
    switch ($status) {
        case 'active': return 'success';
        case 'rejected':
        case 'blocked': return 'danger';
        case 'paused': return 'warning';
        default: return 'neutral';
    }
}

function get_recruiter_reward_override($pdo, $recruiterId)
{
    $stmt = $pdo->prepare('SELECT reward_per_order FROM recruiter_overrides WHERE recruiter_id = ? LIMIT 1');
    $stmt->execute(array($recruiterId));
    $value = $stmt->fetchColumn();
    return $value !== false ? (float) $value : null;
}

function get_city_rate($pdo, $city)
{
    $stmt = $pdo->prepare('SELECT reward_per_order FROM city_rates WHERE city = ? LIMIT 1');
    $stmt->execute(array($city));
    $value = $stmt->fetchColumn();
    return $value !== false ? (float) $value : null;
}

function reward_for_courier($pdo, $recruiterId, $city)
{
    $override = get_recruiter_reward_override($pdo, $recruiterId);
    if ($override !== null) {
        return $override;
    }
    $cityRate = get_city_rate($pdo, $city);
    if ($cityRate !== null) {
        return $cityRate;
    }
    return (float) get_setting($pdo, 'reward_per_order', '30');
}

function get_recruiter_stats($pdo, $recruiterId)
{
    $stmt = $pdo->prepare('SELECT * FROM couriers WHERE recruiter_id = ? AND deleted_at IS NULL ORDER BY registered_at DESC');
    $stmt->execute(array($recruiterId));
    $couriers = $stmt->fetchAll();
    $totalOrders = 0;
    $totalEarnings = 0;
    foreach ($couriers as $courier) {
        if ($courier['status'] === 'active') {
            $rate = reward_for_courier($pdo, $recruiterId, $courier['city']);
            $totalOrders += (int) $courier['orders_count'];
            $totalEarnings += (int) $courier['orders_count'] * $rate;
        }
    }
    $withdrawn = recruiter_withdrawn_amount($pdo, $recruiterId);
    return array('couriers' => $couriers, 'total_couriers' => count($couriers), 'total_orders' => $totalOrders, 'total_earnings' => $totalEarnings, 'withdrawn' => $withdrawn, 'available' => max(0, $totalEarnings - $withdrawn));
}

function recruiter_withdrawn_amount($pdo, $recruiterId)
{
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE recruiter_id = ? AND status = 'approved'");
    $stmt->execute(array($recruiterId));
    return (float) $stmt->fetchColumn();
}

function unread_notifications_count($pdo, $recruiterId)
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE recruiter_id = ? AND is_read = 0');
    $stmt->execute(array($recruiterId));
    return (int) $stmt->fetchColumn();
}

function latest_notifications($pdo, $recruiterId)
{
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE recruiter_id = ? ORDER BY created_at DESC LIMIT 10');
    $stmt->execute(array($recruiterId));
    return $stmt->fetchAll();
}

function mark_notifications_read($pdo, $recruiterId)
{
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE recruiter_id = ?');
    $stmt->execute(array($recruiterId));
}

function create_notification($pdo, $recruiterId, $title, $message)
{
    $stmt = $pdo->prepare('INSERT INTO notifications (recruiter_id, title, message) VALUES (?, ?, ?)');
    $stmt->execute(array($recruiterId, $title, $message));
}

function get_cities($pdo)
{
    $cities = array();
    $stmt = $pdo->query('SELECT city FROM city_rates ORDER BY city ASC');
    foreach ($stmt->fetchAll() as $row) {
        $cities[] = $row['city'];
    }
    return $cities;
}
