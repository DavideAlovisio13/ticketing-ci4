<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table      = 'tickets';
    protected $primaryKey = 'id';

    protected $allowedFields = ['subject', 'status'];

    protected $useTimestamps = true;
    protected $returnType    = 'array';
}
