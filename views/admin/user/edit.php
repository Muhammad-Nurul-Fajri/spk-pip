<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$id = intval($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$user) { header("Location: index.php"); exit; }

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? $user['role'];
    $password = $_POST['password'] ?? '';
    if (empty($nama)||empty($username)) { $pesan = 'Nama dan username wajib diisi!'; }
    else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = mysqli_prepare($koneksi, "UPDATE users SET nama=?,username=?,password=?,role=? WHERE id=?");
            mysqli_stmt_bind_param($stmt2, "ssssi", $nama, $username, $hashed, $role, $id);
        } else {
            $stmt2 = mysqli_prepare($koneksi, "UPDATE users SET nama=?,username=?,role=? WHERE id=?");
            mysqli_stmt_bind_param($stmt2, "sssi", $nama, $username, $role, $id);
        }
        if (mysqli_stmt_execute($stmt2)) { header("Location: index.php"); exit; }
        else $pesan = 'Gagal: ' . mysqli_error($koneksi);
        mysqli_stmt_close($stmt2);
    }
}
$page_title = 'Edit User'; $active_menu = 'user'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom"><div class="page-title"><i class="fa fa-user-edit"></i><h4>Edit User</h4></div></div>
    <div class="card-custom">
        <?php if ($pesan): ?><div class="alert alert-danger" style="border-radius:10px;"><?php echo $pesan; ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label class="form-label fw-bold small">Nama</label><input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($user['nama']); ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Username</label><input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($user['username']); ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label><input type="password" name="password" class="form-control"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Role</label>
                <select name="role" class="form-select">
                    <option value="admin" <?php echo $user['role']=='admin'?'selected':''; ?>>Admin</option>
                    <option value="ketua_yayasan" <?php echo $user['role']=='ketua_yayasan'?'selected':''; ?>>Ketua Yayasan</option>
                    <option value="siswa" <?php echo $user['role']=='siswa'?'selected':''; ?>>Siswa</option>
                </select>
            </div>
            <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i>Update</button>
            <a href="index.php" class="btn-batal ms-2"><i class="fa fa-arrow-left me-1"></i>Kembali</a>
        </form>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>
