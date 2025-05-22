<!doctype html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <title>Tickets CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">
        <h1 class="mb-4">Ticket list</h1>

        <!-- TABLE -->
        <table class="table table-striped align-middle" id="tickets-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Created&nbsp;at</th>
                    <th style="width:170px;">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- CREATE button -->
        <button class="btn btn-primary" id="btn-new">New ticket</button>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="ticketModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="ticket-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">New ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="ticket-id">
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input class="form-control" id="ticket-subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="ticket-status">
                            <option value="open">open</option>
                            <option value="pending">pending</option>
                            <option value="closed">closed</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/tickets.js') ?>"></script>
</body>

</html>