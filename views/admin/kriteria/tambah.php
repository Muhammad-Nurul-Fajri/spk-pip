<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode = trim($_POST['kode_kriteria'] ?? '');
    $nama = trim($_POST['nama_kriteria'] ?? '');
    $bobot = intval($_POST['bobot'] ?? 0);
    $jenis = $_POST['jenis'] ?? 'benefit';
    if (empty($kode)||empty($nama)||$bobot<=0) $pesan = 'Semua field wajib diisi!';
    else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO kriteria (kode_kriteria,nama_kriteria,bobot,jenis) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssis", $kode, $nama, $bobot, $jenis);
        if (mysqli_stmt_execute($stmt)) { header("Location: index.php"); exit; }
        else $pesan = 'Gagal: kode mungkin sudah ada.';
        mysqli_stmt_close($stmt);
    }
}
$page_title = 'Tambah Kriteria'; $active_menu = 'kriteria'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom"><div class="page-title"><i class="fa fa-plus"></i><h4>Tambah Kriteria</h4></div></div>
    <div class="card-custom">
        <?php if ($pesan): ?><div class="alert alert-danger" style="border-radius:10px;"><?php echo $pesan; ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label class="form-label fw-bold small">Kode Kriteria</label><input type="text" name="kode_kriteria" class="form-control" required placeholder="Contoh: C7"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Nama Kriteria</label><input type="text" name="nama_kriteria" class="form-control" required></div>
            <div class="mb-3"><label class="form-label fw-bold small">Bobot</label><input type="number" name="bobot" class="form-control" min="1" required></div>
            <div class="mb-3"><label class="form-label fw-bold small">Jenis</label>
                <select name="jenis" class="form-select"><option value="benefit">Benefit</option><option value="cost">Cost</option></select>
            </div>
            <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i>Simpan</button>
            <a href="index.php" class="btn-batal ms-2"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
        </form>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>