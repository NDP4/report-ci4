<?= $this->extend('layout/main-base') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Activity Log</h6>
                        <p class="text-sm mb-0">System activity and user actions log</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('dashboard/activitylog/export') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i>Export CSV
                        </a>
                        <button type="button" class="btn btn-warning btn-sm" onclick="bulkDelete()">
                            <i class="fas fa-trash-alt me-1"></i>Delete Selected
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="clearAllLogs()">
                            <i class="fas fa-trash me-1"></i>Clear All
                        </button>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mx-4" role="alert" id="successAlert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert" id="errorAlert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive p-0">
                        <form id="bulkDeleteForm" method="post" action="<?= base_url('dashboard/activitylog/bulk-delete') ?>">
                            <?= csrf_field() ?>
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date/Time</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">User</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Action</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Description</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_logs[]" value="<?= $log['id'] ?>" class="log-checkbox">
                                                </td>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0"><?= esc($log['username'] ?? 'System') ?></p>
                                                    <p class="text-xs text-secondary mb-0">ID: <?= esc($log['user_id'] ?? 'N/A') ?></p>
                                                </td>
                                                <td>
                                                    <?php
                                                    $actionClass = '';
                                                    switch ($log['action']) {
                                                        case 'login_success':
                                                            $actionClass = 'badge-success';
                                                            break;
                                                        case 'login_failed':
                                                            $actionClass = 'badge-danger';
                                                            break;
                                                        case 'logout':
                                                            $actionClass = 'badge-info';
                                                            break;
                                                        case 'import_data':
                                                            $actionClass = 'badge-primary';
                                                            break;
                                                        case 'create_user':
                                                        case 'update_user':
                                                        case 'delete_user':
                                                            $actionClass = 'badge-warning';
                                                            break;
                                                        default:
                                                            $actionClass = 'badge-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge badge-sm bg-gradient-<?= str_replace('badge-', '', $actionClass) ?>">
                                                        <?= esc($log['action']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <p class="text-xs text-secondary mb-0" style="max-width: 300px; word-wrap: break-word;">
                                                        <?= esc($log['description']) ?>
                                                    </p>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <button type="button" class="btn btn-link text-danger px-3 mb-0" onclick="deleteLog(<?= $log['id'] ?>)" title="Delete Log">
                                                        <i class="fas fa-trash text-danger" aria-hidden="true"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <p class="text-secondary mb-0">No activity logs found</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </form>
                    </div>

                    <?php if (isset($pager)): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <?= $pager->links() ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this activity log? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirm Bulk Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                </div>
                <p class="mb-0">Are you sure you want to delete <strong id="selectedCount">0</strong> selected activity log(s)?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmBulkDelete">
                    <i class="fas fa-trash me-1"></i>Delete Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Clear All Confirmation Modal -->
<div class="modal fade" id="clearAllModal" tabindex="-1" aria-labelledby="clearAllModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearAllModalLabel">Clear All Activity Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Warning!</strong> This will permanently delete ALL activity logs from the system. This action cannot be undone.
                </div>
                Are you sure you want to clear all activity logs?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmClearAll">Clear All</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-hide alerts after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');

        if (successAlert) {
            setTimeout(function() {
                const alert = new bootstrap.Alert(successAlert);
                alert.close();
            }, 3000);
        }

        if (errorAlert) {
            setTimeout(function() {
                const alert = new bootstrap.Alert(errorAlert);
                alert.close();
            }, 3000);
        }
    });

    // Toggle all checkboxes
    function toggleAllCheckboxes() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.log-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    // Delete single log
    function deleteLog(id) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();

        document.getElementById('confirmDelete').onclick = function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?= base_url('dashboard/activitylog/delete/') ?>${id}`;

            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '<?= csrf_token() ?>';
            csrfField.value = '<?= csrf_hash() ?>';
            form.appendChild(csrfField);

            document.body.appendChild(form);
            form.submit();
        };
    }

    // Bulk delete selected logs
    function bulkDelete() {
        const selectedLogs = document.querySelectorAll('.log-checkbox:checked');

        if (selectedLogs.length === 0) {
            // Custom alert for no selection
            const alertModal = document.createElement('div');
            alertModal.className = 'modal fade';
            alertModal.setAttribute('tabindex', '-1');
            alertModal.innerHTML = `
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h6 class="modal-title">
                                <i class="fas fa-info-circle text-info me-2"></i>No Selection
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">Please select at least one activity log to delete.</p>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">
                                <i class="fas fa-check me-1"></i>OK
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(alertModal);
            const modal = new bootstrap.Modal(alertModal);
            modal.show();

            // Remove modal after it's hidden
            alertModal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(alertModal);
            });
            return;
        }

        // Show custom bulk delete modal
        document.getElementById('selectedCount').textContent = selectedLogs.length;
        const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        modal.show();

        document.getElementById('confirmBulkDelete').onclick = function() {
            document.getElementById('bulkDeleteForm').submit();
        };
    }

    // Clear all logs
    function clearAllLogs() {
        const modal = new bootstrap.Modal(document.getElementById('clearAllModal'));
        modal.show();

        document.getElementById('confirmClearAll').onclick = function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('dashboard/activitylog/clear-all') ?>';

            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '<?= csrf_token() ?>';
            csrfField.value = '<?= csrf_hash() ?>';
            form.appendChild(csrfField);

            document.body.appendChild(form);
            form.submit();
        };
    }
</script>

<?= $this->endsection() ?>