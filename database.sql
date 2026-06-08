CREATE DATABASE IF NOT EXISTS yandex_food_recruiters CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE yandex_food_recruiters;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(190) NOT NULL,
    role ENUM('recruiter', 'admin') NOT NULL DEFAULT 'recruiter',
    referral_code VARCHAR(32) NULL UNIQUE,
    email_verified TINYINT(1) NOT NULL DEFAULT 1,
    email_verification_code VARCHAR(6) NULL,
    email_verification_expires_at DATETIME NULL,
    last_verification_sent_at DATETIME NULL,
    balance_correction DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS couriers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(120) NOT NULL,
    last_name VARCHAR(120) NOT NULL,
    city VARCHAR(120) NOT NULL,
    delivery_type ENUM('auto', 'foot', 'bike') NULL,
    phone VARCHAR(40) NULL,
    status ENUM('active', 'paused', 'blocked', 'pending', 'rejected') NOT NULL DEFAULT 'pending',
    rejection_reason VARCHAR(255) NULL,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    orders_count INT UNSIGNED NOT NULL DEFAULT 0,
    utm_campaign VARCHAR(190) NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_couriers_recruiter FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_couriers_recruiter (recruiter_id), INDEX idx_couriers_status (status), INDEX idx_couriers_registered_at (registered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) NOT NULL UNIQUE, setting_value VARCHAR(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS notifications (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, recruiter_id INT UNSIGNED NOT NULL, title VARCHAR(255) NOT NULL, message TEXT NOT NULL, is_read TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_notifications_recruiter FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_recruiter_unread (recruiter_id, is_read)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS withdrawals (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, recruiter_id INT UNSIGNED NOT NULL, amount DECIMAL(12,2) NOT NULL, comment VARCHAR(255) NULL, admin_comment VARCHAR(255) NULL, status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, processed_at TIMESTAMP NULL DEFAULT NULL, CONSTRAINT fk_withdrawals_recruiter FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_withdrawals_status (status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS city_rates (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, city VARCHAR(120) NOT NULL UNIQUE, reward_per_order INT UNSIGNED NOT NULL, max_earnings_per_courier INT NULL DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS news (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, body TEXT NOT NULL, image_path VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS password_resets (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL, token VARCHAR(6) NOT NULL, expires_at DATETIME NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_password_resets_email (email), INDEX idx_password_resets_token (token)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS login_attempts (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL, ip_address VARCHAR(64) NOT NULL, attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, success TINYINT(1) NOT NULL DEFAULT 0, INDEX idx_login_attempts_lookup (email, ip_address, attempted_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS balance_history (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, recruiter_id INT UNSIGNED NOT NULL, admin_id INT UNSIGNED NULL, amount DECIMAL(12,2) NOT NULL, comment VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_balance_history_recruiter (recruiter_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value) VALUES ('reward_per_order','30'),('min_withdrawal','500'),('city_rates_valid_from',''),('city_rates_valid_to',''),('support_bot_url','https://t.me/ваш_бот') ON DUPLICATE KEY UPDATE setting_value = setting_value;
INSERT INTO city_rates (city, reward_per_order, max_earnings_per_courier) VALUES ('Москва',30,NULL),('Санкт-Петербург',30,NULL),('Казань',25,NULL) ON DUPLICATE KEY UPDATE reward_per_order=reward_per_order;

-- Миграции для существующей базы:
-- ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 1;
-- ALTER TABLE users ADD COLUMN email_verification_code VARCHAR(6) NULL;
-- ALTER TABLE users ADD COLUMN email_verification_expires_at DATETIME NULL;
-- ALTER TABLE users ADD COLUMN last_verification_sent_at DATETIME NULL;
-- ALTER TABLE users ADD COLUMN balance_correction DECIMAL(12,2) NOT NULL DEFAULT 0;
-- ALTER TABLE city_rates ADD COLUMN max_earnings_per_courier INT NULL DEFAULT NULL;
-- ALTER TABLE news ADD COLUMN image_path VARCHAR(255) NULL DEFAULT NULL;
-- Администратор создаётся только через setup.php.
