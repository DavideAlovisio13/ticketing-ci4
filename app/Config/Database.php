<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * --------------------------------------------------------------------
 * Database Configuration
 * --------------------------------------------------------------------
 * Questo file centralizza tutte le impostazioni delle connessioni
 * al database per CodeIgniter 4.  
 * **NOTA BENE:** sono stati aggiunti esclusivamente commenti e
 * docblock; la logica del codice resta identica all’originale.
 */

class Database extends Config
{
    /**
     * Percorso assoluto dove si trovano Migration e Seed.
     * APPPATH punta alla directory `app/`; DIRECTORY_SEPARATOR
     * garantisce la portabilità tra sistemi operativi.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Gruppo di connessione predefinito usato quando non se ne
     * specifica uno in `Database::connect()`.
     */
    public string $defaultGroup = 'default';

    /**
     * -----------------------------------------------------------------
     * Connessione di default (MySQL/MariaDB via driver MySQLi)
     * -----------------------------------------------------------------
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',                // DSN completo (p. es. "mysql:host=localhost;dbname=ci4")
        'hostname'     => 'localhost',       // Host o path del socket
        'username'     => '',                // Nome utente DB
        'password'     => '',                // Password DB
        'database'     => '',                // Schema o nome del database
        'DBDriver'     => 'MySQLi',          // Driver CodeIgniter (MySQLi|PDO ecc.)
        'DBPrefix'     => '',                // Prefisso tabelle (opzionale)
        'pConnect'     => false,             // Connessione persistente?
        'DBDebug'      => true,              // Mostra errori (disattivare in prod.)
        'charset'      => 'utf8mb4',         // Set di caratteri
        'DBCollat'     => 'utf8mb4_general_ci', // Collation
        'swapPre'      => '',                // Swap prefissi (per CLI)
        'encrypt'      => false,             // Abilita SSL/TLS
        'compress'     => false,             // Compressione protocollo MySQL
        'strictOn'     => false,             // Modalità STRICT
        'failover'     => [],                // Connessioni di riserva
        'port'         => 3306,              // Porta MySQL
        'numberNative' => false,             // Tipi numerici nativi PHP?
        'foundRows'    => false,             // Abilita SQL_CALC_FOUND_ROWS
        'dateFormat'   => [
            'date'     => 'Y-m-d',           // Formato DATE
            'datetime' => 'Y-m-d H:i:s',     // Formato DATETIME/TIMESTAMP
            'time'     => 'H:i:s',           // Formato TIME
        ],
    ];

    //
    // -----------------------------------------------------------------
    // Configurazioni di esempio per altri motori
    // -----------------------------------------------------------------
    // Rimangono commentate per non interferire con l’esecuzione;
    // copiarle e personalizzarle quando necessario.
    //

    //    /**
    //     * Connessione di esempio per SQLite3
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'database'    => 'database.db',
    //        'DBDriver'    => 'SQLite3',
    //        'DBPrefix'    => '',
    //        'DBDebug'     => true,
    //        'swapPre'     => '',
    //        'failover'    => [],
    //        'foreignKeys' => true,
    //        'busyTimeout' => 1000,
    //        'synchronous' => null,
    //        'dateFormat'  => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Connessione di esempio per PostgreSQL
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'public',
    //        'DBDriver'   => 'Postgre',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'port'       => 5432,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Connessione di esempio per SQL Server
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'dbo',
    //        'DBDriver'   => 'SQLSRV',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'encrypt'    => false,
    //        'failover'   => [],
    //        'port'       => 1433,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Connessione di esempio per Oracle OCI8
    //     *
    //     * Variabili ambiente utili:
    //     *   NLS_LANG                = 'AMERICAN_AMERICA.UTF8'
    //     *   NLS_DATE_FORMAT         = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_FORMAT    = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS'
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => 'localhost:1521/XEPDB1',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'DBDriver'   => 'OCI8',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'AL32UTF8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    /**
     * -----------------------------------------------------------------
     * Gruppo usato per i test unitari (SQLite in-memory)
     * -----------------------------------------------------------------
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1', // Ignorato da SQLite
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',  // DB effimero per PHPUnit
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',       // Testa la gestione dei prefissi
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /**
     * Impedisce di sovrascrivere dati reali durante i test:
     * se l’ambiente è "testing", forza l’uso del gruppo "tests".
     */
    public function __construct()
    {
        parent::__construct();

        // Protegge il DB di produzione durante l’automated testing
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
