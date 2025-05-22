<?php

namespace App\Exceptions;

use Exception;

class TicketException extends Exception
{
    private int $statusCode;
    
    public function __construct(string $message = '', int $statusCode = 500, Exception $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, 0, $previous);
    }
    
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}