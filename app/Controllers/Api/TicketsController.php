<?php

namespace App\Controllers\Api;

use App\Services\TicketService;
use App\Exceptions\TicketException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * --------------------------------------------------------------------
 * TicketsController (REST API)
 * --------------------------------------------------------------------
 * Espone endpoint CRUD e azioni custom (assign/close/reopen) per i
 * ticket di assistenza. Tutte le risposte sono in formato JSON.
 * L’implementazione delega la business logic a TicketService,
 * centralizzando così la gestione di validazioni e eccezioni.
 */
class TicketsController extends ResourceController
{
    /**
     * @var string Formato di risposta predefinito (JSON)
     */
    protected $format = 'json';

    /**
     * Service layer responsabile della logica di dominio.
     */
    protected TicketService $ticketService;

    public function __construct()
    {
        // Iniezione “manuale” del service (si potrebbe usare DI container)
        $this->ticketService = new TicketService();
    }

    /**
     * ----------------------------------------------------------------
     * GET /api/tickets
     * ----------------------------------------------------------------
     * Ritorna elenco paginato di ticket (con possibili filtri).
     * Parametri query es.: page, per_page, status, search ...
     */
    public function index(): ResponseInterface
    {
        try {
            $params = $this->request->getGet();            // Query string
            $result = $this->ticketService->getAllTickets($params);

            return $this->respond([
                'status'     => 'success',
                'data'       => $result['data'],
                'pagination' => [
                    'total'       => $result['total'],
                    'page'        => $result['page'],
                    'per_page'    => $result['per_page'],
                    'total_pages' => $result['total_pages'],
                ],
            ]);
        } catch (TicketException $e) {
            // Errori specifici dominio → fail()
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * GET /api/tickets/{id}
     * ----------------------------------------------------------------
     * Ritorna un singolo ticket in base all’ID.
     */
    public function show($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }

            $ticket = $this->ticketService->getTicketById((int) $id);

            return $this->respond([
                'status' => 'success',
                'data'   => $ticket,
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * POST /api/tickets
     * ----------------------------------------------------------------
     * Crea un nuovo ticket. Accetta payload JSON o form-urlencoded.
     */
    public function create(): ResponseInterface
    {
        try {
            $input = $this->getRequestInput();
            if (empty($input)) {
                return $this->failValidationErrors('No data provided');
            }

            $ticket = $this->ticketService->createTicket($input);

            return $this->respondCreated([
                'status'  => 'success',
                'message' => 'Ticket created successfully',
                'data'    => $ticket,
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * PUT /api/tickets/{id}
     * ----------------------------------------------------------------
     * Aggiorna un ticket esistente.
     */
    public function update($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }

            $input = $this->getRequestInput();
            if (empty($input)) {
                return $this->failValidationErrors('No data provided');
            }

            $ticket = $this->ticketService->updateTicket((int) $id, $input);

            return $this->respond([
                'status'  => 'success',
                'message' => 'Ticket updated successfully',
                'data'    => $ticket,
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * DELETE /api/tickets/{id}
     * ----------------------------------------------------------------
     * Elimina un ticket.
     */
    public function delete($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }

            $this->ticketService->deleteTicket((int) $id);

            return $this->respondDeleted([
                'status'  => 'success',
                'message' => 'Ticket deleted successfully',
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * POST /api/tickets/{id}/assign
     * ----------------------------------------------------------------
     * Assegna un ticket a un utente.
     * Richiede nel body: { "user_id": int }
     */
    public function assign($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }

            $input = $this->getRequestInput();
            if (empty($input['user_id'])) {
                return $this->failValidationErrors('User ID is required');
            }

            $ticket = $this->ticketService->assignTicket((int) $id, (int) $input['user_id']);

            return $this->respond([
                'status'  => 'success',
                'message' => 'Ticket assigned successfully',
                'data'    => $ticket,
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * POST /api/tickets/{id}/close
     * ----------------------------------------------------------------
     * Chiude un ticket (cambia stato).
     */
    public function close($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }

            $ticket = $this->ticketService->closeTicket((int) $id);

            return $this->respond([
                'status'  => 'success',
                'message' => 'Ticket closed successfully',
                'data'    => $ticket,
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * POST /api/tickets/{id}/reopen
     * ----------------------------------------------------------------
     * Riapre un ticket chiuso.
     */
    public function reopen($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }

            $ticket = $this->ticketService->reopenTicket((int) $id);

            return $this->respond([
                'status'  => 'success',
                'message' => 'Ticket reopened successfully',
                'data'    => $ticket,
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * GET /api/tickets/stats
     * ----------------------------------------------------------------
     * Ritorna statistiche aggregate per la dashboard
     * (totale per stato, tempo medio di chiusura, ecc.).
     */
    public function stats(): ResponseInterface
    {
        try {
            $stats = $this->ticketService->getDashboardStats();

            return $this->respond([
                'status' => 'success',
                'data'   => $stats,
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * GET /api/users/{userId}/tickets
     * ----------------------------------------------------------------
     * Ritorna i ticket relativi a un singolo utente.
     * Query param facoltativo `relation` (assigned|created|all).
     */
    public function userTickets($userId = null): ResponseInterface
    {
        try {
            if (!$userId || !is_numeric($userId)) {
                return $this->failValidationErrors('Invalid user ID');
            }

            // Se non specificato → 'assigned'
            $relation = $this->request->getGet('relation') ?? 'assigned';
            $tickets  = $this->ticketService->getTicketsByUser((int) $userId, $relation);

            return $this->respond([
                'status' => 'success',
                'data'   => $tickets,
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * ----------------------------------------------------------------
     * Helper: Ottiene l’input della richiesta (JSON o form-data)
     * ----------------------------------------------------------------
     * Restituisce array vuoto se il body è assente/non valido.
     */
    private function getRequestInput(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');

        // Parsing adattivo in base al Content-Type
        if (strpos($contentType, 'application/json') !== false) {
            $input = $this->request->getJSON(true); // true → array assoc.
        } else {
            $input = $this->request->getPost();     // x-www-form-urlencoded
        }

        return $input ?: [];
    }
}
