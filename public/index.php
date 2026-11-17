<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$route = isset($_GET['r']) && is_string($_GET['r']) ? $_GET['r'] : 'home';

$routes = [
    'home' => 'action_home',
    'catalog' => 'action_catalog',
    'booking' => 'action_booking',
    'booking.store' => 'action_booking_store',
    'booking.success' => 'action_booking_success',
    'admin.login' => 'action_admin_login',
    'admin.login.post' => 'action_admin_login_post',
    'admin.logout' => 'action_admin_logout',
    'admin.bookings' => 'action_admin_bookings',
    'admin.booking.status' => 'action_admin_booking_status',
    'admin.booking.delete' => 'action_admin_booking_delete',
];

if (!isset($routes[$route])) {
    http_response_code(404);
    render('errors/404', ['title' => 'Страница не найдена — Сладкие сны']);
    exit;
}

$routes[$route]();
