/**
 * --------------------------------------------------------------------
 * TicketManager – Front-End Controller (vanilla JS + Bootstrap)
 * --------------------------------------------------------------------
 * Responsabilità:
 *   • Orchestrare tutte le operazioni CRUD e dashboard via API REST.
 *   • Gestire stato locale (paginazione, filtri) e interazioni UI
 *     (modale, toast, loading spinner, ecc.).
 *   • Delegare alla sorgente back-end /api/tickets la persistenza.
 *
 * NOTA BENE: il codice applicativo è intatto; sono stati aggiunti
 * unicamente commenti per documentare logica e best practice.
 */
class TicketManager {
    constructor() {
        // Endpoint REST base
        this.api = '/api/tickets';

        // Stato di paginazione
        this.currentPage = 1;
        this.perPage = 10;

        // Stato filtri attivi
        this.filters = {};

        // Riferimenti DOM / Bootstrap
        this.modal = new bootstrap.Modal('#ticketModal');
        this.form = document.getElementById('ticket-form');
        this.tbody = document.getElementById('tickets-tbody');

        // Bootstrap dell’app
        this.init();
    }

    /* --------------------------------------------------------------
       INIZIALIZZAZIONE
       -------------------------------------------------------------- */
    init() {
        this.bindEvents();        // Event delegation & listeners
        this.loadDashboardStats(); // Statistiche iniziali
        this.loadTickets();       // Prima fetch paginata
    }

