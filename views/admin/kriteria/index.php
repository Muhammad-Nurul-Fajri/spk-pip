<?php

require_once '../../../config/koneksi.php';

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data kriteria dari database
$data_kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id ASC");

// Cek jika query gagal
if (!$data_kriteria) {
    die("Error query: " . mysqli_error($koneksi) . "<br>Pastikan tabel 'kriteria' sudah dibuat di database.");
}

$jumlah_data = mysqli_num_rows($data_kriteria);

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Kriteria</title>

<!-- BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

/* =========================
   RESET
========================= */

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

/* =========================
   BODY
========================= */

body{
    background: #f4f7f1;
    overflow-x: hidden;
}

/* =========================
   SIDEBAR
========================= */

.sidebar{

    width: 270px;

    height: 100vh;

    position: fixed;

    left: 0;
    top: 0;

    background: linear-gradient(
        180deg,
        #4caf50,
        #c6d166
    );

    color: white;

    overflow-y: auto;
    overflow-x: hidden;
}

/* HILANGKAN SCROLLBAR */

.sidebar::-webkit-scrollbar{
    width: 0px;
}

/* =========================
   LOGO
========================= */

.logo{

    padding: 25px 20px;

    text-align: center;

    border-bottom: 1px solid rgba(255,255,255,0.15);
}

.logo img{

    width: 95px;
    height: 95px;

    object-fit: contain;

    margin-bottom: 12px;
}

.logo h4{

    font-size: 15px;

    font-weight: bold;

    line-height: 1.6;

    margin-bottom: 8px;
}

.logo p{

    font-size: 12px;

    margin: 0;

    opacity: 0.95;
}

/* =========================
   MENU
========================= */

.menu{

    padding: 18px 0;
    margin: 0;
}

.menu li{

    list-style: none;
}

.menu li a{

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px 24px;

    color: white;

    text-decoration: none;

    font-size: 14px;

    transition: 0.3s;
}

.menu li a:hover{

    background: rgba(255,255,255,0.15);
}

.menu li a i{

    width: 22px;

    text-align: center;
}

/* ACTIVE MENU */

.menu .active{

    background: rgba(255,255,255,0.18);
}

/* =========================
   CONTENT
========================= */

.content{

    margin-left: 270px;

    padding: 22px;
}

/* =========================
   NAVBAR
========================= */

.navbar-custom{

    background: white;

    padding: 16px 22px;

    border-radius: 18px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow: 0 4px 12px rgba(0,0,0,0.05);

    margin-bottom: 25px;
}

/* =========================
   TITLE
========================= */

.page-title{

    display: flex;

    align-items: center;

    gap: 12px;
}

.page-title i{

    font-size: 22px;

    color: #4caf50;
}

.page-title h4{

    margin: 0;

    font-size: 23px;

    font-weight: bold;

    color: #333;
}

/* =========================
   USER
========================= */

.user-box{

    display: flex;

    align-items: center;

    gap: 12px;
}

.user-icon{

    width: 42px;
    height: 42px;

    border-radius: 50%;

    background: #4caf50;

    display: flex;

    justify-content: center;

    align-items: center;

    color: white;

    font-size: 18px;
}

.user-name{

    font-size: 14px;

    font-weight: bold;

    color: #333;
}

/* =========================
   CARD
========================= */

