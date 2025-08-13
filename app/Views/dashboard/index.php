<?= $this->extend('layout/main-base'); ?>

<?= $this->section('content'); ?>

<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card mb-3">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Tickets</p>
                                <h5 class="font-weight-bolder"><?= number_format($totalRecords) ?></h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <!-- <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i> -->
                                <i class="fa-solid fa-ticket text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card mb-3">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Closed Tickets</p>
                                <h5 class="font-weight-bolder text-success"><?= number_format($totalClosed) ?></h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <!-- <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i> -->
                                <i class="fa-solid fa-folder-closed text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card mb-3">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Open Tickets</p>
                                <h5 class="font-weight-bolder text-warning"><?= number_format($totalOpen) ?></h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <!-- <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i> -->
                                <i class="fa-solid fa-folder-open text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Filter Data</h6>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Date Start</label>
                                    <input type="date" class="form-control" id="date_start" name="date_start">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Date End</label>
                                    <input type="date" class="form-control" id="date_end" name="date_end">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Main Category</label>
                                    <select class="form-select" id="main_category" name="main_category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($mainCategories as $mainCat): ?>
                                            <option value="<?= esc($mainCat['main_category']) ?>"><?= esc($mainCat['main_category']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= esc($cat['category']) ?>"><?= esc($cat['category']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Channel</label>
                                    <select class="form-select" id="channel" name="channel">
                                        <option value="">All Channels</option>
                                        <?php foreach ($channels as $ch): ?>
                                            <option value="<?= esc($ch['channel_name']) ?>"><?= esc($ch['channel_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Witel</label>
                                    <select class="form-select" id="witel" name="witel">
                                        <option value="">All Witel</option>
                                        <?php foreach ($witels as $witel): ?>
                                            <option value="<?= esc($witel['witel']) ?>"><?= esc($witel['witel']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" id="applyFilter">
                                    <i class="fas fa-filter me-1"></i>Apply Filter
                                </button>
                                <button type="button" class="btn btn-secondary" id="resetFilter">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <!-- Status Distribution Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Ticket Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Monthly Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Category Distribution Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Category Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Witel Distribution Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Witel Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="witelChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let statusChart, monthlyChart, categoryChart, witelChart;

    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        loadChartData();

        // Main Category change event - load dependent categories
        document.getElementById('main_category').addEventListener('change', function() {
            loadCategoriesByMainCategory(this.value);
        });

        // Apply filter button
        document.getElementById('applyFilter').addEventListener('click', function() {
            loadChartData();
        });

        // Reset filter button
        document.getElementById('resetFilter').addEventListener('click', function() {
            document.getElementById('filterForm').reset();
            // Reset category dropdown to show all categories
            loadCategoriesByMainCategory('');
            loadChartData();
        });
    });

    function loadCategoriesByMainCategory(mainCategory) {
        const categorySelect = document.getElementById('category');

        // Show loading state
        categorySelect.innerHTML = '<option value="">Loading...</option>';
        categorySelect.disabled = true;

        // Prepare form data
        const formData = new FormData();
        formData.append('main_category', mainCategory);

        // Add CSRF token if it exists
        const csrfToken = document.querySelector('meta[name="<?= csrf_token() ?>"]');
        if (csrfToken) {
            formData.append('<?= csrf_token() ?>', csrfToken.getAttribute('content'));
        }

        fetch(`<?= base_url('dashboard/getCategoriesByMainCategory') ?>`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                categorySelect.disabled = false;

                if (data.status === 'success') {
                    // Clear and rebuild category options
                    categorySelect.innerHTML = '<option value="">All Categories</option>';

                    data.data.forEach(function(category) {
                        const option = document.createElement('option');
                        option.value = category.category;
                        option.textContent = category.category;
                        categorySelect.appendChild(option);
                    });
                } else {
                    categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    console.error('Error loading categories:', data.message);
                }
            })
            .catch(error => {
                categorySelect.disabled = false;
                categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                console.error('Error:', error);
            });
    }

    function initializeCharts() {
        // Status Chart (Pie)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#28a745', // Success - Green
                        '#ffc107', // Warning - Yellow
                        '#17a2b8', // Info - Blue
                        '#dc3545', // Danger - Red
                        '#6c757d' // Secondary - Gray
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Chart (Line)
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Tickets',
                    data: [],
                    borderColor: '#c5040c',
                    backgroundColor: 'rgba(197, 4, 12, 0.1)',
                    tension: 0.4,
                    fill: true
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

        // Category Chart (Bar)
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        categoryChart = new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Tickets',
                    data: [],
                    backgroundColor: '#fc8f06'
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

        // Witel Chart (Horizontal Bar)
        const witelCtx = document.getElementById('witelChart').getContext('2d');
        witelChart = new Chart(witelCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Tickets',
                    data: [],
                    backgroundColor: '#c5040c'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function loadChartData() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);

        fetch(`<?= base_url('dashboard/getChartData') ?>?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateCharts(data.data);
                } else {
                    console.error('Error loading chart data:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function updateCharts(data) {
        // Update Status Chart
        statusChart.data.labels = data.statusDistribution.map(item => item.ticket_status_name);
        statusChart.data.datasets[0].data = data.statusDistribution.map(item => item.count);
        statusChart.update();

        // Update Monthly Chart
        monthlyChart.data.labels = data.monthlyTrend.map(item => item.month);
        monthlyChart.data.datasets[0].data = data.monthlyTrend.map(item => item.count);
        monthlyChart.update();

        // Update Category Chart
        categoryChart.data.labels = data.categoryDistribution.map(item => item.main_category);
        categoryChart.data.datasets[0].data = data.categoryDistribution.map(item => item.count);
        categoryChart.update();

        // Update Witel Chart
        witelChart.data.labels = data.witelDistribution.map(item => item.witel);
        witelChart.data.datasets[0].data = data.witelDistribution.map(item => item.count);
        witelChart.update();
    }
</script>

<?= $this->endsection(); ?>