    /* Collega gli handler a UI e componenti dinamici */
    bindEvents() {
        // Nuovo ticket (apre modal vuota)
        document.getElementById('btn-new').addEventListener('click', () => {
            this.showCreateModal();
        });

        // Submit modale (create / update)
        this.form.addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });

        // Gestione filtro/ordinamento (debounced)
        const filterElements = ['search', 'filter-status', 'filter-priority', 'sort-by', 'sort-order'];
        filterElements.forEach(id => {
            document.getElementById(id).addEventListener('change', () => {
                this.handleFilterChange();
            });
        });

        // Reset filtri
        document.getElementById('reset-filters').addEventListener('click', () => {
            this.resetFilters();
        });

        // Delegazione azioni della table (edit/close/reopen/delete)
        this.tbody.addEventListener('click', (e) => {
            const action = e.target.dataset.action;
            const ticketId = e.target.closest('tr')?.dataset.id;

            if (action && ticketId) {
                this.handleTableAction(action, parseInt(ticketId), e.target);
            }
        });
    }

    /* --------------------------------------------------------------
       DASHBOARD STATISTICS
       -------------------------------------------------------------- */
    async loadDashboardStats() {
        try {
            const response = await this.makeRequest(`${this.api}/stats`);
            this.updateDashboardStats(response.data);
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    /* Aggiorna i contatori della dashboard */
    updateDashboardStats(stats) {
        document.getElementById('total-tickets').textContent = stats.total;
        document.getElementById('open-tickets').textContent = stats.by_status.open || 0;
        document.getElementById('in-progress-tickets').textContent = stats.by_status.in_progress || 0;
        document.getElementById('closed-tickets').textContent = stats.by_status.closed || 0;

        // Rende visibile la sezione
        document.getElementById('dashboard-stats').style.display = 'flex';
    }

    /* --------------------------------------------------------------
       LISTA TICKETS (fetch + render + pagination)
       -------------------------------------------------------------- */
    async loadTickets() {
        try {
            this.setLoadingState(true);

            // Query string costruita con URLSearchParams
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                ...this.filters,
            });

            const response = await this.makeRequest(`${this.api}?${params}`);
            this.renderTickets(response.data);
            this.renderPagination(response.pagination);

        } catch (error) {
            this.showToast('Error loading tickets', 'error');
            console.error('Error loading tickets:', error);
        } finally {
            this.setLoadingState(false);
        }
    }

    /* Rende la tabella tickets (o placeholder se vuota) */
    renderTickets(tickets) {
        if (!tickets || tickets.length === 0) {
            this.tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No tickets found
                    </td>
                </tr>
            `;
            return;
        }

        this.tbody.innerHTML = tickets.map(ticket => this.createTicketRow(ticket)).join('');
    }

    /* Template HTML per una riga di ticket */
    createTicketRow(ticket) {
        const statusClass = `status-${ticket.status}`;
        const priorityClass = `priority-${ticket.priority}`;
        const createdDate = new Date(ticket.created_at).toLocaleDateString();

        return `
            <tr data-id="${ticket.id}">
                <td><strong>#${ticket.id}</strong></td>
                <td>
                    <div class="fw-bold">${this.escapeHtml(ticket.subject)}</div>
                    ${ticket.description ? `<small class="text-muted">${this.escapeHtml(ticket.description.substring(0, 50))}${ticket.description.length > 50 ? '...' : ''}</small>` : ''}
                </td>
                <td>
                    <span class="badge ${statusClass}">${this.formatStatus(ticket.status)}</span>
                </td>
                <td>
                    <span class="badge ${priorityClass}">
                        <i class="fas fa-flag"></i>
                        ${this.formatPriority(ticket.priority)}
                    </span>
                </td>
                <td>${ticket.category ? this.escapeHtml(ticket.category) : '<span class="text-muted">-</span>'}</td>
                <td><small>${createdDate}</small></td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" data-action="edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${ticket.status !== 'closed' ? `
                            <button class="btn btn-outline-success" data-action="close" title="Close">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : `
                            <button class="btn btn-outline-info" data-action="reopen" title="Reopen">
                                <i class="fas fa-redo"></i>
                            </button>
                        `}
                        <button class="btn btn-outline-danger" data-action="delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    /* Costruisce la paginazione (Prev / numeri / Next) */
    renderPagination(pagination) {
        if (pagination.total_pages <= 1) {
            document.getElementById('pagination-nav').style.display = 'none';
            return;
        }

        const paginationEl = document.getElementById('pagination');
        const pages = [];

        // Previous
        pages.push(`
            <li class="page-item ${pagination.page === 1 ? 'disabled' : ''}">
                <button class="page-link" data-page="${pagination.page - 1}">Previous</button>
            </li>
        `);

        // Numeri pagina con ellissi
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === pagination.page || i === 1 || i === pagination.total_pages ||
                (i >= pagination.page - 2 && i <= pagination.page + 2)) {
                pages.push(`
                    <li class="page-item ${i === pagination.page ? 'active' : ''}">
                        <button class="page-link" data-page="${i}">${i}</button>
                    </li>
                `);
            } else if (i === pagination.page - 3 || i === pagination.page + 3) {
                pages.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
        }

        // Next
        pages.push(`
            <li class="page-item ${pagination.page === pagination.total_pages ? 'disabled' : ''}">
                <button class="page-link" data-page="${pagination.page + 1}">Next</button>
            </li>
        `);

        paginationEl.innerHTML = pages.join('');
        document.getElementById('pagination-nav').style.display = 'block';

        // Delegazione click sui pulsanti pagina
        paginationEl.addEventListener('click', (e) => {
            if (e.target.dataset.page) {
                this.currentPage = parseInt(e.target.dataset.page);
                this.loadTickets();
            }
        });
    }

    /* --------------------------------------------------------------
       AZIONI DA TABELLONE (edit / close / reopen / delete)
       -------------------------------------------------------------- */
    async handleTableAction(action, ticketId, button) {
        const originalHtml = button.innerHTML;

        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            switch (action) {
                case 'edit':
                    await this.showEditModal(ticketId);
                    break;
                case 'close':
                    await this.closeTicket(ticketId);
                    break;
                case 'reopen':
                    await this.reopenTicket(ticketId);
                    break;
                case 'delete':
                    await this.deleteTicket(ticketId);
                    break;
            }
        } catch (error) {
            console.error(`Error performing ${action}:`, error);
        } finally {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    }

    /* Modale di modifica (pre-compila campi) */
    async showEditModal(ticketId) {
        try {
            const response = await this.makeRequest(`${this.api}/${ticketId}`);
            const ticket = response.data;

            document.getElementById('modal-title').textContent = `Edit Ticket #${ticketId}`;
            document.getElementById('ticket-id').value = ticketId;
            document.getElementById('ticket-subject').value = ticket.subject;
            document.getElementById('ticket-description').value = ticket.description || '';
            document.getElementById('ticket-status').value = ticket.status;
            document.getElementById('ticket-priority').value = ticket.priority;
            document.getElementById('ticket-category').value = ticket.category || '';

            this.modal.show();
        } catch (error) {
            this.showToast('Error loading ticket details', 'error');
        }
    }

    /* Modale di creazione (form reset) */
    showCreateModal() {
        this.resetForm();
        document.getElementById('modal-title').textContent = 'New Ticket';
        this.modal.show();
    }

    /* --------------------------------------------------------------
       GESTIONE SUBMIT FORM (create / update)
       -------------------------------------------------------------- */
    async handleFormSubmit(e) {
        e.preventDefault();

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');

        try {
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            // Costruisce oggetto ticket dal form
            const ticketData = {
                subject: document.getElementById('ticket-subject').value,
                description: document.getElementById('ticket-description').value,
                status: document.getElementById('ticket-status').value,
                priority: document.getElementById('ticket-priority').value,
                category: document.getElementById('ticket-category').value,
            };

            const ticketId = document.getElementById('ticket-id').value;

            if (ticketId) {
                await this.updateTicket(ticketId, ticketData);
                this.showToast('Ticket updated successfully', 'success');
            } else {
                await this.createTicket(ticketData);
                this.showToast('Ticket created successfully', 'success');
            }

            this.modal.hide();
            this.loadTickets();
            this.loadDashboardStats();

        } catch (error) {
            this.showToast(error.message || 'Error saving ticket', 'error');
        } finally {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    /* --------------------------------------------------------------
       CRUD WRAPPERS (POST, PUT, POST actions, DELETE)
       -------------------------------------------------------------- */
    async createTicket(data) {
        return this.makeRequest(this.api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
    }

    async updateTicket(id, data) {
        return this.makeRequest(`${this.api}/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
    }

    async closeTicket(id) {
        if (!confirm('Are you sure you want to close this ticket?')) return;

        await this.makeRequest(`${this.api}/${id}/close`, { method: 'POST' });
        this.showToast('Ticket closed successfully', 'success');
        this.loadTickets();
        this.loadDashboardStats();
    }

    async reopenTicket(id) {
        if (!confirm('Are you sure you want to reopen this ticket?')) return;

        await this.makeRequest(`${this.api}/${id}/reopen`, { method: 'POST' });
        this.showToast('Ticket reopened successfully', 'success');
        this.loadTickets();
        this.loadDashboardStats();
    }

    async deleteTicket(id) {
        if (!confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) return;

        await this.makeRequest(`${this.api}/${id}`, { method: 'DELETE' });
        this.showToast('Ticket deleted successfully', 'success');
        this.loadTickets();
        this.loadDashboardStats();
    }

    /* --------------------------------------------------------------
       FILTRI & RICERCA (debounced)
       -------------------------------------------------------------- */
    handleFilterChange() {
        clearTimeout(this.filterTimeout);
        this.filterTimeout = setTimeout(() => {
            this.updateFilters();
            this.currentPage = 1;
            this.loadTickets();
        }, 300);
    }

    /* Aggiorna this.filters rimuovendo chiavi vuote */
    updateFilters() {
        this.filters = {
            search: document.getElementById('search').value,
            status: document.getElementById('filter-status').value,
            priority: document.getElementById('filter-priority').value,
            sort_by: document.getElementById('sort-by').value,
            sort_order: document.getElementById('sort-order').value,
        };

        Object.keys(this.filters).forEach(key => {
            if (!this.filters[key]) delete this.filters[key];
        });
    }

    /* Reset UI + stato filtri */
    resetFilters() {
        document.getElementById('search').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-priority').value = '';
        document.getElementById('sort-by').value = 'created_at';
        document.getElementById('sort-order').value = 'DESC';

        this.filters = {};
        this.currentPage = 1;
        this.loadTickets();
    }

    /* Reset campi form modale */
    resetForm() {
        this.form.reset();
        document.getElementById('ticket-id').value = '';
        document.getElementById('ticket-priority').value = 'medium';
    }

    /* --------------------------------------------------------------
       UTILITIES
       -------------------------------------------------------------- */
    setLoadingState(loading) {
        const table = document.getElementById('tickets-table');
        table.classList.toggle('loading', loading);
    }

    /* Wrapper fetch con JSON + error handling */
    async makeRequest(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                ...options.headers,
            },
            ...options,
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        return data;
    }

    /* Toast Bootstrap 5 evoluto con icone FA */
    showToast(message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container');
        const toastId = 'toast-' + Date.now();

        const bgClass = {
            success: 'bg-success',
            error: 'bg-danger',
            warning: 'bg-warning',
            info: 'bg-info',
        }[type] || 'bg-info';

        const icon = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle',
        }[type] || 'fas fa-info-circle';

        const toastHtml = `
            <div class="toast ${bgClass} text-white" id="${toastId}" role="alert">
                <div class="toast-body d-flex align-items-center">
                    <i class="${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    /* Escape HTML per prevenire XSS in output non-trusted */
    escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    /* Mapping status → label umana */
    formatStatus(status) {
        const statusMap = {
            open: 'Open',
            pending: 'Pending',
            in_progress: 'In Progress',
            resolved: 'Resolved',
            closed: 'Closed',
        };
        return statusMap[status] || status;
    }

    /* Mapping priority → label umana */
    formatPriority(priority) {
        const priorityMap = {
            low: 'Low',
            medium: 'Medium',
            high: 'High',
            urgent: 'Urgent',
        };
        return priorityMap[priority] || priority;
    }
}

/* --------------------------------------------------------------
   Bootstrap dell’applicazione quando il DOM è pronto
   -------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    new TicketManager();
});
