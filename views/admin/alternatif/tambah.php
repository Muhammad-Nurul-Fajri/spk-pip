<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode = trim($_POST['kode_alternatif'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    if (empty($kode)||empty($nama)) { $pesan = 'Kode dan Nama wajib diisi!'; }
    else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO siswa (kode_alternatif, nama, kelas, status_pendaftaran) VALUES (?,?,?,'draft')");
        mysqli_stmt_bind_param($stmt, "sss", $kode, $nama, $kelas);
        if (mysqli_stmt_execute($stmt)) { header("Location: index.php"); exit; }
        else $pesan = 'Gagal: kode mungkin sudah ada.';
        mysqli_stmt_close($stmt);
    }
}
$page_title = 'Tambah Alternatif'; $active_menu = 'alternatif'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom"><div class="page-title"><i class="fa fa-user-plus"></i><h4>Tambah Alternatif</h4></div></div>
    <div class="card-custom">
        <?php if ($pesan): ?><div class="alert alert-danger" style="border-radius:10px;"><?php echo $pesan; ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label class="form-label fw-bold small">Kode Alternatif</label><input type="text" name="kode_alternatif" class="form-control" required placeholder="Contoh: A6"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Nama Siswa</label><input type="text" name="nama" class="form-control" required></div>
            <div class="mb-3"><label class="form-label fw-bold small">Kelas</label><input type="text" name="kelas" class="form-control"></div>
            <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i>Simpan</button>
            <a href="index.php" class="btn-batal ms-2"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
        </form>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>
