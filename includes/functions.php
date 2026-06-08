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
        if ($base === '' || $base === '.') { $base = '/public'; }
    }
    return rtrim($base, '/') . '/' . $path;
}

function asset_url($path)
{
    return url_for('../assets/' . ltrim($path, '/'));
}

function upload_url($path)
{
    return url_for('../uploads/' . ltrim($path, '/'));
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
    if (!is_logged_in()) { redirect(url_for('login.php')); }
}

function require_admin()
{
    require_login();
    if (!is_admin()) { http_response_code(403); exit('Доступ запрещён.'); }
}

function require_recruiter()
{
    require_login();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'recruiter') { redirect(admin_url('index.php')); }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
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
        http_response_code(419); exit('Сессия устарела. Обновите страницу и попробуйте снова.');
    }
}

function app_base_url()
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443');
    $scheme = $https ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    return rtrim($scheme . '://' . $host . url_for(''), '/');
}

function mail_config()
{
    return require ROOT_PATH . '/config/mail.php';
}

function recaptcha_site_key()
{
    $config = mail_config();
    return isset($config['recaptcha_site_key']) ? $config['recaptcha_site_key'] : '';
}

function verify_recaptcha($response)
{
    $config = mail_config();
    if (empty($config['recaptcha_secret_key'])) { return true; }
    if (!is_string($response) || trim($response) === '') { return false; }
    $data = http_build_query(array('secret' => $config['recaptcha_secret_key'], 'response' => $response, 'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''));
    $context = stream_context_create(array('http' => array('method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $data, 'timeout' => 5)));
    $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    if ($result === false) { return false; }
    $json = json_decode($result, true);
    return is_array($json) && !empty($json['success']);
}

function send_mail_smtp($to, $subject, $htmlBody, $textBody)
{
    $config = mail_config();
    $autoload = ROOT_PATH . '/vendor/autoload.php';
    if (file_exists($autoload)) { require_once $autoload; }
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['encryption'];
        $mail->Port = (int) $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;
        $mail->send();
        return true;
    }
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: " . $config['from_name'] . ' <' . $config['from_email'] . ">\r\n";
    return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlBody, $headers);
}

function generate_six_digit_code()
{
    return (string) random_int(100000, 999999);
}

function ensure_database_schema($pdo)
{
    $queries = array(
        "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 1",
        "ALTER TABLE users ADD COLUMN email_verification_code VARCHAR(6) NULL",
        "ALTER TABLE users ADD COLUMN email_verification_expires_at DATETIME NULL",
        "ALTER TABLE users ADD COLUMN last_verification_sent_at DATETIME NULL",
        "ALTER TABLE users ADD COLUMN balance_correction DECIMAL(12,2) NOT NULL DEFAULT 0",
        "ALTER TABLE city_rates ADD COLUMN max_earnings_per_courier INT NULL DEFAULT NULL",
        "ALTER TABLE news ADD COLUMN image_path VARCHAR(255) NULL DEFAULT NULL"
    );
    foreach ($queries as $sql) { try { $pdo->exec($sql); } catch (Exception $e) { } }
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL, token VARCHAR(6) NOT NULL, expires_at DATETIME NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_password_resets_email (email), INDEX idx_password_resets_token (token)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL, ip_address VARCHAR(64) NOT NULL, attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, success TINYINT(1) NOT NULL DEFAULT 0, INDEX idx_login_attempts_lookup (email, ip_address, attempted_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS balance_history (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, recruiter_id INT UNSIGNED NOT NULL, admin_id INT UNSIGNED NULL, amount DECIMAL(12,2) NOT NULL, comment VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_balance_history_recruiter (recruiter_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function ensure_default_settings($pdo)
{
    ensure_database_schema($pdo);
    $settings = array('reward_per_order' => '30', 'min_withdrawal' => '500', 'city_rates_valid_from' => '', 'city_rates_valid_to' => '', 'support_bot_url' => 'https://t.me/ваш_бот');
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

function admin_exists($pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    return (int) $stmt->fetchColumn() > 0;
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

function failed_login_count($pdo, $email)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND success = 0 AND attempted_at > (NOW() - INTERVAL 15 MINUTE)");
    $stmt->execute(array($email, $ip));
    return (int) $stmt->fetchColumn();
}

function record_login_attempt($pdo, $email, $success)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $stmt = $pdo->prepare('INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, ?)');
    $stmt->execute(array($email, $ip, $success ? 1 : 0));
}

function send_verification_code($pdo, $email)
{
    $code = generate_six_digit_code();
    $stmt = $pdo->prepare('UPDATE users SET email_verification_code = ?, email_verification_expires_at = DATE_ADD(NOW(), INTERVAL 20 MINUTE), last_verification_sent_at = NOW() WHERE email = ?');
    $stmt->execute(array($code, $email));
    send_mail_smtp($email, 'Код подтверждения email', '<p>Ваш код подтверждения: <strong>' . e($code) . '</strong></p>', 'Ваш код подтверждения: ' . $code);
}

function create_password_reset($pdo, $email)
{
    $code = generate_six_digit_code();
    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
    $stmt->execute(array($email));
    $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 20 MINUTE))');
    $stmt->execute(array($email, $code));
    send_mail_smtp($email, 'Код восстановления пароля', '<p>Ваш код восстановления: <strong>' . e($code) . '</strong></p>', 'Ваш код восстановления: ' . $code);
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

function city_rates_active($pdo)
{
    $from = get_setting($pdo, 'city_rates_valid_from', '');
    $to = get_setting($pdo, 'city_rates_valid_to', '');
    $today = date('Y-m-d');
    if ($from !== '' && $today < $from) { return false; }
    if ($to !== '' && $today > $to) { return false; }
    return true;
}

function get_city_rate_row($pdo, $city)
{
    if (!city_rates_active($pdo)) { return null; }
    $stmt = $pdo->prepare('SELECT * FROM city_rates WHERE LOWER(city) = LOWER(?) LIMIT 1');
    $stmt->execute(array($city));
    $row = $stmt->fetch();
    return $row ? $row : null;
}

function courier_reward_total($pdo, $courier)
{
    $global = (float) get_setting($pdo, 'reward_per_order', '30');
    $rateRow = get_city_rate_row($pdo, $courier['city']);
    $rate = $rateRow ? (float) $rateRow['reward_per_order'] : $global;
    $total = (int) $courier['orders_count'] * $rate;
    if ($rateRow && $rateRow['max_earnings_per_courier'] !== null && $rateRow['max_earnings_per_courier'] !== '') {
        $max = (float) $rateRow['max_earnings_per_courier'];
        if ($max >= 0 && $total > $max) { $total = $max; }
    }
    return $total;
}

function calculate_recruiter_gross($pdo, $recruiterId)
{
    $stmt = $pdo->prepare("SELECT * FROM couriers WHERE recruiter_id = ? AND status = 'active' AND deleted_at IS NULL");
    $stmt->execute(array($recruiterId));
    $sum = 0;
    foreach ($stmt->fetchAll() as $courier) { $sum += courier_reward_total($pdo, $courier); }
    return $sum;
}

function approved_withdrawals_amount($pdo, $recruiterId)
{
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE recruiter_id = ? AND status = 'approved'");
    $stmt->execute(array($recruiterId));
    return (float) $stmt->fetchColumn();
}

function balance_correction_amount($pdo, $recruiterId)
{
    $stmt = $pdo->prepare("SELECT COALESCE(balance_correction, 0) FROM users WHERE id = ?");
    $stmt->execute(array($recruiterId));
    return (float) $stmt->fetchColumn();
}

function calculate_recruiter_balance($pdo, $recruiterId)
{
    return calculate_recruiter_gross($pdo, $recruiterId) - approved_withdrawals_amount($pdo, $recruiterId) + balance_correction_amount($pdo, $recruiterId);
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

function create_news_notification($pdo, $title, $message)
{
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'recruiter'");
    foreach ($stmt->fetchAll() as $row) { create_notification($pdo, (int) $row['id'], $title, $message); }
}

function get_cities($pdo)
{
    $cities = array();
    $stmt = $pdo->query('SELECT city FROM city_rates ORDER BY city ASC');
    foreach ($stmt->fetchAll() as $row) { $cities[] = $row['city']; }
    return $cities;
}

function handle_news_image_upload($field)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) { return null; }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) { throw new RuntimeException('Ошибка загрузки изображения.'); }
    if ($file['size'] > 2 * 1024 * 1024) { throw new RuntimeException('Изображение должно быть не больше 2 МБ.'); }
    $info = getimagesize($file['tmp_name']);
    if (!$info) { throw new RuntimeException('Файл не является изображением.'); }
    $mime = $info['mime'];
    if (!is_dir(NEWS_UPLOADS_PATH)) { mkdir(NEWS_UPLOADS_PATH, 0775, true); }
    $name = 'news-' . date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.webp';
    $dest = NEWS_UPLOADS_PATH . '/' . $name;
    if ($mime === 'image/webp') {
        move_uploaded_file($file['tmp_name'], $dest);
    } else {
        if (!function_exists('imagewebp')) { throw new RuntimeException('На сервере нет GD/WebP для конвертации.'); }
        if ($mime === 'image/jpeg') { $img = imagecreatefromjpeg($file['tmp_name']); }
        elseif ($mime === 'image/png') { $img = imagecreatefrompng($file['tmp_name']); imagepalettetotruecolor($img); imagealphablending($img, true); imagesavealpha($img, true); }
        else { throw new RuntimeException('Допустимы WebP, JPG или PNG с конвертацией в WebP.'); }
        imagewebp($img, $dest, 85);
        imagedestroy($img);
    }
    return 'news/' . $name;
}

function chart_series_last_30_days($pdo)
{
    $labels = array(); $recruiters = array(); $couriers = array(); $rewards = array();
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime('-' . $i . ' days'));
        $labels[] = date('d.m', strtotime($date));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role='recruiter' AND DATE(created_at)=?");
        $stmt->execute(array($date)); $recruiters[] = (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM couriers WHERE deleted_at IS NULL AND DATE(registered_at)=?");
        $stmt->execute(array($date)); $couriers[] = (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT * FROM couriers WHERE deleted_at IS NULL AND status='active' AND DATE(registered_at)=?");
        $stmt->execute(array($date));
        $sum = 0; foreach ($stmt->fetchAll() as $c) { $sum += courier_reward_total($pdo, $c); }
        $rewards[] = $sum;
    }
    return array('labels' => $labels, 'recruiters' => $recruiters, 'couriers' => $couriers, 'rewards' => $rewards);
}

function reward_for_courier($pdo, $recruiterId, $city)
{
    $global = (float) get_setting($pdo, 'reward_per_order', '30');
    $rateRow = get_city_rate_row($pdo, $city);
    return $rateRow ? (float) $rateRow['reward_per_order'] : $global;
}

function get_recruiter_stats($pdo, $recruiterId)
{
    $stmt = $pdo->prepare('SELECT * FROM couriers WHERE recruiter_id = ? AND deleted_at IS NULL ORDER BY registered_at DESC');
    $stmt->execute(array($recruiterId));
    $couriers = $stmt->fetchAll();
    $totalOrders = 0;
    foreach ($couriers as $courier) { if ($courier['status'] === 'active') { $totalOrders += (int) $courier['orders_count']; } }
    $gross = calculate_recruiter_gross($pdo, $recruiterId);
    $withdrawn = approved_withdrawals_amount($pdo, $recruiterId);
    return array('couriers' => $couriers, 'total_couriers' => count($couriers), 'total_orders' => $totalOrders, 'total_earnings' => $gross, 'withdrawn' => $withdrawn, 'available' => calculate_recruiter_balance($pdo, $recruiterId));
}
