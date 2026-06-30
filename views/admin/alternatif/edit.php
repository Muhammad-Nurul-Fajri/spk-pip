<?php
require_once '../../../config/koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan_error = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id = $id");
if (!$query || mysqli_num_rows($query) == 0) {
    header('Location: index.php');
    exit;
}
$data = mysqli_fetch_assoc($query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_alternatif = trim($_POST['kode_alternatif'] ?? '');
    $nama            = trim($_POST['nama'] ?? '');
    $kelas           = trim($_POST['kelas'] ?? '');

    if (empty($kode_alternatif)) {
        $pesan_error = 'Kode alternatif wajib diisi!';
    } elseif (empty($nama)) {
        $pesan_error = 'Nama siswa wajib diisi!';
    } elseif (empty($kelas)) {
        $pesan_error = 'Kelas wajib diisi!';
    } else {
        $cek = mysqli_query($koneksi, "SELECT id FROM siswa WHERE kode_alternatif = '$kode_alternatif' AND id != $id");
        if (mysqli_num_rows($cek) > 0) {
            $pesan_error = 'Kode alternatif sudah ada!';
        } else {
            $update = mysqli_query($koneksi,
                "UPDATE siswa SET kode_alternatif='$kode_alternatif', nama='$nama', kelas='$kelas' WHERE id=$id"
            );
            if ($update) {
                header('Location: index.php');
                exit;
            } else {
                $pesan_error = 'Gagal mengupdate: ' . mysqli_error($koneksi);
            }
        }
    }
    $data['kode_alternatif'] = $kode_alternatif;
    $data['nama'] = $nama;
    $data['kelas'] = $kelas;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Alternatif</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
body { background: #f4f7f1; overflow-x: hidden; }
.sidebar { width: 270px; height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, #4caf50, #c6d166); color: white; overflow-y: auto; }
.sidebar::-webkit-scrollbar { width: 0px; }
.logo { padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.15); }
.logo img { width: 95px; height: 95px; object-fit: contain; margin-bottom: 12px; }
.logo h4 { font-size: 15px; font-weight: bold; line-height: 1.6; margin-bottom: 8px; }
.logo p { font-size: 12px; margin: 0; opacity: 0.95; }
.menu { padding: 18px 0; margin: 0; }
.menu li { list-style: none; }
.menu li a { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: white; text-decoration: none; font-size: 14px; transition: 0.3s; }
.menu li a:hover, .menu li a.active { background: rgba(255,255,255,0.15); }
.menu li a i { width: 22px; text-align: center; }
.content { margin-left: 270px; padding: 22px; min-height: 100vh; }
.navbar-custom { background: white; padding: 16px 22px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 25px; }
.page-title { display: flex; align-items: center; gap: 12px; }
.page-title i { font-size: 22px; color: #4caf50; }
.page-title h4 { margin: 0; font-size: 23px; font-weight: bold; color: #333; }
.user-box { display: flex; align-items: center; gap: 12px; }
.user-icon { width: 38px; height: 38px; border-radius: 50%; background: #e8f5e9; color: #4caf50; display: flex; justify-content: center; align-items: center; font-size: 16px; border: 1px solid #c8e6c9; }
.user-name { font-size: 14px; font-weight: bold; color: #555; }
.card-custom { background: white; border-radius: 18px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.04); padding: 24px; margin-bottom: 25px; }
.form-control { height: 42px; border-radius: 10px; border: 1px solid #d6e4d3; background: #fafdf8; padding-left: 14px; font-size: 13px; }
.form-control:focus { border-color: #C6D166; box-shadow: 0 0 0 0.15rem rgba(76,175,80,0.15); background: white; }
.btn-simpan { background: linear-gradient(135deg, #4caf50, #C6D166); color: white; border: none; border-radius: 10px; padding: 10px 28px; font-weight: bold; transition: 0.3s; }
.btn-simpan:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(46,125,50,0.2); color: white; }
.btn-batal { background: #6c757d; color: white; border: none; border-radius: 10px; padding: 10px 28px; font-weight: bold; text-decoration: none; transition: 0.3s; }
.btn-batal:hover { background: #5a6268; color: white; }
@media(max-width: 900px){ .sidebar { width: 100%; height: auto; position: relative; } .content { margin-left: 0; } }
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
            <h4>Edit Alternatif</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name">Admin</div>
        </div>
    </div>

    <div class="card-custom">
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger" style="border-radius: 10px; font-size: 14px;">
                <?php echo $pesan_error; ?>
            </div>
        <?php endif; ?>

        <form action="edit.php?id=<?php echo $id; ?>" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Kode Alternatif</label>
                <input type="text" name="kode_alternatif" class="form-control" required
                       value="<?php echo htmlspecialchars($data['kode_alternatif']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Siswa</label>
                <input type="text" name="nama" class="form-control" required
                       value="<?php echo htmlspecialchars($data['nama']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Kelas</label>
                <input type="text" name="kelas" class="form-control" required
                       value="<?php echo htmlspecialchars($data['kelas']); ?>">
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn-simpan">
                    <i class="fa fa-save me-1"></i> Update
                </button>
                <a href="index.php" class="btn-batal">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