.card-custom{

    background: white;

    border-radius: 18px;

    padding: 24px;

    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* =========================
   HEADER DATA
========================= */

.header-data{

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 22px;
}

.header-data h5{

    margin: 0;

    font-size: 20px;

    font-weight: bold;

    color: #333;
}

/* =========================
   BUTTON
========================= */

.btn-add{

    background: #4caf50;

    color: white;

    border: none;

    padding: 10px 18px;

    border-radius: 10px;

    font-size: 14px;

    transition: 0.3s;

    text-decoration: none;

    display: inline-flex;

    align-items: center;

    gap: 8px;
}

.btn-add:hover{

    background: #43a047;

    color: white;
}

/* =========================
   TABLE
========================= */

.table{

    vertical-align: middle;

    margin-bottom: 0;
}

.table thead{

    background: #4caf50;

    color: white;
}

.table thead th{

    font-size: 14px;

    font-weight: bold;

    padding: 14px;
}

.table tbody td{

    font-size: 14px;

    padding: 14px;
}

/* =========================
   BUTTON AKSI
========================= */

.btn-edit{

    background: #ffc107;

    color: white;

    border: none;

    width: 36px;
    height: 36px;

    border-radius: 8px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    text-decoration: none;

    transition: 0.3s;
}

.btn-edit:hover{

    background: #e0a800;

    color: white;
}

.btn-delete{

    background: #dc3545;

    color: white;

    border: none;

    width: 36px;
    height: 36px;

    border-radius: 8px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    text-decoration: none;

    transition: 0.3s;
}

.btn-delete:hover{

    background: #c82333;

    color: white;
}

/* =========================
   BOBOT BADGE
========================= */

.bobot-badge{
    background: #e8f5e9;
    color: #2e7d32;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}

/* =========================
   EMPTY STATE
========================= */

.empty-state{

    text-align: center;

    padding: 50px 20px;
}

.empty-state i{

    font-size: 50px;

    color: #ddd;

    margin-bottom: 15px;
}

.empty-state p{

    color: #999;

    font-size: 15px;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width: 900px){

    .sidebar{

        width: 100%;

        height: auto;

        position: relative;
    }

    .content{

        margin-left: 0;
    }

    .header-data{

        flex-direction: column;

        gap: 15px;

        align-items: start;
    }

}


/* Header tabel hijau - semua tengah */
.table thead th {
    background: #4caf50;
    color: white;
    text-align: center;
    vertical-align: middle;
}

/* Isi tabel - default tengah */
.table tbody td {
    text-align: center;
    vertical-align: middle;
    background: white;
}

/* Nama kriteria rata kiri */
.table tbody td:nth-child(3) {
    text-align: left !important;
    padding-left: 20px;
}

/* Hover effect */
.table tbody tr:hover td {
    background: #e8f5e9;
}

/* Kolom No diperkecil */
.table th:first-child,
.table td:first-child {
    width: 5%;
    min-width: 40px;
}

/* Tombol edit dan hapus diperkecil */
.btn-edit, .btn-delete {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    font-size: 12px;
}
</style>

</head>

<body>

<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <!-- LOGO -->

    <div class="logo">

        <img src="../../../public/assets/img/logo.png">

        <h4>
            Sistem Pendukung Keputusan
            Seleksi Penerima Bantuan PIP
        </h4>

        <p>
            Pondok Pesantren Haji Maqbul Hasibuan
        </p>

    </div>

    <!-- MENU -->

    <ul class="menu">

        <li>
            <a href="../dashboard.php">
                <i class="fa fa-house"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="index.php" class="active">
                <i class="fa fa-list"></i>
                Data Kriteria
            </a>
        </li>

        <li>
            <a href="../sub_kriteria/index_subkriteria.php">
                <i class="fa fa-layer-group"></i>
                Data Sub Kriteria
            </a>
        </li>

        <li>
            <a href="../alternatif/index.php">
                <i class="fa fa-user-graduate"></i>
                Data Alternatif
            </a>
        </li>

        <li>
            <a href="../penilaian/index.php">
                <i class="fa fa-check-circle"></i>
                Data Penilaian
            </a>
        </li>

        <li>
            <a href="../perhitungan/index.php">
                <i class="fa fa-calculator"></i>
                Data Perhitungan
            </a>
        </li>

        <li>
            <a href="../hasil/index.php">
                <i class="fa fa-trophy"></i>
                Data Hasil Akhir
            </a>
        </li>

        <li>
            <a href="../user/index.php">
                <i class="fa fa-users"></i>
                Data User
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fa fa-window-maximize"></i>
                Kelola Halaman
            </a>
        </li>

        <li>
            <a href="../../../logout.php">
                <i class="fa fa-right-from-bracket"></i>
                Logout
            </a>
        </li>

    </ul>

</div>

<!-- =========================
     CONTENT
========================= -->

<div class="content">

    <!-- NAVBAR -->

    <div class="navbar-custom">

        <div class="page-title">

            <i class="fa fa-list"></i>

            <h4>
                Data Kriteria
            </h4>

        </div>

        <div class="user-box">

            <div class="user-icon">

                <i class="fa fa-user"></i>

            </div>

            <div class="user-name">

                Admin

            </div>

        </div>

    </div>

    <!-- CARD DATA -->

    <div class="card-custom">

        <!-- HEADER -->

        <div class="header-data">

            <h5>
                Tabel Data Kriteria
            </h5>

            <a href="tambah.php" class="btn btn-add">

                <i class="fa fa-plus"></i>

                Tambah Data

            </a>

        </div>

        <!-- TABLE -->

        <div class="table-responsive">

            <?php if ($jumlah_data > 0): ?>

            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th width="8%">No</th>
                        <th>Kode Kriteria</th>
                        <th>Nama Kriteria</th>
                        <th>Jenis</th>
                        <th>Bobot</th>
                        <th width="15%">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($data_kriteria)): 
                    ?>

                    <tr>

                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['kode_kriteria']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['nama_kriteria']); ?></td>
                        <td>
                            <?php if ($row['jenis'] == 'cost'): ?>
                                <span style="color: #dc3545; font-weight: bold;">Cost</span>
                            <?php else: ?>
                                <span style="color: #4caf50; font-weight: bold;">Benefit</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="bobot-badge"><?php echo $row['bobot']; ?>%</span></td>

                        <td>

                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-edit" title="Edit">

                                <i class="fa fa-edit"></i>

                            </a>

                            <a href="hapus.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-delete" 
                               title="Hapus"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus kriteria \'<?php echo htmlspecialchars($row['nama_kriteria']); ?>\'?')">

                                <i class="fa fa-trash"></i>

                            </a>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

            <?php else: ?>

            <div class="empty-state">

                <i class="fa fa-inbox"></i>

                <p>Belum ada data kriteria.<br>Silakan klik tombol <strong>Tambah Data</strong> untuk menambahkan.</p>

            </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>