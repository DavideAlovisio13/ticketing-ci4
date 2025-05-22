<?php

namespace App\Controllers\Api;

use App\Services\TicketService;
use App\Exceptions\TicketException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TicketsController extends ResourceController
{
    protected $format = 'json';
    protected TicketService $ticketService;
    
    public function __construct()
    {
        $this->ticketService = new TicketService();
    }
    
    /**
     * GET /api/tickets
     * Get all tickets with pagination and filters
     */
    public function index(): ResponseInterface
    {
        try {
            $params = $this->request->getGet();
            $result = $this->ticketService->getAllTickets($params);
            
            return $this->respond([
                'status' => 'success',
                'data' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'per_page' => $result['per_page'],
                    'total_pages' => $result['total_pages']
                ]
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * GET /api/tickets/{id}
     * Get specific ticket
     */
    public function show($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }
            
            $ticket = $this->ticketService->getTicketById((int)$id);
            
            return $this->respond([
                'status' => 'success',
                'data' => $ticket
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * POST /api/tickets
     * Create new ticket
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
                'status' => 'success',
                'message' => 'Ticket created successfully',
                'data' => $ticket
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * PUT /api/tickets/{id}
     * Update existing ticket
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
            
            $ticket = $this->ticketService->updateTicket((int)$id, $input);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Ticket updated successfully',
                'data' => $ticket
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * DELETE /api/tickets/{id}
     * Delete ticket
     */
    public function delete($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }
            
            $this->ticketService->deleteTicket((int)$id);
            
            return $this->respondDeleted([
                'status' => 'success',
                'message' => 'Ticket deleted successfully'
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * POST /api/tickets/{id}/assign
     * Assign ticket to user
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
            
            $ticket = $this->ticketService->assignTicket((int)$id, (int)$input['user_id']);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Ticket assigned successfully',
                'data' => $ticket
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * POST /api/tickets/{id}/close
     * Close ticket
     */
    public function close($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }
            
            $ticket = $this->ticketService->closeTicket((int)$id);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Ticket closed successfully',
                'data' => $ticket
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * POST /api/tickets/{id}/reopen
     * Reopen ticket
     */
    public function reopen($id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationErrors('Invalid ticket ID');
            }
            
            $ticket = $this->ticketService->reopenTicket((int)$id);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Ticket reopened successfully',
                'data' => $ticket
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * GET /api/tickets/stats
     * Get dashboard statistics
     */
    public function stats(): ResponseInterface
    {
        try {
            $stats = $this->ticketService->getDashboardStats();
            
            return $this->respond([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * GET /api/users/{userId}/tickets
     * Get tickets by user
     */
    public function userTickets($userId = null): ResponseInterface
    {
        try {
            if (!$userId || !is_numeric($userId)) {
                return $this->failValidationErrors('Invalid user ID');
            }
            
            $relation = $this->request->getGet('relation') ?? 'assigned';
            $tickets = $this->ticketService->getTicketsByUser((int)$userId, $relation);
            
            return $this->respond([
                'status' => 'success',
                'data' => $tickets
            ]);
        } catch (TicketException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode());
        }
    }
    
    /**
     * Helper method to get request input with proper validation
     */
    private function getRequestInput(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = $this->request->getJSON(true);
        } else {
            $input = $this->request->getPost();
        }
        
        return $input ?: [];
    }
}