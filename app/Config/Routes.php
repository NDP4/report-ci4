<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route redirect to dashboard
$routes->get('/', 'TicketController::index');

// Auth routes
$routes->group('', [], static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('auth/authenticate', 'AuthController::authenticate');
    $routes->get('auth/logout', 'AuthController::logout');
});

// Import routes (admin only)
$routes->group('', ['filter' => 'admin'], static function ($routes) {
    $routes->get('import', 'ImportController::index');
    $routes->post('import/upload', 'ImportController::upload');
    $routes->get('import/template', 'ImportController::template');
});

// Dashboard routes (protected)
$routes->group('dashboard', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->post('getCategoriesByMainCategory', 'Dashboard::getCategoriesByMainCategory');
    $routes->get('getChartData', 'Dashboard::getChartData');
    $routes->post('getDataTables', 'TicketController::getDataTables');
    $routes->get('detail/(:segment)', 'TicketController::detail/$1');
    $routes->get('export', 'TicketController::export');
    $routes->get('about', 'TicketController::about');
    $routes->get('ticket', 'TicketController::ticket');

    // Import routes (admin only within dashboard)
    $routes->group('', ['filter' => 'admin'], static function ($routes) {
        $routes->get('import', 'ImportController::index');
    });

    // User Management (admin only)
    $routes->group('', ['filter' => 'admin'], static function ($routes) {
        $routes->get('user', 'Dashboard\User::index');
        $routes->get('user/create', 'Dashboard\User::create');
        $routes->post('user/store', 'Dashboard\User::store');
        $routes->get('user/edit/(:num)', 'Dashboard\User::edit/$1');
        $routes->post('user/update/(:num)', 'Dashboard\User::update/$1');
        $routes->post('user/delete/(:num)', 'Dashboard\User::delete/$1');
        $routes->get('activitylog', 'Dashboard::activitylog');
        $routes->get('activitylog/export', 'Dashboard::exportActivityLog');
        $routes->post('activitylog/delete/(:num)', 'Dashboard::deleteActivityLog/$1');
        $routes->post('activitylog/bulk-delete', 'Dashboard::bulkDeleteActivityLog');
        $routes->post('activitylog/clear-all', 'Dashboard::clearAllActivityLogs');
    });

    // Help routes
    $routes->get('help/documentation', 'HelpController::documentation');
});
