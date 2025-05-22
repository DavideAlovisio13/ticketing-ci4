<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Main page
$routes->get('/', 'TicketsPage::index');

// API Routes Group
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {

    // Tickets resource routes
    $routes->resource('tickets', ['controller' => 'TicketsController']);

    // Additional ticket routes
    $routes->post('tickets/(:num)/assign', 'TicketsController::assign/$1');
    $routes->post('tickets/(:num)/close', 'TicketsController::close/$1');
    $routes->post('tickets/(:num)/reopen', 'TicketsController::reopen/$1');

    // Statistics route
    $routes->get('tickets/stats', 'TicketsController::stats');

    // User tickets route
    $routes->get('users/(:num)/tickets', 'TicketsController::userTickets/$1');
});

// Web routes for the main interface
$routes->resource('tickets', ['controller' => 'TicketsController']);
