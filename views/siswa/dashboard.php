<?php
session_start();
require_once '../../config/koneksi.php';

// Verification role
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'siswa') {
    header("Location: ../../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Get student profile
$query_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id = $id_user");
$siswa = mysqli_fetch_assoc($query_siswa);

// Get student penilaian values
$query_penilaian = mysqli_query($koneksi, "
    SELECT p.*, k.kode_kriteria, k.nama_kriteria, k.jenis
    FROM penilaian p
    JOIN kriteria k ON p.id_kriteria = k.id
    WHERE p.id_siswa = $id_user
    ORDER BY k.kode_kriteria ASC
");

// Get student ranking
$query_ranking = mysqli_query($koneksi, "
    SELECT h.*, (SELECT COUNT(*) FROM hasil_wp) as total_peserta
    FROM hasil_wp h
    WHERE h.id_siswa = $id_user
");
$hasil = mysqli_fetch_assoc($query_ranking);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Siswa</title>
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
.welcome-card {
    background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
    border-left: 5px solid #4caf50;
}
.rank-badge {
    font-size: 32px;
    font-weight: bold;
    color: #4caf50;
    display: inline-block;
    padding: 10px 20px;
    background: #e8f5e9;
    border-radius: 15px;
    margin-bottom: 10px;
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
            <i class="fa fa-user-graduate"></i>
            <h4>Dashboard Siswa</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($siswa['nama']); ?></div>
        </div>
    </div>

    <!-- WELCOME CARD -->
    <div class="card-custom welcome-card">
        <h5>Selamat Datang, <strong><?php echo htmlspecialchars($siswa['nama']); ?></strong>!</h5>
        <p class="mb-0 text-muted">Berikut adalah informasi penilaian dan status penerimaan bantuan Program Indonesia Pintar (PIP) Anda.</p>
    </div>

    <div class="row">
        <!-- STATUS SELEKSI -->
        <div class="col-md-6">
            <div class="card-custom text-center h-100">
                <h5 class="mb-4">Status Penerimaan Bantuan PIP</h5>
                
                <?php if ($hasil): ?>
                    <div class="rank-badge">
                        Peringkat <?php echo $hasil['ranking']; ?> / <?php echo $hasil['total_peserta']; ?>
                    </div>
                    <p class="text-muted">Nilai Preferensi (V): <strong><?php echo number_format($hasil['nilai_v'], 5); ?></strong></p>
                    
                    <!-- Lolos Seleksi (Misal Top 3) -->
                    <?php if ($hasil['ranking'] <= 3): ?>
                        <div class="alert alert-success mt-3" role="alert">
                            <i class="fa fa-circle-check me-2"></i>
                            Selamat! Anda terpilih sebagai salah satu penerima bantuan PIP berdasarkan hasil perhitungan Weighted Product.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3" role="alert">
                            <i class="fa fa-triangle-exclamation me-2"></i>
                            Saat ini Anda berada di peringkat cadangan. Bantuan akan diberikan jika kuota bertambah atau ada penerima di atas yang mengundurkan diri.
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="py-4">
                        <i class="fa fa-clock text-warning mb-3" style="font-size: 50px;"></i>
                        <p class="lead">Perhitungan Belum Selesai</p>
                        <p class="text-muted">Hasil akhir seleksi belum dipublikasikan oleh administrator. Silakan cek secara berkala.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DETAIL DATA DIRI -->
        <div class="col-md-6">
            <div class="card-custom h-100">
                <h5 class="mb-4">Profil & Kriteria Penilaian</h5>
                <table class="table table-borderless mb-4">
                    <tr>
                        <td width="35%" class="text-muted">Nama Lengkap</td>
                        <td>: <strong><?php echo htmlspecialchars($siswa['nama']); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kode Alternatif</td>
                        <td>: <span class="badge bg-secondary"><?php echo htmlspecialchars($siswa['kode_alternatif']); ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kelas</td>
                        <td>: <?php echo htmlspecialchars($siswa['kelas']); ?></td>
                    </tr>
                </table>

                <h6 class="border-bottom pb-2 mb-3">Nilai Parameter Kriteria:</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Kriteria</th>
                                <th class="text-center">Nilai Parameter</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($query_penilaian)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_kriteria']); ?> (<?php echo $row['kode_kriteria']; ?>)</td>
                                    <td class="text-center"><strong><?php echo $row['nilai']; ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
