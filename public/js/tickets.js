$(function () {
    const api = '/api/tickets';
    const $tblBody = $('#tickets-table tbody');
    const modal = new bootstrap.Modal('#ticketModal');
    const $form = $('#ticket-form');

    /* ---------- Helpers ---------- */
    const resetForm = () => {
        $form[0].reset();
        $('#ticket-id').val('');
        $('#modal-title').text('New ticket');
    };

    const rowHtml = t => `
    <tr data-id="${t.id}">
      <td>${t.id}</td>
      <td>${t.subject}</td>
      <td><span class="badge bg-${t.status === 'closed' ? 'secondary' : t.status === 'pending' ? 'warning' : 'success'}">${t.status}</span></td>
      <td>${t.created_at}</td>
      <td>
        <button class="btn btn-sm btn-outline-primary btn-edit">Edit</button>
      </td>
    </tr>`;

    /* ---------- Read ---------- */
    const loadTickets = () => {
        $.getJSON(api).done(data => {
            $tblBody.empty().append(data.map(rowHtml));
        });
    };
    loadTickets();

    /* ---------- Create ---------- */
    $('#btn-new').on('click', () => {
        resetForm();
        modal.show();
    });

    /* ---------- Edit (open modal) ---------- */
    $tblBody.on('click', '.btn-edit', function () {
        const $tr = $(this).closest('tr');
        const id = $tr.data('id');
        $.getJSON(`${api}/${id}`).done(t => {
            $('#modal-title').text(`Edit ticket #${id}`);
            $('#ticket-id').val(id);
            $('#ticket-subject').val(t.subject);
            $('#ticket-status').val(t.status);
            modal.show();
        });
    });

    /* ---------- Save (create or update) ---------- */
    $form.on('submit', function (e) {
        e.preventDefault();

        const id = $('#ticket-id').val();
        const payload = {
            subject: $('#ticket-subject').val(),
            status: $('#ticket-status').val()
        };

        const ajax = id
            ? $.ajax({ url: `${api}/${id}`, method: 'PUT', contentType: 'application/json', data: JSON.stringify(payload) })
            : $.ajax({ url: api, method: 'POST', contentType: 'application/json', data: JSON.stringify(payload) });

        ajax.done(() => { modal.hide(); loadTickets(); });
    });
});
