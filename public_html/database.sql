CREATE DATABASE IF NOT EXISTS yandex_food_recruiters CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE yandex_food_recruiters;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(190) NOT NULL,
    role ENUM('recruiter', 'admin') NOT NULL DEFAULT 'recruiter',
    referral_code VARCHAR(32) NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS couriers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(120) NOT NULL,
    last_name VARCHAR(120) NOT NULL,
    city VARCHAR(120) NOT NULL,
    phone VARCHAR(40) NULL,
    status ENUM('active', 'paused', 'blocked') NOT NULL DEFAULT 'active',
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    orders_count INT UNSIGNED NOT NULL DEFAULT 0,
    utm_campaign VARCHAR(190) NULL,
    CONSTRAINT fk_couriers_recruiter FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_couriers_recruiter (recruiter_id),
    INDEX idx_couriers_registered_at (registered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value)
VALUES ('reward_per_order', '30')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- Администратор создаётся через форму первого запуска на index.php,
-- если в таблице users ещё нет пользователя с role = 'admin'.
