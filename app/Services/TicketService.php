<?php

namespace App\Services;

use App\Models\TicketModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Exceptions\TicketException;

/**
 * --------------------------------------------------------------------
 * TicketService
 * --------------------------------------------------------------------
 * Layer di servizio che incapsula la logica di business sui ticket:
 * - orchestration tra Model e controller
 * - gestione eccezioni dominio → TicketException
 * - logging centralizzato
 *
 * NB: tutti i commenti sono stati aggiunti ora; il codice eseguibile
 * resta identico a quello fornito.
 */
class TicketService
{
    /**
     * Model responsabile dell’accesso dati.
     */
    protected TicketModel $ticketModel;

    public function __construct()
    {
        // Iniezione “manuale”; in progetti più grandi si può usare DI
        $this->ticketModel = new TicketModel();
    }

    /**
     * ----------------------------------------------------------------
     * Get all tickets with pagination and filters
     * ----------------------------------------------------------------
     * - Converte parametri query in filtri e paginazione
     * - Delega al Model per l’esecuzione della query
     * - Gestisce eventuali eccezioni e logging
     */
    public function getAllTickets(array $params = []): array
    {
        try {
            $page    = (int) ($params['page']     ?? 1);
            $perPage = (int) ($params['per_page'] ?? 10);
            $filters = $this->buildFilters($params);

            return $this->ticketModel->getTicketsPaginated($filters, $page, $perPage);
        } catch (\Exception $e) {
            log_message('error', 'Error getting tickets: ' . $e->getMessage());
            throw new TicketException('Unable to fetch tickets');
        }
    }

    /**
     * Get single ticket by ID
     */
    public function getTicketById(int $id): ?array
    {
        try {
            $ticket = $this->ticketModel->find($id);

            if (!$ticket) {
                // Restituisce un 404 specifico
                throw new TicketException('Ticket not found', 404);
            }

            return $ticket;
        } catch (TicketException $e) {
            // Propaga eccezioni dominio senza alterarle
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Error getting ticket by ID: ' . $e->getMessage());
            throw new TicketException('Unable to fetch ticket');
        }
    }

    /**
     * Create new ticket
     */
    public function createTicket(array $data): array
    {
        try {
            // Default applicativi se mancanti
            $data['status']   = $data['status']   ?? TicketModel::STATUS_OPEN;
            $data['priority'] = $data['priority'] ?? TicketModel::PRIORITY_MEDIUM;

            // Inserimento + validazione Model
            if (!$this->ticketModel->insert($data)) {
                $errors = $this->ticketModel->errors();
                throw new TicketException('Validation failed: ' . implode(', ', $errors), 422);
            }

            $ticketId = $this->ticketModel->getInsertID();
            $ticket   = $this->ticketModel->find($ticketId);

            log_message('info', "Ticket created: ID {$ticketId}");

            return $ticket;
        } catch (TicketException $e) {
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Error creating ticket: ' . $e->getMessage());
            throw new TicketException('Unable to create ticket');
        }
    }

    /**
     * Update ticket
     */
    public function updateTicket(int $id, array $data): array
    {
        try {
            // Verifica esistenza
            $existingTicket = $this->getTicketById($id);

            // Cleanup di campi non aggiornabili
            unset($data['id'], $data['created_at'], $data['updated_at']);

            if (!$this->ticketModel->update($id, $data)) {
                $errors = $this->ticketModel->errors();
                throw new TicketException('Validation failed: ' . implode(', ', $errors), 422);
            }

            $updatedTicket = $this->ticketModel->find($id);

            log_message('info', "Ticket updated: ID {$id}");

            return $updatedTicket;
        } catch (TicketException $e) {
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Error updating ticket: ' . $e->getMessage());
            throw new TicketException('Unable to update ticket');
        }
    }

    /**
     * Delete ticket
     */
    public function deleteTicket(int $id): bool
    {
        try {
            // Throws 404 se non esiste
            $this->getTicketById($id);

            if (!$this->ticketModel->delete($id)) {
                throw new TicketException('Unable to delete ticket');
            }

            log_message('info', "Ticket deleted: ID {$id}");

            return true;
        } catch (TicketException $e) {
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Error deleting ticket: ' . $e->getMessage());
            throw new TicketException('Unable to delete ticket');
        }
    }

