<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('/dashboard') ?>">Report System</a>

            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <?= $user['username'] ?> (<?= ucfirst($user['role']) ?>)
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= base_url('/dashboard/about') ?>">About Me</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="<?= base_url('/auth/logout') ?>">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Dashboard</h5>
                    </div>
                    <div class="card-body">
                        <h6>Welcome, <?= $user['username'] ?>!</h6>
                        <p>You are logged in as: <span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= ucfirst($user['role']) ?></span></p>

                        <?php if ($user['role'] === 'admin'): ?>
                            <div class="alert alert-info">
                                <h6>Admin Features:</h6>
                                <ul>
                                    <li>Full access to all reports</li>
                                    <li>User management</li>
                                    <li>System configuration</li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h6>Viewer Features:</h6>
                                <ul>
                                    <li>View reports only</li>
                                    <li>Export reports</li>
                                    <li>Limited access</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>