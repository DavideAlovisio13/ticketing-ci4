<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table      = 'tickets';
    protected $primaryKey = 'id';

    // Consente insert/update “mass-assignment” dei soli campi indicati
    protected $allowedFields = ['subject', 'status'];

    // Gestione automatica di created_at / updated_at
    protected $useTimestamps = true;

    protected $returnType = 'array';
}
