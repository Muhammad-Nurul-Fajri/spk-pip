<?php
session_start();
require_once '../../config/koneksi.php';
require_role('admin');

// Counts for dashboard cards
$total_kriteria = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM kriteria"))['c'];
$total_siswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM siswa"))['c'];
$total_user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM users"))['c'];
$total_pending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM siswa WHERE status_pendaftaran='submitted'"))['c'];

$page_title = 'Dashboard Admin';
$active_menu = 'dashboard';
$asset_depth = 2;
$logo_path = '../../public/assets/img/logo.png';
$base_admin = '';
$base_root = '../../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../layouts/head.php'; ?>
</head>
<body>
<?php include '../layouts/sidebar_admin.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-house"></i>
            <h4>Dashboard Admin</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
        </div>
    </div>

    <!-- MENU CARDS -->
    <div class="row g-3 mb-3">
        <div class="col-md-6 col-lg-3">
            <a href="kriteria/index.php" style="text-decoration:none;">
                <div class="menu-card border-green">
                    <i class="fa fa-list card-icon"></i>
                    <h5>Data Kriteria</h5>
                    <p><?php echo $total_kriteria; ?> kriteria terdaftar</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="sub_kriteria/index_subkriteria.php" style="text-decoration:none;">
                <div class="menu-card border-blue">
                    <i class="fa fa-layer-group card-icon"></i>
                    <h5>Data Sub Kriteria</h5>
                    <p>Kelola sub kriteria penilaian</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="alternatif/index.php" style="text-decoration:none;">
                <div class="menu-card border-orange">
                    <i class="fa fa-user-graduate card-icon"></i>
                    <h5>Data Alternatif</h5>
                    <p><?php echo $total_siswa; ?> siswa terdaftar</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="penilaian/index.php" style="text-decoration:none;">
                <div class="menu-card border-red">
                    <i class="fa fa-check-circle card-icon"></i>
                    <h5>Data Penilaian</h5>
                    <p>Matriks penilaian alternatif</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="perbandingan/index.php" style="text-decoration:none;">
                <div class="menu-card border-purple">
                    <i class="fa fa-balance-scale card-icon"></i>
                    <h5>Perbandingan AHP</h5>
                    <p>Matriks perbandingan Saaty</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="ahp/index.php" style="text-decoration:none;">
                <div class="menu-card border-teal">
                    <i class="fa fa-chart-pie card-icon"></i>
                    <h5>Hasil Bobot AHP</h5>
                    <p>Uji konsistensi (CR ≤ 0.10)</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="perhitungan/index.php" style="text-decoration:none;">
                <div class="menu-card border-purple">
                    <i class="fa fa-calculator card-icon"></i>
                    <h5>Perhitungan WP</h5>
                    <p>Hitung Weighted Product</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="hasil/index.php" style="text-decoration:none;">
                <div class="menu-card border-teal">
                    <i class="fa fa-trophy card-icon"></i>
                    <h5>Hasil Akhir</h5>
                    <p>Perangkingan penerima PIP</p>
                </div>
            </a>
        </div>
    </div>

    <?php if ($total_pending > 0): ?>
    <div class="alert alert-warning" style="border-radius:12px;">
        <i class="fa fa-bell me-2"></i>Terdapat <strong><?php echo $total_pending; ?></strong> pendaftaran siswa menunggu verifikasi.
        <a href="alternatif/index.php" class="fw-bold ms-1">Lihat →</a>
    </div>
    <?php endif; ?>

    <!-- WELCOME BOX -->
    <div class="welcome-box">
        <h1>Selamat Datang, Admin!</h1>
        <p class="description">
            Sistem Pendukung Keputusan (SPK) ini mengombinasikan metode <strong>Analytical Hierarchy Process (AHP)</strong> untuk pembobotan kriteria secara teruji (CR ≤ 0.10) dan <strong>Weighted Product (WP)</strong> untuk pembobotan &amp; perangkingan alternatif siswa penerima <strong>Program Indonesia Pintar (PIP)</strong>. 
            Sistem mengevaluasi setiap alternatif berdasarkan 6 kriteria: Pekerjaan Orang Tua, Penghasilan Orang Tua, Jumlah Tanggungan, Status Kartu Kemiskinan, Nilai Akhir Semester, dan Hafalan Al-Qur'an.
        </p>
        <p class="school">
            Pondok Pesantren H. Maqbul Hasibuan — Sibuhuan, Padang Lawas, Sumatera Utara
        </p>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
</body>
</html>