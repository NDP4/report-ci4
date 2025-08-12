<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <!-- Argon Dashboard CSS -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="<?= base_url('assets/css/nucleo-icons.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/css/nucleo-svg.css') ?>" rel="stylesheet" />
    <link id="pagestyle" href="<?= base_url('assets/css/argon-dashboard.css?v=2.0.4') ?>" rel="stylesheet" />
    <style>
        /* .bg-gradient-primary {
            background: linear-gradient(87deg, #c5040c 0, #c5040c 100%) !important;
        } */

        .btn-primary {
            background-color: #c5040c !important;
            border-color: #c5040c !important;
        }

        .btn-primary:focus,
        .btn-primary:hover {
            background-color: #fc540c !important;
            border-color: #fc540c !important;
        }

        .text-primary {
            color: #c5040c !important;
        }

        .form-check-input:checked {
            background-color: #c5040c !important;
            border-color: #c5040c !important;
        }
    </style>
</head>

<body class="">
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain mt-5">
                                <div class="card-header pb-0 text-start">
                                    <h4 class="font-weight-bolder">Login</h4>
                                    <p class="mb-0">Masukkan username/email dan password untuk login</p>
                                </div>
                                <div class="card-body">
                                    <?php if (session()->getFlashdata('error')): ?>
                                        <div class="alert alert-danger text-white"><?= session()->getFlashdata('error') ?></div>
                                    <?php endif; ?>
                                    <?php if (session()->getFlashdata('success')): ?>
                                        <div class="alert alert-success text-white"><?= session()->getFlashdata('success') ?></div>
                                    <?php endif; ?>
                                    <?php if (session()->getFlashdata('errors')): ?>
                                        <div class="alert alert-danger text-white">
                                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                                <p><?= $error ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <form action="<?= base_url('/auth/authenticate') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <div class="mb-3">
                                            <label for="login" class="form-label">Username or Email</label>
                                            <input type="text" class="form-control form-control-lg" id="login" name="login"
                                                value="<?= esc(old('login')) ?>" required maxlength="100" autocomplete="username">
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control form-control-lg" id="password"
                                                name="password" required maxlength="100" autocomplete="current-password">
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                            <label class="form-check-label" for="rememberMe">Remember me</label>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit"
                                                class="btn btn-lg btn-primary w-100 mt-4 mb-0">Login</button>
                                        </div>
                                    </form>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            Demo accounts:<br>
                                            Admin: admin@example.com / admin123<br>
                                            Viewer: viewer@example.com / viewer123
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image: url('<?= base_url('assets/img/bginter.jpg') ?>'); background-size: cover;">
                                <span class="mask bg-gradient-primary opacity-2"></span>
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">"Your Digital CX Partner"</h4>
                                <p class="text-white position-relative">to become a major provider in the information services industry in the regional area.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!--   Core JS Files   -->
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/argon-dashboard.min.js?v=2.0.4') ?>"></script>
</body>

</html>