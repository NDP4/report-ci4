<?= $this->extend('layout/main-base') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">Upload Data AUX</h6>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-8">
                            <form action="<?= base_url('dashboard/aux/upload/process') ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>

                                <div class="form-group mb-3">
                                    <label for="file_type" class="form-label">Jenis Data</label>
                                    <select class="form-select" id="file_type" name="file_type" required>
                                        <option value="">Pilih jenis data...</option>
                                        <option value="sdm">Data SDM</option>
                                        <option value="presensi">Data Presensi</option>
                                        <option value="queue_onx">Data Queue ONX</option>
                                        <option value="report_agent_log">Data Report Agent Log (AUX)</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="file" class="form-label">File Excel/CSV</label>
                                    <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                                    <small class="form-text text-muted">Format yang didukung: .xlsx, .xls, .csv (Maksimal 500MB)</small>
                                    <div id="file-info" class="mt-2" style="display: none;">
                                        <small class="text-info">
                                            <i class="fas fa-info-circle"></i>
                                            <span id="file-size"></span> - Upload mungkin memerlukan waktu lebih lama untuk file besar.
                                        </small>
                                    </div>
                                </div>

                                <!-- Progress Bar for Upload -->
                                <div id="upload-progress" class="mb-3" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Mengupload file...</small>
                                        <small class="text-muted" id="progress-text">0%</small>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar" style="width: 0%" id="progress-bar"></div>
                                    </div>
                                    <small class="text-muted mt-1">
                                        <i class="fas fa-hourglass-half"></i>
                                        Proses upload dan import sedang berlangsung. Mohon jangan menutup halaman ini.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="submit-btn">
                                        <i class="fas fa-upload me-2"></i>Upload
                                    </button>
                                    <button type="button" class="btn btn-primary d-none" id="submit-loading" disabled>
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Processing...
                                    </button>
                                    <a href="<?= base_url('dashboard/aux') ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">Format File Excel</h6>
                                </div>
                                <div class="card-body">
                                    <div id="format-info">
                                        <p class="text-muted">Pilih jenis data untuk melihat format yang diharapkan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileTypeSelect = document.getElementById('file_type');
        const formatInfo = document.getElementById('format-info');
        const fileInput = document.getElementById('file');
        const fileInfo = document.getElementById('file-info');
        const fileSizeDisplay = document.getElementById('file-size');
        const uploadForm = document.querySelector('form');
        const submitBtn = document.getElementById('submit-btn');
        const submitLoading = document.getElementById('submit-loading');
        const progressContainer = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');

        const formats = {
            'sdm': `
            <h6>Format Data SDM:</h6>
            <ol>
                <li>Log ID</li>
                <li>Full Name</li>
                <li>Channel Name</li>
                <li>Position</li>
                <li>Unit</li>
            </ol>
        `,
            'presensi': `
            <h6>Format Data Presensi:</h6>
            <ol>
                <li>Log ID</li>
                <li>Full Name</li>
                <li>Work Date (YYYY-MM-DD)</li>
                <li>Time In (HH:MM:SS)</li>
                <li>Time Out (HH:MM:SS)</li>
                <li>Hadir (1/0)</li>
            </ol>
        `,
            'queue_onx': `
            <h6>Format Data Queue ONX:</h6>
            <ol>
                <li>Source ID</li>
                <li>Full Name</li>
                <li>Channel Name</li>
                <li>Kolom 4</li>
                <li>Kolom 5</li>
                <li>Date Start Interaction</li>
                <li>Main Category</li>
                <li>Category</li>
                <li>Witel</li>
            </ol>
        `,
            'report_agent_log': `
            <h6>Format Data Report Agent Log:</h6>
            <ol>
                <li>Full Name</li>
                <li>Kolom 2</li>
                <li>Date Start</li>
                <li>State</li>
                <li>Reason Login</li>
            </ol>
        `
        };

        // Handle file type change
        fileTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            if (selectedType && formats[selectedType]) {
                formatInfo.innerHTML = formats[selectedType];
            } else {
                formatInfo.innerHTML = '<p class="text-muted">Pilih jenis data untuk melihat format yang diharapkan.</p>';
            }
        });

        // Handle file selection
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                fileSizeDisplay.textContent = `Ukuran file: ${fileSize} MB`;
                fileInfo.style.display = 'block';

                // Show warning for large files
                if (file.size > 50 * 1024 * 1024) { // 50MB
                    fileSizeDisplay.innerHTML += ' <span class="text-warning">(File besar - proses mungkin memerlukan waktu lama)</span>';
                }
            } else {
                fileInfo.style.display = 'none';
            }
        });

        // Handle form submission
        uploadForm.addEventListener('submit', function(e) {
            const file = fileInput.files[0];
            if (!file) {
                e.preventDefault();
                alert('Silakan pilih file terlebih dahulu.');
                return;
            }

            // Check file size (500MB limit)
            if (file.size > 500 * 1024 * 1024) {
                e.preventDefault();
                alert('Ukuran file terlalu besar. Maksimal 500MB.');
                return;
            }

            // Show loading state after form validation
            setTimeout(function() {
                submitBtn.classList.add('d-none');
                submitLoading.classList.remove('d-none');

                // For large files, show progress simulation
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    progressContainer.style.display = 'block';
                    simulateProgress();
                }
            }, 100);

            // DO NOT disable form elements before submission!
            // Elements will be disabled after successful submission
        }); // Simulate upload progress for large files
        function simulateProgress() {
            let progress = 0;
            const interval = setInterval(function() {
                progress += Math.random() * 15;
                if (progress > 95) progress = 95; // Don't complete until real upload finishes

                progressBar.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '%';
            }, 1000);

            // Clear interval after 30 seconds
            setTimeout(function() {
                clearInterval(interval);
            }, 30000);
        }

        // Prevent page unload during upload
        let formSubmitted = false;

        uploadForm.addEventListener('submit', function() {
            formSubmitted = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if (formSubmitted && submitBtn.classList.contains('d-none')) {
                e.preventDefault();
                e.returnValue = 'Upload sedang berlangsung. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });
    });
</script>

<?= $this->endSection() ?>