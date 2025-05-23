<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * --------------------------------------------------------------------
 * TicketModel
 * --------------------------------------------------------------------
 * Gestisce la persistenza e le query di dominio per i ticket.
 * - Mapping campi ↔︎ DB (`$allowedFields`)
 * - Regole di validazione lato server
 * - Helper per statistiche e azioni di workflow
 *
 * Tutto il codice applicativo resta invariato: sono stati aggiunti solo
 * commenti in stile “programmatore esperto”.
 */
class TicketModel extends Model
{
    // -----------------------------------------------------------------
    // Configurazione base del Model
    // -----------------------------------------------------------------
    protected $table      = 'tickets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Campi consentiti nel mass-assignment
    protected $allowedFields = [
        'subject',
        'description',
        'status',
        'priority',
        'category',
        'assigned_to',
        'created_by',
    ];

    // Timestamp automatici gestiti da CI
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // -----------------------------------------------------------------
    // Validazione: regole e messaggi custom
    // -----------------------------------------------------------------
    protected $validationRules = [
        'subject' => [
            'label' => 'Subject',
            'rules' => 'required|min_length[5]|max_length[255]',
        ],
        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty|max_length[2000]',
        ],
        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[open,pending,in_progress,resolved,closed]',
        ],
        'priority' => [
            'label' => 'Priority',
            'rules' => 'required|in_list[low,medium,high,urgent]',
        ],
        'category' => [
            'label' => 'Category',
            'rules' => 'permit_empty|max_length[100]',
        ],
        'assigned_to' => [
            'label' => 'Assigned To',
            'rules' => 'permit_empty|integer',
        ],
        'created_by' => [
            'label' => 'Created By',
            'rules' => 'permit_empty|integer',
        ],
    ];

    protected $validationMessages = [
        'subject' => [
            'required'   => 'Subject is required',
            'min_length' => 'Subject must be at least 5 characters long',
            'max_length' => 'Subject cannot exceed 255 characters',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list'  => 'Status must be one of: open, pending, in_progress, resolved, closed',
        ],
        'priority' => [
            'required' => 'Priority is required',
            'in_list'  => 'Priority must be one of: low, medium, high, urgent',
        ],
    ];

    protected $skipValidation = false;

    // -----------------------------------------------------------------
    // Costanti di dominio
    // -----------------------------------------------------------------
    public const STATUS_OPEN        = 'open';
    public const STATUS_PENDING     = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED    = 'resolved';
    public const STATUS_CLOSED      = 'closed';

    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';

    // -----------------------------------------------------------------
    // Getter statici per dropdown / mapping UI
    // -----------------------------------------------------------------
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_OPEN        => 'Open',
            self::STATUS_PENDING     => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED    => 'Resolved',
            self::STATUS_CLOSED      => 'Closed',
        ];
    }

    public static function getAvailablePriorities(): array
    {
        return [
            self::PRIORITY_LOW    => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH   => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    // -----------------------------------------------------------------
    // Query helper: paginazione + filtri dinamici
    // -----------------------------------------------------------------
    public function getTicketsPaginated(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $builder = $this->builder();

        // Filtri condizionali
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $builder->where('priority', $filters['priority']);
        }
        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('subject', $filters['search'])
                ->orLike('description', $filters['search'])
                ->groupEnd();
        }
        if (!empty($filters['assigned_to'])) {
            $builder->where('assigned_to', $filters['assigned_to']);
        }

        // Ordinamento
        $sortField = $filters['sort_by']   ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        $builder->orderBy($sortField, $sortOrder);

        // Conteggio totale (senza reset builder)
        $totalCount = $builder->countAllResults(false);

        // Paginazione
        $offset  = ($page - 1) * $perPage;
        $tickets = $builder->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'data'        => $tickets,
            'total'       => $totalCount,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($totalCount / $perPage),
        ];
    }

    // Query helper rapidi
    public function getTicketsByStatus(string $status): array
    {
        return $this->where('status', $status)->findAll();
    }

    public function getTicketsAssignedTo(int $userId): array
    {
        return $this->where('assigned_to', $userId)->findAll();
    }

    public function getTicketsCreatedBy(int $userId): array
    {
        return $this->where('created_by', $userId)->findAll();
    }

    // -----------------------------------------------------------------
    // Statistiche aggregate per dashboard
    // -----------------------------------------------------------------
    public function getDashboardStats(): array
    {
        $stats = [];

        // Conteggio per status
        foreach (self::getAvailableStatuses() as $status => $label) {
            $stats['by_status'][$status] = $this->where('status', $status)->countAllResults();
        }

        // Conteggio per priority
        foreach (self::getAvailablePriorities() as $priority => $label) {
            $stats['by_priority'][$priority] = $this->where('priority', $priority)->countAllResults();
        }

        // Totale complessivo
        $stats['total'] = $this->countAll();

        // Ticket creati negli ultimi 7 giorni
        $stats['recent'] = $this->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->countAllResults();

        return $stats;
    }

    // -----------------------------------------------------------------
    // Mutator helper: modifica stato workflow
    // -----------------------------------------------------------------
    public function assignTicket(int $ticketId, int $userId): bool
    {
        return $this->update($ticketId, [
            'assigned_to' => $userId,
            'status'      => self::STATUS_IN_PROGRESS,
        ]);
    }

    public function closeTicket(int $ticketId): bool
    {
        return $this->update($ticketId, [
            'status' => self::STATUS_CLOSED,
        ]);
    }

    public function reopenTicket(int $ticketId): bool
    {
        return $this->update($ticketId, [
            'status' => self::STATUS_OPEN,
        ]);
    }
}
