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


<?= $this->endSection(); ?>