<?php
if (!isset($pageTitle)) { $pageTitle = APP_NAME; }
$mailCfg = mail_config();
?><!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <?php if (!empty($needsRecaptcha) && !empty($mailCfg['recaptcha_site_key'])): ?><script src="https://www.google.com/recaptcha/api.js" async defer></script><?php endif; ?>
</head>
<body class="<?= isset($bodyClass) ? e($bodyClass) : '' ?>">
