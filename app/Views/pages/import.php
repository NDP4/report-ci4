<?= $this->extend('layout/main-base'); ?>

<?= $this->section('content'); ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-gradient-primary pb-3 border-radius-lg">
                    <h5 class="mb-0 text-white text-sm"><i class="fas fa-upload me-2"></i>Import Service Ticket Data</h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success bg-gradient-success text-white alert-dismissible fade show border-radius-lg" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger bg-gradient-danger text-white alert-dismissible fade show border-radius-lg" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger bg-gradient-danger text-white alert-dismissible fade show border-radius-lg" role="alert">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <div class="alert bg-gradient-primary text-white mb-3 border-radius-lg">
                        <strong>Server Limits:</strong><br>
                        Upload Max Size: <?= $uploadMaxSize ?? 'Unknown' ?><br>
                        POST Max Size: <?= $postMaxSize ?? 'Unknown' ?><br>
                        Memory Limit: <?= $memoryLimit ?? 'Unknown' ?>
                    </div>
                    <form action="<?= base_url('import/upload') ?>" method="post" enctype="multipart/form-data" id="uploadForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="file" class="form-label text-dark">Pilih File Excel/CSV</label>
                            <input type="file" class="form-control border border-primary border-radius-lg" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text text-muted">
                                Format yang didukung: .xlsx, .xls, .csv (Maksimal berdasarkan server limit di atas)
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="alert bg-gradient-info text-white border-radius-lg">
                                <strong>Format Kolom Excel (76 Kolom) - Sesuai Header Excel:</strong><br>
                                <small>
                                    1. ticket_id | 2. subject | 3. remark | ... hingga 76 kolom<br>
                                    <em>Pastikan header Excel persis seperti contoh di atas!</em><br>
                                    <strong>Note:</strong> Beberapa field menggunakan camelCase: mainCategory, subCategory, detailSubCategory, dll.
                                </small>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn bg-gradient-primary text-white border-radius-lg" id="uploadBtn"><i class="fas fa-upload me-1"></i>Upload & Import</button>
                            <a href="<?= base_url('import/template') ?>" class="btn btn-outline-secondary border-radius-lg"><i class="fas fa-download me-1"></i>Download Template</a>
                        </div>
                    </form>
                    <div id="progressContainer" class="mt-3" style="display: none;">
                        <div class="alert bg-gradient-warning text-dark mb-0 border-radius-lg">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                                <span class="text-white">Mengupload dan memproses file...</span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary border-radius-lg" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>