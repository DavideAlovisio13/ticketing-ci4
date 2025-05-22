<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TicketsController extends ResourceController
{
    protected $modelName = \App\Models\TicketModel::class;
    protected $format    = 'json';

    // GET /api/tickets
    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    // GET /api/tickets/{id}
    public function show($id = null)
    {
        $data = $this->model->find($id);
        return $data
            ? $this->respond($data)
            : $this->failNotFound('Ticket not found');
    }

    // POST /api/tickets
    public function create()
    {
        $input = $this->request->getJSON(true);   // array associativo

        if (!$this->model->insert($input))
            return $this->failValidationErrors($this->model->errors());

        $input['id'] = $this->model->getInsertID();
        return $this->respondCreated($input);
    }

    // PUT /api/tickets/{id}
    public function update($id = null)
    {
        $input = $this->request->getJSON(true);

        if (!$this->model->update($id, $input))
            return $this->failValidationErrors($this->model->errors());

        return $this->respond($this->model->find($id));
    }
}
