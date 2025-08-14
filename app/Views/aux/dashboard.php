<?= $this->extend('layout/main-base') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Dashboard AUX - <?= date('d F Y', strtotime($workDate)) ?></h6>
                        <div>
                            <a href="<?= base_url('dashboard/aux/upload') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload me-2"></i>Upload Data
                            </a>
                            <button type="button" class="btn btn-success btn-sm" onclick="computeBuckets()">
                                <i class="fas fa-calculator me-2"></i>Hitung Bucket
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="get" action="<?= base_url('dashboard/aux') ?>" class="row g-3">
                        <div class="col-md-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?= $workDate ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="bucket" class="form-label">Bucket</label>
                            <select class="form-select" id="bucket" name="bucket">
                                <option value="">Semua Bucket</option>
                                <option value="1" <?= $selectedBucket === '1' ? 'selected' : '' ?>>Bucket 1</option>
                                <option value="2" <?= $selectedBucket === '2' ? 'selected' : '' ?>>Bucket 2</option>
                                <option value="3" <?= $selectedBucket === '3' ? 'selected' : '' ?>>Bucket 3</option>
                                <option value="idle" <?= $selectedBucket === 'idle' ? 'selected' : '' ?>>Idle</option>
                                <option value="anomali" <?= $selectedBucket === 'anomali' ? 'selected' : '' ?>>Anomali</option>
                                <option value="absent" <?= $selectedBucket === 'absent' ? 'selected' : '' ?>>Absent</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="<?= base_url('dashboard/aux/export?date=' . $workDate) ?>" class="btn btn-outline-success">
                                <i class="fas fa-download me-2"></i>Export
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $bucketCounts = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            'idle' => 0,
            'anomali' => 0,
            'absent' => 0
        ];
        foreach ($bucketStats as $stat) {
            $bucketCounts[$stat['bucket']] = $stat['count'];
        }
        ?>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success"><?= $bucketCounts['1'] ?></h5>
                    <p class="card-text">Bucket 1</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning"><?= $bucketCounts['2'] ?></h5>
                    <p class="card-text">Bucket 2</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-danger"><?= $bucketCounts['3'] ?></h5>
                    <p class="card-text">Bucket 3</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info"><?= $bucketCounts['idle'] ?></h5>
                    <p class="card-text">Idle</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-secondary"><?= $bucketCounts['anomali'] ?></h5>
                    <p class="card-text">Anomali</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-dark"><?= $bucketCounts['absent'] ?></h5>
                    <p class="card-text">Absent</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Distribusi Bucket</h6>
                </div>
                <div class="card-body">
                    <canvas id="bucketChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Ticket by Witel</h6>
                </div>
                <div class="card-body">
                    <canvas id="witelChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Ticket by Category</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Bucket Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Status Agent</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="agentTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Agent</th>
                                    <th>Channel</th>
                                    <th>Queue Count</th>
                                    <th>Has AUX</th>
                                    <th>Presensi</th>
                                    <th>Bucket</th>
                                    <th>Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agentBuckets as $key => $agent) : ?>
                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td><?= esc($agent['fullname_norm']) ?></td>
                                        <td><?= esc($agent['channel_name_norm']) ?></td>
                                        <td><?= $agent['queue_count'] ?></td>
                                        <td>
                                            <span class="badge <?= $agent['has_aux'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $agent['has_aux'] ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $agent['presensi'] ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $agent['presensi'] ? 'Hadir' : 'Absent' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $bucketColor = '';
                                            switch ($agent['bucket']) {
                                                case '1':
                                                    $bucketColor = 'bg-success';
                                                    break;
                                                case '2':
                                                    $bucketColor = 'bg-warning';
                                                    break;
                                                case '3':
                                                    $bucketColor = 'bg-danger';
                                                    break;
                                                case 'idle':
                                                    $bucketColor = 'bg-info';
                                                    break;
                                                case 'anomali':
                                                    $bucketColor = 'bg-secondary';
                                                    break;
                                                case 'absent':
                                                    $bucketColor = 'bg-dark';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $bucketColor ?>">
                                                <?= ucfirst($agent['bucket']) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($agent['reason']) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="showAuxDetails('<?= esc($agent['fullname_norm']) ?>')">
                                                <i class="fas fa-eye"></i> Detail AUX
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AUX Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Detail AUX Activities</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="auxTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Agent</th>
                                    <th>Waktu</th>
                                    <th>State</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($auxDetails as $key => $aux) : ?>
                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td><?= esc($aux['fullname_norm']) ?></td>
                                        <td><?= date('H:i:s', strtotime($aux['date_start'])) ?></td>
                                        <td><?= esc($aux['state']) ?></td>
                                        <td><?= esc($aux['reason_login']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for AUX Details -->
<div class="modal fade" id="auxDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail AUX Activities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="auxDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Chart data from PHP
    const chartData = <?= json_encode($chartData) ?>;

    // Bucket Chart
    if (chartData.bucket.labels.length > 0) {
        const bucketCtx = document.getElementById('bucketChart').getContext('2d');
        new Chart(bucketCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.bucket.labels,
                datasets: [{
                    data: chartData.bucket.data,
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#343a40']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Witel Chart
    if (chartData.witel.labels.length > 0) {
        const witelCtx = document.getElementById('witelChart').getContext('2d');
        new Chart(witelCtx, {
            type: 'bar',
            data: {
                labels: chartData.witel.labels,
                datasets: [{
                    label: 'Tickets',
                    data: chartData.witel.data,
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Category Chart
    if (chartData.category.labels.length > 0) {
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: chartData.category.labels,
                datasets: [{
                    data: chartData.category.data,
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // DataTables initialization
    $(document).ready(function() {
        $('#agentTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [
                [6, 'asc']
            ] // Sort by bucket
        });

        $('#auxTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [
                [2, 'asc']
            ] // Sort by time
        });
    });

    // Compute buckets function
    function computeBuckets() {
        const workDate = document.getElementById('date').value || '<?= date('Y-m-d') ?>';

        fetch('<?= base_url('dashboard/aux/compute-buckets') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({
                    work_date: workDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghitung bucket');
            });
    }

    // Show AUX details function
    function showAuxDetails(fullname) {
        const workDate = document.getElementById('date').value || '<?= date('Y-m-d') ?>';

        fetch(`<?= base_url('dashboard/aux/aux-details') ?>?date=${workDate}&name=${fullname}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let content = '<div class="table-responsive"><table class="table table-sm">';
                    content += '<thead><tr><th>Waktu</th><th>State</th><th>Reason</th></tr></thead><tbody>';

                    if (data.data.length > 0) {
                        data.data.forEach(aux => {
                            const time = new Date(aux.date_start).toLocaleTimeString();
                            content += `<tr><td>${time}</td><td>${aux.state}</td><td>${aux.reason_login || '-'}</td></tr>`;
                        });
                    } else {
                        content += '<tr><td colspan="3" class="text-center">Tidak ada data AUX</td></tr>';
                    }

                    content += '</tbody></table></div>';

                    document.getElementById('auxDetailsContent').innerHTML = content;
                    const modal = new bootstrap.Modal(document.getElementById('auxDetailsModal'));
                    modal.show();
                } else {
                    alert('Error loading AUX details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat detail AUX');
            });
    }
</script>

<?= $this->endSection() ?>