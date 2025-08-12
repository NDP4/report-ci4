<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
        <!-- <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i> -->
        <a class="navbar-brand m-0" href="/">
            <img src="<?php echo base_url('assets/img/logo.png'); ?>" width="32px" height="32px" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">Telkomsel Infomedia</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <?php $uri = service('uri'); ?>
            <li class="nav-item">
                <a class="nav-link<?= ($uri->getSegment(1) == 'dashboard' && $uri->getSegment(2) == '') ? ' active' : '' ?>" href="<?= base_url('dashboard') ?>">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-regular fa-house text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Ticket</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= ($uri->getSegment(1) == 'dashboard' && $uri->getSegment(2) == 'ticket') ? ' active' : '' ?>" href="<?= base_url('dashboard/ticket') ?>">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-regular fa-ticket text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Service Ticket</span>
                </a>
            </li>
            <?php if (session()->get('role') == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link<?= ($uri->getSegment(1) == 'dashboard' && $uri->getSegment(2) == 'import') ? ' active' : '' ?>" href="<?= base_url('dashboard/import') ?>">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-file-arrow-up text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Import Data</span>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account pages</h6>
            </li>
            <?php if (session()->get('role') == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link<?= ($uri->getSegment(1) == 'dashboard' && $uri->getSegment(2) == 'user') ? ' active' : '' ?>" href="<?= base_url('dashboard/user') ?>">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-user text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Management User</span>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link " href="<?= base_url('auth/logout') ?>">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-right-from-bracket text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="sidenav-footer mx-3 ">
        <div class="card card-plain shadow-none" id="sidenavCard">
            <img class="w-50 mx-auto" src="<?php echo base_url('assets/img/logo.png'); ?>" alt="sidebar_illustration">
            <div class="card-body text-center p-3 w-100 pt-0">
                <div class="docs-info">
                    <h6 class="mb-0">Need help?</h6>
                    <p class="text-xs font-weight-bold mb-0">Please check our docs</p>
                </div>
            </div>
        </div>
        <a href="<?php echo base_url('dashboard/help/documentation') ?>" target="_blank" class="btn btn-dark btn-sm w-100 mb-3">Documentation</a>
        <a class="btn btn-primary btn-sm mb-0 w-100" href="<?= base_url('auth/logout') ?>" type="button"><i class="fa-solid fa-right-from-bracket text-white text-sm opacity-10"></i> Logout</a>
    </div>
</aside>