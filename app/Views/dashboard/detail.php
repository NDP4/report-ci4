<?= $this->extend('layout/main-base'); ?>

<?= $this->section('content'); ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- tombol kembali  -->
            <div class="d-flex justify-content-end mb-3">
                <a href="<?= base_url('dashboard/ticket') ?>" class="btn bg-gradient-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Tickets
                </a>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-ticket-alt me-2"></i>Ticket Detail: <?= $ticket['ticket_id'] ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5 class="text-primary">Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Ticket ID:</strong></td>
                                    <td><?= $ticket['ticket_id'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Subject:</strong></td>
                                    <td><?= $ticket['subject'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge bg-<?= strtolower($ticket['ticket_status_name']) == 'closed' ? 'success' : 'warning' ?>"><?= $ticket['ticket_status_name'] ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td><?= $ticket['priority_name'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td><?= $ticket['created_by_name'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Witel:</strong></td>
                                    <td><?= $ticket['witel'] ?></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h5 class="text-primary">Customer Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Customer ID:</strong></td>
                                    <td><?= $ticket['customer_id'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Customer Name:</strong></td>
                                    <td><?= $ticket['customer_name'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Customer HP:</strong></td>
                                    <td><?= $ticket['customer_hp'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Customer Email:</strong></td>
                                    <td><?= $ticket['customer_email'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Informant Name:</strong></td>
                                    <td><?= $ticket['informant_name'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Informant HP:</strong></td>
                                    <td><?= $ticket['informant_hp'] ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <!-- Category Information -->
                        <div class="col-md-6">
                            <h5 class="text-primary">Category Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Main Category:</strong></td>
                                    <td><?= $ticket['main_category'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td><?= $ticket['category'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Sub Category:</strong></td>
                                    <td><?= $ticket['sub_category'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Detail Sub Category:</strong></td>
                                    <td><?= $ticket['detail_sub_category'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Channel:</strong></td>
                                    <td><?= $ticket['channel_name'] ?></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Date Information -->
                        <div class="col-md-6">
                            <h5 class="text-primary">Date Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Date Created:</strong></td>
                                    <td><?= $ticket['date_start_interaction'] ? date('d/m/Y H:i:s', strtotime($ticket['date_start_interaction'])) : '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Date Open:</strong></td>
                                    <td><?= $ticket['date_open'] ? date('d/m/Y H:i:s', strtotime($ticket['date_open'])) : '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Date Close:</strong></td>
                                    <td><?= $ticket['date_close'] ? date('d/m/Y H:i:s', strtotime($ticket['date_close'])) : '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Last Update:</strong></td>
                                    <td><?= $ticket['date_last_update'] ? date('d/m/Y H:i:s', strtotime($ticket['date_last_update'])) : '-' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if (!empty($ticket['remark']) || !empty($ticket['feedback'])): ?>
                        <hr>
                        <div class="row">
                            <?php if (!empty($ticket['remark'])): ?>
                                <div class="col-md-6">
                                    <h5 class="text-primary">Remark</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <?= nl2br(htmlspecialchars($ticket['remark'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($ticket['feedback'])): ?>
                                <div class="col-md-6">
                                    <h5 class="text-primary">Feedback</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <?= nl2br(htmlspecialchars($ticket['feedback'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->endSection(); ?>