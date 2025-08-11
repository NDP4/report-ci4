<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route redirect to login
$routes->get('/', 'AuthController::login');

// Auth routes
$routes->group('', [], static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('auth/authenticate', 'AuthController::authenticate');
    $routes->get('auth/logout', 'AuthController::logout');
});

// Import routes
$routes->get('import', 'ImportController::index');
$routes->post('import/upload', 'ImportController::upload');
$routes->get('import/template', 'ImportController::template');

// Dashboard routes (protected)
$routes->group('dashboard', [], static function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('about', 'DashboardController::about');
});
