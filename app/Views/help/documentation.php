<?= $this->extend('layout/main-base'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-lg">
                <div class="card-header pb-0">
                    <h3 class="font-weight-bolder text-primary">Documentation & Help</h3>
                    <p class="mb-0">Panduan penggunaan aplikasi website Telkomsel Infomedia</p>
                </div>
                <div class="card-body">
                    <h5 class="text-dark">Tentang Aplikasi</h5>
                    <p>Aplikasi ini digunakan untuk manajemen data pelanggan, ticketing layanan, dan administrasi user di lingkungan Telkomsel Infomedia. Dirancang dengan keamanan dan kemudahan penggunaan.</p>
                    <hr>
                    <h5 class="text-dark">Fitur Utama</h5>
                    <ul>
                        <li><b>Login & Logout</b>: Autentikasi user dengan perlindungan brute-force dan rate limiting.</li>
                        <li><b>Dashboard</b>: Ringkasan data dan aktivitas terbaru.</li>
                        <li><b>Service Ticket</b>: Manajemen dan pelacakan tiket layanan pelanggan.</li>
                        <li><b>Import Data</b>: (Admin) Import data pelanggan secara massal.</li>
                        <li><b>Management User</b>: (Admin) Kelola user, hak akses, dan audit log.</li>
                    </ul>
                    <hr>
                    <h5 class="text-dark">Keamanan</h5>
                    <ul>
                        <li>Proteksi login: rate limiting, brute-force lockout, adaptive delay.</li>
                        <li>Audit log: Semua aktivitas login, update, dan delete tercatat.</li>
                        <li>CSRF & XSS protection di semua form.</li>
                        <li>Session dan cookie aman, hanya diakses via HTTPS (production).</li>
                    </ul>
                    <hr>
                    <h5 class="text-dark">Cara Penggunaan</h5>
                    <ol>
                        <li>Login dengan username/email dan password yang valid.</li>
                        <li>Gunakan menu di sidebar untuk navigasi fitur.</li>
                        <li>Admin dapat mengakses menu Import Data dan Management User.</li>
                        <li>Logout dengan klik tombol di sidebar.</li>
                    </ol>
                    <hr>
                    <h5 class="text-dark">Kontak Bantuan</h5>
                    <p>Jika mengalami kendala, silakan hubungi admin IT atau email: <b>support@infomedia.co.id</b></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>