<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../../../config/koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan_error = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data sub kriteria + join kriteria
$query = "SELECT sk.*, k.kode_kriteria, k.nama_kriteria 
          FROM sub_kriteria sk 
          LEFT JOIN kriteria k ON sk.id_kriteria = k.id 
          WHERE sk.id = $id";
$data = mysqli_query($koneksi, $query);

if (!$data || mysqli_num_rows($data) == 0) {
    header('Location: index_subkriteria.php');
    exit;
}

$row = mysqli_fetch_assoc($data);

// Ambil semua kriteria untuk dropdown
$kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kriteria = intval($_POST['id_kriteria'] ?? 0);
    $nama_sub = mysqli_real_escape_string($koneksi, trim($_POST['nama_sub'] ?? ''));
    $nilai = trim($_POST['nilai'] ?? '');

    if ($id_kriteria <= 0) {
        $pesan_error = 'Kriteria wajib dipilih!';
    } elseif (empty($nama_sub)) {
        $pesan_error = 'Nama sub kriteria wajib diisi!';
    } elseif ($nilai === '' || !is_numeric($nilai) || $nilai < 0) {
        $pesan_error = 'Nilai harus angka positif!';
    } else {
        $cek = mysqli_query($koneksi, "SELECT id FROM sub_kriteria 
            WHERE id_kriteria = $id_kriteria AND nama_sub = '$nama_sub' AND id != $id");

        if (mysqli_num_rows($cek) > 0) {
            $pesan_error = 'Nama sub kriteria sudah ada untuk kriteria ini!';
        } else {
            $update = mysqli_query($koneksi, 
                "UPDATE sub_kriteria 
                 SET id_kriteria = $id_kriteria, nama_sub = '$nama_sub', nilai = '$nilai' 
                 WHERE id = $id");

            if ($update) {
                header('Location: index_subkriteria.php');
                exit;
            } else {
                $pesan_error = 'Gagal update: ' . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Data Sub Kriteria</title>
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
    color: #ffc107;
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
}
.card-header-custom{
    margin-bottom: 22px;
}
.card-header-custom h5{
    margin: 0;
    font-size: 20px;
    font-weight: bold;
    color: #333;
}
.form-label{
    font-size: 14px;
    font-weight: bold;
    color: #555;
    margin-bottom: 8px;
}
.form-label .required{
    color: #dc3545;
}
.form-control, .form-select{
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
}
.form-control:focus, .form-select:focus{
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.15);
}
.alert-custom{
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
}
.alert-error-custom{
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.btn-update{
    background: #ffc107;
    color: #333;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: bold;
    transition: 0.3s;
}
.btn-update:hover{
    background: #e0a800;
    color: #333;
}
.btn-batal{
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: bold;
    transition: 0.3s;
    text-decoration: none;
}
.btn-batal:hover{
    background: #5a6268;
    color: white;
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
            <i class="fa fa-pen-to-square"></i>
            <h4>Edit Data Sub Kriteria</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name">Admin</div>
        </div>
    </div>
    <div class="card-custom">
        <div class="card-header-custom">
            <h5>Form Edit Data Sub Kriteria</h5>
        </div>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-error-custom alert-custom mb-3">
                <i class="fa fa-exclamation-circle me-2"></i><?php echo $pesan_error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kriteria <span class="required">*</span></label>
                    <select name="id_kriteria" class="form-select" required>
                        <option value="" disabled>-- Pilih Kriteria --</option>
                        <?php while($k = mysqli_fetch_assoc($kriteria)): ?>
                        <option value="<?php echo $k['id']; ?>" <?php echo ($k['id'] == $row['id_kriteria']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($k['kode_kriteria']); ?> - <?php echo htmlspecialchars($k['nama_kriteria']); ?> (<?php echo $k['jenis']; ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Sub Kriteria <span class="required">*</span></label>
                    <input type="text" name="nama_sub" class="form-control" value="<?php echo htmlspecialchars($row['nama_sub']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai <span class="required">*</span></label>
                    <input type="number" name="nilai" class="form-control" value="<?php echo $row['nilai']; ?>" step="1" min="0" required>
                    <small class="text-muted" style="font-size: 12px;">Nilai untuk perhitungan WP (contoh: 1-5)</small>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn-update"><i class="fa fa-rotate me-2"></i>Update</button>
                <a href="index_subkriteria.php" class="btn-batal"><i class="fa fa-arrow-left me-2"></i>Kembali</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>