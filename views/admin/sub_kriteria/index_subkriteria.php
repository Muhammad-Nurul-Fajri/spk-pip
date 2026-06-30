<?php
require_once '../../../config/koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil semua kriteria
$kriteria_list = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
if (!$kriteria_list) {
    die("Error query kriteria: " . mysqli_error($koneksi));
}

$total_kriteria = mysqli_num_rows($kriteria_list);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Sub Kriteria</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}
body{
    background: #f4f7f1;
    overflow-x: hidden;
}
.sidebar{
    width: 270px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: linear-gradient(180deg, #4caf50, #c6d166);
    color: white;
    overflow-y: auto;
    overflow-x: hidden;
}
.sidebar::-webkit-scrollbar{
    width: 0px;
}
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
.menu .active{
    background: rgba(255,255,255,0.18);
}
.content{
    margin-left: 270px;
    padding: 22px;
}
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
.card-custom{
    background: white;
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}
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
/* TABEL SAMA PERSIS DENGAN KRITERIA */
.table{
    vertical-align: middle;
    margin-bottom: 0;
}
/* HEADER TABEL HIJAU */
.table thead th{
    background: #4caf50;
    color: white;
    font-size: 14px;
    font-weight: bold;
    padding: 14px;
    text-align: center;
    vertical-align: middle;
}
/* ISI TABEL PUTIH */
.table tbody td{
    font-size: 14px;
    padding: 14px;
    text-align: center;
    vertical-align: middle;
    background: white;
}
.table tbody tr:hover td{
    background: #e8f5e9;
}
/* Nama sub kriteria rata kiri */
.table tbody td:nth-child(2) {
    text-align: left !important;
    padding-left: 20px;
}
/* Kolom No diperkecil */
.table th:first-child,
.table td:first-child {
    width: 5%;
    min-width: 40px;
}
/* Tombol edit dan hapus SAMA seperti kriteria */
.btn-edit{
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
    transition: 0.3s;
    font-size: 12px;
}
.btn-edit:hover{
    background: #e0a800;
    color: white;
}
.btn-delete{
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
    transition: 0.3s;
    font-size: 12px;
}
.btn-delete:hover{
    background: #c82333;
    color: white;
}
.badge-nilai{
    background: #e8f5e9;
    color: #2e7d32;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}
.kriteria-header{
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #e8f5e9;
    color: #2e7d32;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 15px;
}
.kriteria-title{
    font-size: 16px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 10px;
}
.kriteria-title i{
    font-size: 18px;
}
.kriteria-jenis{
    font-size: 13px;
    font-weight: normal;
    color: #666;
    margin-left: 8px;
}
.empty-state{
    text-align: center;
    padding: 30px 20px;
}
.empty-state i{
    font-size: 40px;
    color: #ddd;
    margin-bottom: 10px;
}
.empty-state p{
    color: #999;
    font-size: 14px;
}
@media(max-width: 900px){
    .sidebar{
        width: 100%;
        height: auto;
        position: relative;
    }
    .content{
        margin-left: 0;
    }
    .kriteria-header{
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
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
        <li><a href="index_subkriteria.php" class="active"><i class="fa fa-layer-group"></i> Data Sub Kriteria</a></li>
        <li><a href="../alternatif/index.php"><i class="fa fa-user-graduate"></i> Data Alternatif</a></li>
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
            <i class="fa fa-layer-group"></i>
            <h4>Data Sub Kriteria</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name">Admin</div>
        </div>
    </div>

    <?php 
    while ($k = mysqli_fetch_assoc($kriteria_list)): 
        $id_kriteria = $k['id'];
        $sub_query = "SELECT * FROM sub_kriteria WHERE id_kriteria = $id_kriteria ORDER BY id ASC";
        $sub_data = mysqli_query($koneksi, $sub_query);
        $jumlah_sub = mysqli_num_rows($sub_data);
    ?>
    <div class="card-custom">
        <div class="kriteria-header">
            <div class="kriteria-title">
                <i class="fa fa-list-check"></i>
                <?php echo htmlspecialchars($k['kode_kriteria']); ?> - <?php echo htmlspecialchars($k['nama_kriteria']); ?> 
                <span class="kriteria-jenis">(<?php echo $k['jenis']; ?>)</span>
            </div>
            <a href="tambah_subkriteria.php?id_kriteria=<?php echo $k['id']; ?>" class="btn btn-add">
                <i class="fa fa-plus"></i>
                Tambah Data
            </a>
        </div>
        <div class="table-responsive">
            <?php if ($jumlah_sub > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="8%">No</th>
                        <th>Nama Sub Kriteria</th>
                        <th width="15%">Nilai</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($sub_data)): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_sub']); ?></td>
                        <td><span class="badge-nilai"><?php echo $row['nilai']; ?></span></td>
                        <td>
                            <a href="edit_subkriteria.php?id=<?php echo $row['id']; ?>" class="btn btn-edit" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="hapus_subkriteria.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-delete" 
                               title="Hapus"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus sub kriteria \'<?php echo htmlspecialchars($row['nama_sub']); ?>\'?')">
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
                <p>Belum ada sub kriteria untuk kriteria ini.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>

</div>
</body>
</html>