<?php
return array(
    'host' => getenv('SMTP_HOST') ? getenv('SMTP_HOST') : 'smtp.example.com',
    'port' => getenv('SMTP_PORT') ? (int) getenv('SMTP_PORT') : 587,
    'username' => getenv('SMTP_USERNAME') ? getenv('SMTP_USERNAME') : 'support@partner-yaedalavka.ru',
    'password' => getenv('SMTP_PASSWORD') ? getenv('SMTP_PASSWORD') : 'change-me',
    'encryption' => getenv('SMTP_ENCRYPTION') ? getenv('SMTP_ENCRYPTION') : 'tls',
    'from_email' => getenv('SMTP_FROM_EMAIL') ? getenv('SMTP_FROM_EMAIL') : 'support@partner-yaedalavka.ru',
    'from_name' => getenv('SMTP_FROM_NAME') ? getenv('SMTP_FROM_NAME') : 'Поддержка партнёров Яндекс Еды',
    'recaptcha_site_key' => getenv('RECAPTCHA_SITE_KEY') ? getenv('RECAPTCHA_SITE_KEY') : '',
    'recaptcha_secret_key' => getenv('RECAPTCHA_SECRET_KEY') ? getenv('RECAPTCHA_SECRET_KEY') : '',
);
