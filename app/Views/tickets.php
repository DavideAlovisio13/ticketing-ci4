<!doctype html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enhanced Tickets CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .priority-low {
            color: #28a745;
        }

        .priority-medium {
            color: #ffc107;
        }

        .priority-high {
            color: #fd7e14;
        }

        .priority-urgent {
            color: #dc3545;
        }

        .status-open {
            background-color: #28a745 !important;
        }

        .status-pending {
            background-color: #ffc107 !important;
        }

        .status-in_progress {
            background-color: #17a2b8 !important;
        }

        .status-resolved {
            background-color: #6f42c1 !important;
        }

        .status-closed {
            background-color: #6c757d !important;
        }

        .card-stats {
            transition: transform 0.2s;
        }

        .card-stats:hover {
            transform: translateY(-2px);
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Toast Container -->
    <div class="toast-container"></div>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="mb-4">
                    <i class="fas fa-ticket-alt"></i>
                    Enhanced Ticket Management System
                </h1>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="row mb-4" id="dashboard-stats" style="display: none;">
            <div class="col-md-3">
                <div class="card card-stats text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Tickets</h5>
                        <h2 class="text-primary" id="total-tickets">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats text-center">
                    <div class="card-body">
                        <h5 class="card-title">Open</h5>
                        <h2 class="text-success" id="open-tickets">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats text-center">
                    <div class="card-body">
                        <h5 class="card-title">In Progress</h5>
                        <h2 class="text-info" id="in-progress-tickets">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats text-center">
                    <div class="card-body">
                        <h5 class="card-title">Closed</h5>
                        <h2 class="text-secondary" id="closed-tickets">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter"></i>
                    Filters & Search
                </h5>
            </div>
            <div class="card-body">
                <form id="filters-form">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" placeholder="Search tickets...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filter-status">
                                <option value="">All Status</option>
                                <option value="open">Open</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="filter-priority">
                                <option value="">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sort By</label>
                            <select class="form-select" id="sort-by">
                                <option value="created_at">Created Date</option>
                                <option value="updated_at">Updated Date</option>
                                <option value="subject">Subject</option>
                                <option value="priority">Priority</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Order</label>
                            <select class="form-select" id="sort-order">
                                <option value="DESC">Newest First</option>
                                <option value="ASC">Oldest First</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-outline-secondary w-100" id="reset-filters">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i>
                    Tickets
                </h5>
                <button class="btn btn-primary" id="btn-new">
                    <i class="fas fa-plus"></i>
                    New Ticket
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="tickets-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Category</th>
                                <th>Created</th>
                                <th style="width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tickets-tbody">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Tickets pagination" id="pagination-nav" style="display: none;">
                    <ul class="pagination justify-content-center" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Ticket Modal -->
    <div class="modal fade" id="ticketModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="ticket-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">New Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="ticket-id">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Subject *</label>
                            <input class="form-control" id="ticket-subject" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="ticket-description" rows="4" placeholder="Describe the issue or request..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="ticket-status">
                                <option value="open">Open</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="ticket-priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <input class="form-control" id="ticket-category" placeholder="e.g., Bug, Feature Request">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Save Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/tickets-enhanced.js') ?>"></script>
</body>

</html>