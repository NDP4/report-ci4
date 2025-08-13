<?= $this->extend('layout/main-base'); ?>

<?= $this->section('content'); ?>

<!-- <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fas fa-ticket-alt me-2"></i>Service Ticket Dashboard</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="<?= base_url('import') ?>"><i class="fas fa-upload me-1"></i>Import Data</a>
            <a class="nav-link" href="<?= base_url('auth/logout') ?>"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
        </div>
    </div>
</nav> -->

<div class="container-fluid mt-4">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-dark bg-white stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Tickets</h5>
                            <h2 class="mb-0"><?= number_format($totalRecords) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-ticket-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark bg-white stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Closed Tickets</h5>
                            <h2 class="mb-0"><?= number_format($totalClosed) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark bg-white stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Open Tickets</h5>
                            <h2 class="mb-0"><?= number_format($totalOpen) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Service Tickets Data</h5>
            <div>
                <!-- import ticket hanya untuk admin -->
                <?php if (session()->get('role') === 'admin'): ?>
                    <a class=" btn btn-primary btn-sm" href="<?= base_url('dashboard/import') ?>"><i class="fas fa-upload me-1"></i>Import Data</a>
                <?php endif; ?>
                <button type="button" class="btn btn-success btn-sm" id="exportBtn">
                    <i class="fas fa-download me-1"></i>Export Excel
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="$('#ticketsTable').DataTable().ajax.reload();">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ticketsTable" class="table table-striped table-hover w-100">
                    <!-- <table id="ticketsTable" class="table table-striped table-bordered"> -->
                    <thead class="table-light">
                        <tr>
                            <th>Ticket ID</th>
                            <th>Subject</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Witel</th>
                            <th>Main Category</th>
                            <th>Category</th>
                            <th>Date Created</th>
                            <th>Date Closed</th>
                            <th>Created By</th>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                        -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Processing...</p>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="<?php echo base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>



<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#ticketsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '<?= base_url('dashboard/getDataTables') ?>',
                type: 'POST',
                data: function(d) {
                    d.searchable_columns = ['ticket_id', 'subject', 'customer_name', 'ticket_status_name', 'priority_name',
                        'witel', 'main_category', 'category', 'date_created', 'date_close', 'created_by_name'
                    ];
                    // d.search_value = d.search.value;
                    return d;
                },
                error: function(xhr, error, thrown) {
                    console.log('Ajax error:', error);
                    alert('Error loading data. Please try again.');
                }
            },
            columns: [{
                    data: 'ticket_id',
                    name: 'ticket_id',
                    searchable: true
                },
                {
                    data: 'subject',
                    name: 'subject',
                    searchable: true
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    searchable: true
                },
                {
                    data: 'ticket_status_name',
                    name: 'ticket_status_name',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'priority_name',
                    name: 'priority_name',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'witel',
                    name: 'witel',
                    searchable: true
                },
                {
                    data: 'main_category',
                    name: 'main_category',
                    searchable: true
                },
                {
                    data: 'category',
                    name: 'category',
                    searchable: true
                },
                {
                    data: 'date_created',
                    name: 'date_created',
                    searchable: true
                },
                {
                    data: 'date_close',
                    name: 'date_close',
                    searchable: true
                },
                {
                    data: 'created_by_name',
                    name: 'created_by_name',
                    searchable: true
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [0, 'asc']
            ],
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: "No data available",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                lengthMenu: "Show _MENU_ entries",
                search: "Search:",
                zeroRecords: "No matching records found"
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });

        // Export functionality
        $('#exportBtn').on('click', function() {
            var searchValue = table.search();
            $('#loadingModal').modal('show');

            window.location.href = '<?= base_url('dashboard/export') ?>?search=' + encodeURIComponent(searchValue);

            setTimeout(function() {
                $('#loadingModal').modal('hide');
            }, 3000);
        });

        // Auto refresh every 5 minutes
        setInterval(function() {
            table.ajax.reload(null, false);
        }, 300000);

        // Enhanced search functionality
        $('.dataTables_filter input').unbind().bind('keyup', function() {
            var searchValue = $(this).val();
            table.search(searchValue).draw();
        });
    });
</script>


<?= $this->endSection(); ?>