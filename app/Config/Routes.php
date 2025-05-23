<?php

use CodeIgniter\Router\RouteCollection;

/**
 * ------------------------------------------------------------------
 * Definizione delle rotte dell’applicazione
 * ------------------------------------------------------------------
 *
 * @var RouteCollection $routes  Iniettata da CodeIgniter al boot.
 */

// ---------------------------------------------------------------
// Pagina principale (frontend SPA / landing page)
// ---------------------------------------------------------------
$routes->get('/', 'TicketsPage::index');

// ---------------------------------------------------------------
// Gruppo di rotte per l’API RESTful
// prefisso /api e namespace specifico dei controller API
// ---------------------------------------------------------------
$routes->group(
    'api',
    ['namespace' => 'App\Controllers\Api'],
    static function ($routes) {

        // ---------------------------
        // Rotte RESTful “resource”
        //   GET    /api/tickets
        //   POST   /api/tickets
        //   GET    /api/tickets/{id}
        //   PUT    /api/tickets/{id}
        //   PATCH  /api/tickets/{id}
        //   DELETE /api/tickets/{id}
        // ---------------------------
        $routes->resource('tickets', ['controller' => 'TicketsController']);

        // ---------------------------
        // Rotte aggiuntive legate ai ticket
        // ---------------------------
        $routes->post('tickets/(:num)/assign', 'TicketsController::assign/$1');  // Assegna ticket a un utente
        $routes->post('tickets/(:num)/close',  'TicketsController::close/$1');   // Chiude ticket
        $routes->post('tickets/(:num)/reopen', 'TicketsController::reopen/$1');  // Riapre ticket

        // Report statistici (p. es. count per stato, tempo medio ecc.)
        $routes->get('tickets/stats', 'TicketsController::stats');

        // Lista ticket per specifico utente
        $routes->get('users/(:num)/tickets', 'TicketsController::userTickets/$1');
    }
);

// ---------------------------------------------------------------
// Rotte web (interfaccia principale, non API)
// Sovrapposte allo stesso controller in namespace App\Controllers
// ---------------------------------------------------------------
$routes->resource('tickets', ['controller' => 'TicketsController']);
