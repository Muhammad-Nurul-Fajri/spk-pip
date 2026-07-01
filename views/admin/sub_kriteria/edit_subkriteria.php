<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$id = intval($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT * FROM sub_kriteria WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt);
$sub = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
if (!$sub) { header("Location: index_subkriteria.php"); exit; }
$kriteria_list = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kriteria = intval($_POST['id_kriteria'] ?? 0);
    $nama_sub = trim($_POST['nama_sub'] ?? '');
    $nilai = intval($_POST['nilai'] ?? 0);
    if ($id_kriteria<=0||empty($nama_sub)||$nilai<=0) $pesan='Field wajib!';
    else {
        $s = mysqli_prepare($koneksi, "UPDATE sub_kriteria SET id_kriteria=?,nama_sub=?,nilai=? WHERE id=?");
        mysqli_stmt_bind_param($s, "isii", $id_kriteria, $nama_sub, $nilai, $id);
        if (mysqli_stmt_execute($s)) { header("Location: index_subkriteria.php"); exit; }
        else $pesan = 'Gagal.';
        mysqli_stmt_close($s);
    }
}
$page_title = 'Edit Sub Kriteria'; $active_menu = 'sub_kriteria'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom"><div class="page-title"><i class="fa fa-edit"></i><h4>Edit Sub Kriteria</h4></div></div>
    <div class="card-custom">
        <?php if ($pesan): ?><div class="alert alert-danger" style="border-radius:10px;"><?php echo $pesan; ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label class="form-label fw-bold small">Kriteria</label>
                <select name="id_kriteria" class="form-select" required>
                    <?php while($k = mysqli_fetch_assoc($kriteria_list)): ?><option value="<?php echo $k['id']; ?>" <?php echo ($k['id']==$sub['id_kriteria'])?'selected':''; ?>><?php echo $k['kode_kriteria'].' - '.$k['nama_kriteria']; ?></option><?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3"><label class="form-label fw-bold small">Nama Sub Kriteria</label><input type="text" name="nama_sub" class="form-control" required value="<?php echo htmlspecialchars($sub['nama_sub']); ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Nilai</label><input type="number" name="nilai" class="form-control" min="1" max="5" required value="<?php echo $sub['nilai']; ?>"></div>
            <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i>Update</button>
            <a href="index_subkriteria.php" class="btn-batal ms-2"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
        </form>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>