<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'siswa';
    if (empty($nama)||empty($username)||empty($password)) { $pesan = 'Semua field wajib diisi!'; }
    else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama,username,password,role) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $hashed, $role);
        if (mysqli_stmt_execute($stmt)) { header("Location: index.php"); exit; }
        else $pesan = 'Gagal: ' . mysqli_error($koneksi);
        mysqli_stmt_close($stmt);
    }
}
$page_title = 'Tambah User'; $active_menu = 'user'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom"><div class="page-title"><i class="fa fa-user-plus"></i><h4>Tambah User</h4></div></div>
    <div class="card-custom">
        <?php if ($pesan): ?><div class="alert alert-danger" style="border-radius:10px;"><?php echo $pesan; ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label class="form-label fw-bold small">Nama</label><input type="text" name="nama" class="form-control" required></div>
            <div class="mb-3"><label class="form-label fw-bold small">Username</label><input type="text" name="username" class="form-control" required></div>
            <div class="mb-3"><label class="form-label fw-bold small">Password</label><input type="password" name="password" class="form-control" required></div>
            <div class="mb-3"><label class="form-label fw-bold small">Role</label>
                <select name="role" class="form-select"><option value="admin">Admin</option><option value="ketua_yayasan">Ketua Yayasan</option><option value="siswa">Siswa</option></select>
            </div>
            <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i>Simpan</button>
            <a href="index.php" class="btn-batal ms-2"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
        </form>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>
