<?php
session_start();
require_once '../../config/koneksi.php';

// Verification role
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'ketua_yayasan') {
    header("Location: ../../login.php");
    exit;
}

// Fetch all ranking results
$query_ranking = mysqli_query($koneksi, "
    SELECT h.*, s.nama, s.kode_alternatif, s.kelas
    FROM hasil_wp h
    JOIN siswa s ON h.id_siswa = s.id
    ORDER BY h.ranking ASC
");
$jumlah_data = mysqli_num_rows($query_ranking);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Ketua Yayasan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}
body {
    background: #f4f7f1;
    overflow-x: hidden;
}
.sidebar {
    width: 270px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: linear-gradient(180deg, #4caf50, #c6d166);
    color: white;
    overflow-y: auto;
}
.logo {
    padding: 25px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}
.logo img {
    width: 95px;
    height: 95px;
    object-fit: contain;
    margin-bottom: 12px;
}
.logo h4 {
    font-size: 15px;
    font-weight: bold;
    line-height: 1.6;
    margin-bottom: 8px;
}
.logo p {
    font-size: 12px;
    margin: 0;
    opacity: 0.95;
}
.menu {
    padding: 18px 0;
    margin: 0;
}
.menu li {
    list-style: none;
}
.menu li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 24px;
    color: white;
    text-decoration: none;
    font-size: 14px;
    transition: 0.3s;
}
.menu li a:hover, .menu li a.active {
    background: rgba(255,255,255,0.15);
}
.menu li a i {
    width: 22px;
    text-align: center;
}
.content {
    margin-left: 270px;
    padding: 22px;
    min-height: 100vh;
}
.navbar-custom {
    background: white;
    padding: 16px 22px;
    border-radius: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}
.page-title {
    display: flex;
    align-items: center;
    gap: 12px;
}
.page-title i {
    font-size: 22px;
    color: #4caf50;
}
.page-title h4 {
    margin: 0;
    font-size: 23px;
    font-weight: bold;
    color: #333;
}
.user-box {
    display: flex;
    align-items: center;
    gap: 12px;
}
.user-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #e8f5e9;
    color: #4caf50;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 16px;
    border: 1px solid #c8e6c9;
}
.user-name {
    font-size: 14px;
    font-weight: bold;
    color: #555;
}
.card-custom {
    background: white;
    border-radius: 18px;
    border: none;
    box-shadow: 0 8px 24px rgba(0,0,0,0.04);
    padding: 24px;
    margin-bottom: 25px;
}
.table thead th {
    background: #4caf50;
    color: white;
    text-align: center;
    vertical-align: middle;
}
.table tbody td {
    text-align: center;
    vertical-align: middle;
    background: white;
}
.table tbody td:nth-child(3) {
    text-align: left !important;
    padding-left: 20px;
}
.table tbody tr:hover td {
    background: #e8f5e9;
}
.btn-print {
    background: #ffc107;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: bold;
    transition: 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-print:hover {
    background: #e0a800;
    color: white;
}
@media print {
    .sidebar, .navbar-custom, .btn-print, .no-print {
        display: none !important;
    }
    .content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card-custom {
        box-shadow: none !important;
        padding: 0 !important;
    }
}
@media(max-width: 900px){
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    .content {
        margin-left: 0;
    }
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../../public/assets/img/logo.png">
        <h4>Sistem Pendukung Keputusan Seleksi Penerima Bantuan PIP</h4>
        <p>Pondok Pesantren Haji Maqbul Hasibuan</p>
    </div>
    <ul class="menu">
        <li><a href="dashboard.php" class="active"><i class="fa fa-house"></i> Dashboard</a></li>
        <li><a href="../../logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-award"></i>
            <h4>Laporan Hasil Akhir</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name">Ketua Yayasan</div>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Hasil Perangkingan Penerima PIP</h5>
                <p class="text-muted mb-0 small">Berdasarkan hasil analisis metode Weighted Product (WP)</p>
            </div>
            <?php if ($jumlah_data > 0): ?>
                <button onclick="window.print()" class="btn-print no-print">
                    <i class="fa fa-print"></i> Cetak Laporan
                </button>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <?php if ($jumlah_data > 0): ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="8%">Ranking</th>
                            <th width="15%">Kode Alternatif</th>
                            <th>Nama Siswa</th>
                            <th width="20%">Kelas</th>
                            <th width="15%">Nilai Preferensi (V)</th>
                            <th width="15%">Status Kelayakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query_ranking)): ?>
                            <tr>
                                <td>
                                    <?php if ($row['ranking'] == 1): ?>
                                        <span class="badge bg-danger fs-6"><i class="fa fa-trophy"></i> 1</span>
                                    <?php elseif ($row['ranking'] == 2): ?>
                                        <span class="badge bg-warning text-dark fs-6">2</span>
                                    <?php elseif ($row['ranking'] == 3): ?>
                                        <span class="badge bg-success fs-6">3</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo $row['ranking']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['kode_alternatif']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                <td><?php echo number_format($row['nilai_v'], 5); ?></td>
                                <td>
                                    <?php if ($row['ranking'] <= 3): ?>
                                        <span class="badge bg-success p-2">Sangat Layak (Penerima)</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark p-2">Layak (Cadangan)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa fa-triangle-exclamation text-warning mb-3" style="font-size: 50px;"></i>
                    <h5>Data Belum Tersedia</h5>
                    <p class="text-muted">Administrator belum memproses perhitungan kelayakan saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
