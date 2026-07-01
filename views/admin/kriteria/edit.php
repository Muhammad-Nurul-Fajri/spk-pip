<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$id = intval($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT * FROM kriteria WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt);
$kriteria = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
if (!$kriteria) { header("Location: index.php"); exit; }
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode = trim($_POST['kode_kriteria'] ?? '');
    $nama = trim($_POST['nama_kriteria'] ?? '');
    $bobot = intval($_POST['bobot'] ?? 0);
    $jenis = $_POST['jenis'] ?? 'benefit';
    if (empty($kode)||empty($nama)||$bobot<=0) $pesan = 'Semua field wajib!';
    else {
        $stmt2 = mysqli_prepare($koneksi, "UPDATE kriteria SET kode_kriteria=?,nama_kriteria=?,bobot=?,jenis=? WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "ssisi", $kode, $nama, $bobot, $jenis, $id);
        if (mysqli_stmt_execute($stmt2)) { header("Location: index.php"); exit; }
        else $pesan = 'Gagal: ' . mysqli_error($koneksi);
        mysqli_stmt_close($stmt2);
    }
}
$page_title = 'Edit Kriteria'; $active_menu = 'kriteria'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom"><div class="page-title"><i class="fa fa-edit"></i><h4>Edit Kriteria</h4></div></div>
    <div class="card-custom">
        <?php if ($pesan): ?><div class="alert alert-danger" style="border-radius:10px;"><?php echo $pesan; ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label class="form-label fw-bold small">Kode Kriteria</label><input type="text" name="kode_kriteria" class="form-control" required value="<?php echo htmlspecialchars($kriteria['kode_kriteria']); ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Nama Kriteria</label><input type="text" name="nama_kriteria" class="form-control" required value="<?php echo htmlspecialchars($kriteria['nama_kriteria']); ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Bobot</label><input type="number" name="bobot" class="form-control" min="1" required value="<?php echo $kriteria['bobot']; ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Jenis</label>
                <select name="jenis" class="form-select"><option value="benefit" <?php echo $kriteria['jenis']=='benefit'?'selected':''; ?>>Benefit</option><option value="cost" <?php echo $kriteria['jenis']=='cost'?'selected':''; ?>>Cost</option></select>
            </div>
            <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i>Update</button>
            <a href="index.php" class="btn-batal ms-2"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
        </form>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>