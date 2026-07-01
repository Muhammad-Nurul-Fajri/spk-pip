<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$id = intval($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT * FROM siswa WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$siswa) { header("Location: index.php"); exit; }

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode = trim($_POST['kode_alternatif'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    if (empty($kode)||empty($nama)) { $pesan = 'Kode dan Nama wajib!'; }
    else {
        $stmt2 = mysqli_prepare($koneksi, "UPDATE siswa SET kode_alternatif=?, nama=?, kelas=? WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "sssi", $kode, $nama, $kelas, $id);
        if (mysqli_stmt_execute($stmt2)) { header("Location: index.php"); exit; }
        else $pesan = 'Gagal: ' . mysqli_error($koneksi);
        mysqli_stmt_close($stmt2);
    }
}
$page_title = 'Edit Alternatif'; $active_menu = 'alternatif'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom"><div class="page-title"><i class="fa fa-user-edit"></i><h4>Edit Alternatif</h4></div></div>
    <div class="card-custom">
        <?php if ($pesan): ?><div class="alert alert-danger" style="border-radius:10px;"><?php echo $pesan; ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label class="form-label fw-bold small">Kode Alternatif</label><input type="text" name="kode_alternatif" class="form-control" required value="<?php echo htmlspecialchars($siswa['kode_alternatif']); ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Nama Siswa</label><input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($siswa['nama']); ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Kelas</label><input type="text" name="kelas" class="form-control" value="<?php echo htmlspecialchars($siswa['kelas'] ?? ''); ?>"></div>
            <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i>Update</button>
            <a href="index.php" class="btn-batal ms-2"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
        </form>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>
