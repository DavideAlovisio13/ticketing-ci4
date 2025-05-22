<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'TicketsPage::index');   // nuova homepage
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) { $routes->resource('tickets', ['controller' => 'TicketsController']); });
$routes->resource('tickets', ['controller' => 'TicketsController']);