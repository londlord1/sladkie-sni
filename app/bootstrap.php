<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/helpers.php';

date_default_timezone_set((string)config('timezone', 'Europe/Moscow'));

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

if (!config('debug')) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_name('hotel_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

require BASE_PATH . '/app/db.php';
require BASE_PATH . '/app/repository.php';
require BASE_PATH . '/app/validation.php';
require BASE_PATH . '/app/actions.php';
