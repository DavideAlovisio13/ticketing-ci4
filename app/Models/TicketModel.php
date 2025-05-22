<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table      = 'tickets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'subject',
        'description',
        'status',
        'priority',
        'category',
        'assigned_to',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'subject' => [
            'label' => 'Subject',
            'rules' => 'required|min_length[5]|max_length[255]'
        ],
        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty|max_length[2000]'
        ],
        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[open,pending,in_progress,resolved,closed]'
        ],
        'priority' => [
            'label' => 'Priority',
            'rules' => 'required|in_list[low,medium,high,urgent]'
        ],
        'category' => [
            'label' => 'Category',
            'rules' => 'permit_empty|max_length[100]'
        ],
        'assigned_to' => [
            'label' => 'Assigned To',
            'rules' => 'permit_empty|integer'
        ],
        'created_by' => [
            'label' => 'Created By',
            'rules' => 'permit_empty|integer'
        ]
    ];

    protected $validationMessages = [
        'subject' => [
            'required' => 'Subject is required',
            'min_length' => 'Subject must be at least 5 characters long',
            'max_length' => 'Subject cannot exceed 255 characters'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be one of: open, pending, in_progress, resolved, closed'
        ],
        'priority' => [
            'required' => 'Priority is required',
            'in_list' => 'Priority must be one of: low, medium, high, urgent'
        ]
    ];

    protected $skipValidation = false;

    // Constants for status and priority
    public const STATUS_OPEN = 'open';
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /**
     * Get all available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_CLOSED => 'Closed'
        ];
    }

    /**
     * Get all available priorities
     */
    public static function getAvailablePriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent'
        ];
    }

    /**
     * Get tickets with pagination and filters
     */
    public function getTicketsPaginated(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $builder = $this->builder();

        // Apply filters
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

        // Apply sorting
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        $builder->orderBy($sortField, $sortOrder);

        // Get total count for pagination
        $totalCount = $builder->countAllResults(false);

        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $tickets = $builder->limit($perPage, $offset)->get()->getResultArray();

        return [
            'data' => $tickets,
            'total' => $totalCount,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalCount / $perPage)
        ];
    }

    /**
     * Get tickets by status
     */
    public function getTicketsByStatus(string $status): array
    {
        return $this->where('status', $status)->findAll();
    }

    /**
     * Get tickets assigned to user
     */
    public function getTicketsAssignedTo(int $userId): array
    {
        return $this->where('assigned_to', $userId)->findAll();
    }

    /**
     * Get tickets created by user
     */
    public function getTicketsCreatedBy(int $userId): array
    {
        return $this->where('created_by', $userId)->findAll();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $stats = [];

        // Count by status
        foreach (self::getAvailableStatuses() as $status => $label) {
            $stats['by_status'][$status] = $this->where('status', $status)->countAllResults();
        }

        // Count by priority
        foreach (self::getAvailablePriorities() as $priority => $label) {
            $stats['by_priority'][$priority] = $this->where('priority', $priority)->countAllResults();
        }

        // Total tickets
        $stats['total'] = $this->countAll();

        // Recent tickets (last 7 days)
        $stats['recent'] = $this->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->countAllResults();

        return $stats;
    }

    /**
     * Assign ticket to user
     */
    public function assignTicket(int $ticketId, int $userId): bool
    {
        return $this->update($ticketId, [
            'assigned_to' => $userId,
            'status' => self::STATUS_IN_PROGRESS
        ]);
    }

    /**
     * Close ticket
     */
    public function closeTicket(int $ticketId): bool
    {
        return $this->update($ticketId, [
            'status' => self::STATUS_CLOSED
        ]);
    }

    /**
     * Reopen ticket
     */
    public function reopenTicket(int $ticketId): bool
    {
        return $this->update($ticketId, [
            'status' => self::STATUS_OPEN
        ]);
    }
}
