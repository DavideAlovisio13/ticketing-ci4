<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

/**
 * --------------------------------------------------------------------
 * Filtro globale dell’applicazione
 * --------------------------------------------------------------------
 * Questa classe estende la configurazione dei filtri di CodeIgniter 4
 * permettendo di definire alias, filtri obbligatori, globali e per
 * metodo/URI.  
 * **NB:** sono stati aggiunti solo commenti; la logica originale resta
 * invariata.
 */
class Filters extends BaseFilters
{
    /**
     * -----------------------------------------------------------------
     * Alias dei filtri
     * -----------------------------------------------------------------
     * Mappano un nome leggibile (key) al fully-qualified class name
     * del filtro. Usare l’alias nei gruppi facilita la lettura.
     *
     * @var array<string, class-string|list<class-string>>
     */
    public array $aliases = [
        'csrf'          => CSRF::class,          // Protezione CSRF
        'toolbar'       => DebugToolbar::class,  // Toolbar di debug
        'honeypot'      => Honeypot::class,      // Campo honeypot anti-bot
        'invalidchars'  => InvalidChars::class,  // Rileva caratteri non validi
        'secureheaders' => SecureHeaders::class, // Security headers
        'cors'          => Cors::class,          // Gestione CORS
        'forcehttps'    => ForceHTTPS::class,    // Reindirizza a HTTPS
        'pagecache'     => PageCache::class,     // Cache delle pagine
        'performance'   => PerformanceMetrics::class, // Metriche prestazioni
    ];

    /**
     * -----------------------------------------------------------------
     * Filtri richiesti (sempre eseguiti)
     * -----------------------------------------------------------------
     * Vengono applicati prima/after di tutti gli altri, anche se la
     * route non esiste. Rimuoverli disabilita funzioni chiave del
     * framework.
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps', // Forza HTTPS globalmente
            'pagecache',  // Cache delle pagine
        ],
        'after' => [
            'pagecache',   // Scrive la cache dopo la risposta
            'performance', // Registra metriche prestazionali
            'toolbar',     // Aggiunge Debug Toolbar
        ],
    ];

    /**
     * -----------------------------------------------------------------
     * Filtri globali opzionali
     * -----------------------------------------------------------------
     * Applicati a ogni richiesta HTTP (se abilitati).
     *
     * @var array<string, array<string, array<string, string>>>|array<string, list<string>>
     */
    public array $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
            // 'invalidchars',
        ],
        'after' => [
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * -----------------------------------------------------------------
     * Filtri per metodo HTTP
     * -----------------------------------------------------------------
     * Consentono di applicare filtri a GET, POST, ecc.  
     * Attenzione: usare con auto-routing disattivato.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * -----------------------------------------------------------------
     * Filtri per pattern URI
     * -----------------------------------------------------------------
     * Esecuzione condizionale in base al path richiesto.
     *
     * Esempio:
     *   'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [];
}
