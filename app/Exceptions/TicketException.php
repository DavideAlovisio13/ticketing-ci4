<?php

namespace App\Exceptions;

use Exception;

/**
 * --------------------------------------------------------------------
 * Eccezione dominio: TicketException
 * --------------------------------------------------------------------
 * Incapsula un messaggio di errore e un codice HTTP, così da poter
 * propagare informazioni dettagliate dal dominio (Service, Model)
 * fino ai controller API senza dipendere da ResponseInterface.
 */
class TicketException extends Exception
{
    /**
     * Codice HTTP da restituire al client (e.g. 404, 422, 500).
     */
    private int $statusCode;

    /**
     * Costruttore.
     *
     * @param string          $message     Messaggio descrittivo
     * @param int             $statusCode  Codice HTTP (default 500)
     * @param Exception|null  $previous    Eventuale eccezione annidata
     */
    public function __construct(string $message = '', int $statusCode = 500, Exception $previous = null)
    {
        $this->statusCode = $statusCode;
        // Il secondo argomento ($code) di Exception non viene usato: 0
        parent::__construct($message, 0, $previous);
    }

    /**
     * Getter per lo status code (utilizzato dai controller).
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
