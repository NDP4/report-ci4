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
                <button type="button" class="btn btn-primary btn-sm" id="refreshBtn">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Search and Filter Controls -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="<?= current_url() ?>" id="searchForm">
                        <div class="input-group input-group-sm flex-nowrap" style="max-width:400px;">
                            <input type="text" class="form-control w-75 h-50" name="search" value="<?= esc($search ?? '') ?>"
                                placeholder="Search tickets..." id="searchInput">

                            <button class="btn btn-outline-secondary w-auto ms-1" type="submit" title="Search">
                                <i class="fas fa-search"></i>
                            </button>

                            <?php if (!empty($search)): ?>
                                <a href="<?= current_url() ?>" class="btn btn-outline-secondary w-auto ms-1" title="Clear">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>


                        <!-- Hidden inputs to preserve other parameters -->
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="per_page" value="<?= $perPage ?>">
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center">
                        <label class="me-2">Show:</label>
                        <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                        <span class="ms-2">entries</span>
                    </div>
                </div>
            </div>

            <!-- Results Info -->
            <?php if (!empty($search)): ?>
                <div class="alert  text-white bg-gradient-primary">
                    <i class="fas fa-info-circle"></i>
                    Found <?= number_format($totalFiltered) ?> results for "<strong><?= esc($search) ?></strong>"
                    <?php if ($totalFiltered != $totalRecords): ?>
                        (filtered from <?= number_format($totalRecords) ?> total entries)
                    <?php endif; ?>
                </div>
            <?php endif; ?>

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
                        <?php if (!empty($tickets)): ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?= esc($ticket['ticket_id'] ?? '') ?></td>
                                    <td><?= esc($ticket['subject'] ?? '') ?></td>
                                    <td><?= esc($ticket['customer_name'] ?? '') ?></td>
                                    <td>
                                        <span class="badge <?= ($ticket['ticket_status_name'] ?? '') === 'CLOSED' ? 'bg-success' : 'bg-warning' ?>">
                                            <?= esc($ticket['ticket_status_name'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?=
                                                            ($ticket['priority_name'] ?? '') === 'High' ? 'bg-danger' : (($ticket['priority_name'] ?? '') === 'Medium' ? 'bg-warning' : 'bg-info')
                                                            ?>">
                                            <?= esc($ticket['priority_name'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td><?= esc($ticket['witel'] ?? '') ?></td>
                                    <td><?= esc($ticket['main_category'] ?? '') ?></td>
                                    <td><?= esc($ticket['category'] ?? '') ?></td>
                                    <td><?= !empty($ticket['date_created_at']) ? date('d/m/Y H:i', strtotime($ticket['date_created_at'])) : (!empty($ticket['date_start_interaction']) ? date('d/m/Y H:i', strtotime($ticket['date_start_interaction'])) : '-') ?></td>
                                    <td><?= !empty($ticket['date_close']) ? date('d/m/Y H:i', strtotime($ticket['date_close'])) : '-' ?></td>
                                    <td><?= esc($ticket['created_by_name'] ?? '') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="viewTicket('<?= $ticket['ticket_id'] ?? '' ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info and Controls -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="dataTables_info">
                        Showing <?= number_format($startRecord) ?> to <?= number_format($endRecord) ?> of
                        <?= number_format($totalFiltered) ?> entries
                        <?php if (!empty($search) && $totalFiltered != $totalRecords): ?>
                            (filtered from <?= number_format($totalRecords) ?> total entries)
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Table pagination">
                            <ul class="pagination justify-content-end mb-0">
                                <!-- Previous Page -->
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage - 1 ?>&per_page=<?= $perPage ?>&search=<?= urlencode($search ?? '') ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Previous</span>
                                    </li>
                                <?php endif; ?>

                                <!-- Page Numbers -->
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);

                                if ($startPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $baseUrl ?>?page=1&per_page=<?= $perPage ?>&search=<?= urlencode($search ?? '') ?>">1</a>
                                    </li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link<?= $i == $currentPage ? ' bg-primary text-white border-white' : '' ?>" href="<?= $baseUrl ?>?page=<?= $i ?>&per_page=<?= $perPage ?>&search=<?= urlencode($search ?? '') ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $baseUrl ?>?page=<?= $totalPages ?>&per_page=<?= $perPage ?>&search=<?= urlencode($search ?? '') ?>"><?= $totalPages ?></a>
                                    </li>
                                <?php endif; ?>

                                <!-- Next Page -->
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage + 1 ?>&per_page=<?= $perPage ?>&search=<?= urlencode($search ?? '') ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Next</span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
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

<script>
    $(document).ready(function() {
        // Export functionality
        $('#exportBtn').on('click', function() {
            var searchValue = '<?= esc($search ?? '') ?>';
            $('#loadingModal').modal('show');

            window.location.href = '<?= base_url('dashboard/export') ?>?search=' + encodeURIComponent(searchValue);

            setTimeout(function() {
                $('#loadingModal').modal('hide');
            }, 3000);
        });

        // Refresh functionality - reload page
        $('#refreshBtn').on('click', function() {
            location.reload();
        });

        // Real-time search with debounce
        let searchTimeout;
        $('#searchInput').on('input', function() {
            clearTimeout(searchTimeout);
            const searchValue = $(this).val();

            searchTimeout = setTimeout(function() {
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#searchForm').submit();
                }
            }, 500); // 500ms delay
        });
    });

    // Function to change per page value
    function changePerPage(perPage) {
        const url = new URL(window.location);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', 1); // Reset to first page
        window.location.href = url.toString();
    }

    // Function to view ticket details
    function viewTicket(ticketId) {
        window.location.href = '<?= base_url('dashboard/detail/') ?>' + ticketId;
    }
</script>

<?= $this->endSection(); ?>