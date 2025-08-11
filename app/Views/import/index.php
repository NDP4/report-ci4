<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Service Ticket Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Import Service Ticket Data</h4>
                    </div>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success">
                                <?= session()->getFlashdata('success') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger">
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Server Limits Info -->
                        <div class="alert alert-warning mb-3">
                            <strong>Server Limits:</strong><br>
                            Upload Max Size: <?= $uploadMaxSize ?? 'Unknown' ?><br>
                            POST Max Size: <?= $postMaxSize ?? 'Unknown' ?><br>
                            Memory Limit: <?= $memoryLimit ?? 'Unknown' ?>
                        </div>

                        <form action="<?= base_url('import/upload') ?>" method="post" enctype="multipart/form-data" id="uploadForm">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="file" class="form-label">Pilih File Excel/CSV</label>
                                <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                                <div class="form-text">
                                    Format yang didukung: .xlsx, .xls, .csv (Maksimal berdasarkan server limit di atas)
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <strong>Format Kolom Excel (76 Kolom) - Sesuai Header Excel:</strong><br>
                                    <small>
                                        1. ticket_id | 2. subject | 3. remark | 4. priority_id | 5. priority_name<br>
                                        6. ticket_status_name | 7. unit_id | 8. unit_name | 9. informant_id | 10. informant_name<br>
                                        11. informant_hp | 12. informant_email | 13. customer_id | 14. customer_name | 15. customer_hp<br>
                                        16. customer_email | 17. date_origin_interaction | 18. date_start_interaction | 19. date_open | 20. date_close<br>
                                        21. date_last_update | 22. is_escalated | 23. created_by_name | 24. updated_by_name | 25. channel_id<br>
                                        26. session_id | 27. category_id | 28. category_name | 29. date_created_at | 30. sla<br>
                                        31. channel_name | 32. <strong>mainCategory</strong> | 33. category | 34. <strong>subCategory</strong> | 35. <strong>detailSubCategory</strong><br>
                                        36. <strong>detailSubCategory2</strong> | ... dan seterusnya hingga 76 kolom...<br><br>
                                        <em>Pastikan header Excel persis seperti contoh di atas!</em><br>
                                        <strong>Note:</strong> Beberapa field menggunakan camelCase: mainCategory, subCategory, detailSubCategory, dll.
                                    </small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="uploadBtn">Upload & Import</button>
                                <a href="<?= base_url('import/template') ?>" class="btn btn-outline-secondary">Download Template</a>
                            </div>
                        </form>

                        <!-- Progress indicator -->
                        <div id="progressContainer" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    <span>Mengupload dan memproses file...</span>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get server POST max size from PHP
        const postMaxSize = <?= json_encode($postMaxSizeBytes ?? 8388608) ?>;

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('file');
            const progressContainer = document.getElementById('progressContainer');
            const uploadBtn = document.getElementById('uploadBtn');

            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;

                if (fileSize > postMaxSize) {
                    e.preventDefault();
                    alert('File terlalu besar. Maksimal ukuran file adalah ' + formatBytes(postMaxSize));
                    return false;
                }

                // Show progress and disable button
                progressContainer.style.display = 'block';
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            }
        });

        // File size validation on file selection
        document.getElementById('file').addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileSize = this.files[0].size;

                if (fileSize > postMaxSize) {
                    this.value = '';
                    alert('File terlalu besar. Maksimal ukuran file adalah ' + formatBytes(postMaxSize));
                }
            }
        });

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    </script>
</body>

</html>