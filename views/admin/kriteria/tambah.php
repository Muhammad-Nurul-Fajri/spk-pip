<?php

require_once '../../../config/koneksi.php';

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan_error  = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $kode_kriteria = trim($_POST['kode_kriteria'] ?? '');
    $nama_kriteria = trim($_POST['nama_kriteria'] ?? '');
    $jenis         = $_POST['jenis'] ?? '';
    $bobot         = trim($_POST['bobot'] ?? '');
    
    // Validasi
    if (empty($kode_kriteria)) {
        $pesan_error = 'Kode kriteria wajib diisi!';
    } elseif (empty($nama_kriteria)) {
        $pesan_error = 'Nama kriteria wajib diisi!';
    } elseif (empty($jenis)) {
        $pesan_error = 'Jenis wajib dipilih!';
    } elseif ($bobot === '') {
        $pesan_error = 'Bobot wajib diisi!';
    } elseif (!is_numeric($bobot) || $bobot < 0 || $bobot > 100) {
        $pesan_error = 'Bobot harus angka 0 - 100!';
    } else {
        
        // Cek duplikat kode
        $cek_kode = mysqli_query($koneksi, "SELECT id FROM kriteria WHERE kode_kriteria = '$kode_kriteria'");
        if (!$cek_kode) {
            $pesan_error = 'Error: ' . mysqli_error($koneksi);
        } elseif (mysqli_num_rows($cek_kode) > 0) {
            $pesan_error = 'Kode kriteria sudah ada!';
        } else {
            
            // Cek duplikat nama
            $cek_nama = mysqli_query($koneksi, "SELECT id FROM kriteria WHERE nama_kriteria = '$nama_kriteria'");
            if (!$cek_nama) {
                $pesan_error = 'Error: ' . mysqli_error($koneksi);
            } elseif (mysqli_num_rows($cek_nama) > 0) {
                $pesan_error = 'Nama kriteria sudah ada!';
            } else {
                
                // Simpan ke database (bobot tanpa %, hanya angka)
                $simpan = mysqli_query($koneksi, 
                    "INSERT INTO kriteria (kode_kriteria, nama_kriteria, jenis, bobot) 
                     VALUES ('$kode_kriteria', '$nama_kriteria', '$jenis', '$bobot')"
                );
                
                if ($simpan) {
                    header('Location: index.php');
                    exit;
                } else {
                    $pesan_error = 'Gagal menyimpan: ' . mysqli_error($koneksi);
                }
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

<title>Tambah Data Kriteria</title>

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
    border-color: #4caf50;
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.15);
}

/* INPUT GROUP UNTUK PERSEN */
.input-group-bobot{
    position: relative;
}

.input-group-bobot .form-control{
    border-right: none;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.input-group-text-persen{
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-left: none;
    border-radius: 0 10px 10px 0;
    padding: 12px 16px;
    color: #555;
    font-size: 14px;
    font-weight: bold;
    display: flex;
    align-items: center;
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

.btn-simpan{
    background: #4caf50;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: bold;
    transition: 0.3s;
}

.btn-simpan:hover{
    background: #43a047;
    color: white;
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

        <h4>
            Sistem Pendukung Keputusan
            Seleksi Penerima Bantuan PIP
        </h4>

        <p>
            Pondok Pesantren Haji Maqbul Hasibuan
        </p>

    </div>

    <ul class="menu">

        <li><a href="../dashboard.php"><i class="fa fa-house"></i> Dashboard</a></li>
        <li><a href="index.php" class="active"><i class="fa fa-list"></i> Data Kriteria</a></li>
        <li><a href="../sub_kriteria/index_subkriteria.php"><i class="fa fa-layer-group"></i> Data Sub Kriteria</a></li>
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

            <i class="fa fa-plus-circle"></i>

            <h4>Tambah Data Kriteria</h4>

        </div>

        <div class="user-box">

            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name">Admin</div>

        </div>

    </div>

    <div class="card-custom">

        <div class="card-header-custom">
            <h5>Form Tambah Data Kriteria</h5>
        </div>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-error-custom alert-custom mb-3">
                <i class="fa fa-exclamation-circle me-2"></i><?php echo $pesan_error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Kriteria <span class="required">*</span></label>
                    <input type="text" name="kode_kriteria" class="form-control" placeholder="Contoh: C1" value="<?php echo htmlspecialchars($kode_kriteria ?? ''); ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Kriteria <span class="required">*</span></label>
                    <input type="text" name="nama_kriteria" class="form-control" placeholder="Masukkan nama kriteria" value="<?php echo htmlspecialchars($nama_kriteria ?? ''); ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis <span class="required">*</span></label>
                    <select name="jenis" class="form-select" required>
                        <option value="" disabled <?php echo empty($jenis) ? 'selected' : ''; ?>>-- Pilih Jenis --</option>
                        <option value="cost" <?php echo ($jenis ?? '') == 'cost' ? 'selected' : ''; ?>>Cost</option>
                        <option value="benefit" <?php echo ($jenis ?? '') == 'benefit' ? 'selected' : ''; ?>>Benefit</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Bobot <span class="required">*</span></label>
                    <div class="d-flex">
                        <input type="number" name="bobot" class="form-control" placeholder="Masukkan bobot" step="1" min="0" max="100" value="<?php echo htmlspecialchars($bobot ?? ''); ?>" required>
                        <span class="input-group-text-persen">%</span>
                    </div>
                    <small class="text-muted" style="font-size: 12px;">Masukkan angka 0 - 100</small>
                </div>

            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn-simpan"><i class="fa fa-save me-2"></i>Simpan</button>
                <a href="index.php" class="btn-batal"><i class="fa fa-arrow-left me-2"></i>Kembali</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>