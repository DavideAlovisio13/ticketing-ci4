<?php

namespace App\Controllers;

class TicketsPage extends BaseController
{
    public function index()
    {
        return view('tickets');   // carica la view che hai creato
    }
}