    /**
     * Assign ticket to user
     */
    public function assignTicket(int $ticketId, int $userId): array
    {
        try {
            $this->getTicketById($ticketId);

            if (!$this->ticketModel->assignTicket($ticketId, $userId)) {
                throw new TicketException('Unable to assign ticket');
            }

            $updatedTicket = $this->ticketModel->find($ticketId);

            log_message('info', "Ticket {$ticketId} assigned to user {$userId}");

            return $updatedTicket;
        } catch (TicketException $e) {
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Error assigning ticket: ' . $e->getMessage());
            throw new TicketException('Unable to assign ticket');
        }
    }

    /**
     * Close ticket
     */
    public function closeTicket(int $ticketId): array
    {
        try {
            $ticket = $this->getTicketById($ticketId);

            if ($ticket['status'] === TicketModel::STATUS_CLOSED) {
                throw new TicketException('Ticket is already closed', 400);
            }

            if (!$this->ticketModel->closeTicket($ticketId)) {
                throw new TicketException('Unable to close ticket');
            }

            $updatedTicket = $this->ticketModel->find($ticketId);

            log_message('info', "Ticket closed: ID {$ticketId}");

            return $updatedTicket;
        } catch (TicketException $e) {
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Error closing ticket: ' . $e->getMessage());
            throw new TicketException('Unable to close ticket');
        }
    }

    /**
     * Reopen ticket
     */
    public function reopenTicket(int $ticketId): array
    {
        try {
            $ticket = $this->getTicketById($ticketId);

            if ($ticket['status'] !== TicketModel::STATUS_CLOSED) {
                throw new TicketException('Only closed tickets can be reopened', 400);
            }

            if (!$this->ticketModel->reopenTicket($ticketId)) {
                throw new TicketException('Unable to reopen ticket');
            }

            $updatedTicket = $this->ticketModel->find($ticketId);

            log_message('info', "Ticket reopened: ID {$ticketId}");

            return $updatedTicket;
        } catch (TicketException $e) {
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Error reopening ticket: ' . $e->getMessage());
            throw new TicketException('Unable to reopen ticket');
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        try {
            return $this->ticketModel->getDashboardStats();
        } catch (\Exception $e) {
            log_message('error', 'Error getting dashboard stats: ' . $e->getMessage());
            throw new TicketException('Unable to fetch dashboard statistics');
        }
    }

    /**
     * Get tickets by user
     */
    public function getTicketsByUser(int $userId, string $relation = 'assigned'): array
    {
        try {
            if ($relation === 'assigned') {
                return $this->ticketModel->getTicketsAssignedTo($userId);
            } elseif ($relation === 'created') {
                return $this->ticketModel->getTicketsCreatedBy($userId);
            } else {
                throw new TicketException('Invalid relation type', 400);
            }
        } catch (TicketException $e) {
            throw $e;
        } catch (\Exception $e) {
            log_message('error', 'Error getting tickets by user: ' . $e->getMessage());
            throw new TicketException('Unable to fetch user tickets');
        }
    }

    /**
     * ----------------------------------------------------------------
     * Build filters array from request parameters
     * ----------------------------------------------------------------
     * Traduce la query string in un array usabile dal Model.
     * Non effettua validazioni approfondite: la logica è delegata
     * ai layer superiori (controller/validator) se necessario.
     */
    private function buildFilters(array $params): array
    {
        $filters = [];

        if (!empty($params['status'])) {
            $filters['status'] = $params['status'];
        }
        if (!empty($params['priority'])) {
            $filters['priority'] = $params['priority'];
        }
        if (!empty($params['category'])) {
            $filters['category'] = $params['category'];
        }
        if (!empty($params['search'])) {
            $filters['search'] = $params['search'];
        }
        if (!empty($params['assigned_to'])) {
            $filters['assigned_to'] = (int) $params['assigned_to'];
        }
        if (!empty($params['sort_by'])) {
            $filters['sort_by'] = $params['sort_by'];
        }
        if (!empty($params['sort_order'])) {
            $filters['sort_order'] = strtoupper($params['sort_order']);
        }

        return $filters;
    }
}
