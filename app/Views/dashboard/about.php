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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>About Me</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-3"><strong>Username:</strong></div>
                            <div class="col-sm-9"><?= $user['username'] ?></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Email:</strong></div>
                            <div class="col-sm-9"><?= $user['email'] ?></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Role:</strong></div>
                            <div class="col-sm-9">
                                <span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Permissions:</strong></div>
                            <div class="col-sm-9">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-success me-1">Full Access</span>
                                    <span class="badge bg-info me-1">User Management</span>
                                    <span class="badge bg-warning">System Config</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary me-1">View Only</span>
                                    <span class="badge bg-primary">Export Reports</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="<?= base_url('/dashboard') ?>" class="btn btn-primary">Back to Dashboard</a>
                            <a href="<?= base_url('/auth/logout') ?>" class="btn btn-outline-danger">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>