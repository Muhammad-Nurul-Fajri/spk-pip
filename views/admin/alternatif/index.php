<?php
require_once '../../../config/koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fetch all siswa records
$siswa_list = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY kode_alternatif ASC");
if (!$siswa_list) {
    die("Error query: " . mysqli_error($koneksi));
}
$jumlah_data = mysqli_num_rows($siswa_list);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Alternatif (Siswa)</title>
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
.header-data {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.btn-add {
    background: #4caf50;
    color: white;
    border-radius: 10px;
    font-weight: bold;
    padding: 9px 18px;
    font-size: 14px;
    border: none;
    transition: 0.3s;
}
.btn-add:hover {
    background: #388e3c;
    color: white;
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
.btn-edit {
    background: #ffc107;
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 12px;
}
.btn-edit:hover {
    background: #e0a800;
    color: white;
}
.btn-delete {
    background: #dc3545;
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 12px;
}
.btn-delete:hover {
    background: #c82333;
    color: white;
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
        <img src="../../../public/assets/img/logo.png">
        <h4>Sistem Pendukung Keputusan Seleksi Penerima Bantuan PIP</h4>
        <p>Pondok Pesantren Haji Maqbul Hasibuan</p>
    </div>
    <ul class="menu">
        <li><a href="../dashboard.php"><i class="fa fa-house"></i> Dashboard</a></li>
        <li><a href="../kriteria/index.php"><i class="fa fa-list"></i> Data Kriteria</a></li>
        <li><a href="../sub_kriteria/index_subkriteria.php"><i class="fa fa-layer-group"></i> Data Sub Kriteria</a></li>
        <li><a href="index.php" class="active"><i class="fa fa-user-graduate"></i> Data Alternatif</a></li>
        <li><a href="../penilaian/index.php"><i class="fa fa-check-circle"></i> Data Penilaian</a></li>
        <li><a href="../perhitungan/index.php"><i class="fa fa-calculator"></i> Data Perhitungan</a></li>
        <li><a href="../hasil/index.php"><i class="fa fa-trophy"></i> Data Hasil Akhir</a></li>
        <li><a href="../user/index.php"><i class="fa fa-users"></i> Data User</a></li>
        <li><a href="#"><i class="fa fa-window-maximize"></i> Kelola Halaman</a></li>
        <li><a href="../../../logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-user-graduate"></i>
            <h4>Data Alternatif (Siswa)</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name">Admin</div>
        </div>
    </div>

    <div class="card-custom">
        <div class="header-data">
            <h5 class="m-0 text-muted">Daftar Alternatif Siswa</h5>
            <a href="tambah.php" class="btn btn-add">
                <i class="fa fa-plus"></i> Tambah Alternatif
            </a>
        </div>

        <div class="table-responsive">
            <?php if ($jumlah_data > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="8%">No</th>
                            <th width="20%">Kode Alternatif</th>
                            <th>Nama Siswa</th>
                            <th width="20%">Kelas</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($siswa_list)): 
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['kode_alternatif']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-edit me-1" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="hapus.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-delete" 
                                       title="Hapus"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus alternatif \'<?php echo htmlspecialchars($row['nama']); ?>\'?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-user-graduate mb-3" style="font-size: 40px; color: #ddd;"></i>
                    <p class="m-0">Belum ada data alternatif siswa.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
