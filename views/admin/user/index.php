<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$user_list = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id ASC");
$jumlah_data = mysqli_num_rows($user_list);
$page_title = 'Data User'; $active_menu = 'user'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../../layouts/head.php'; ?></head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom">
        <div class="page-title"><i class="fa fa-users"></i><h4>Data User</h4></div>
        <div class="user-box"><div class="user-icon"><i class="fa fa-user"></i></div><div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div></div>
    </div>
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h5 class="m-0 text-muted">Daftar Pengguna Sistem</h5>
            <a href="tambah.php" class="btn btn-add"><i class="fa fa-plus me-1"></i>Tambah User</a>
        </div>
        <div class="table-responsive">
            <?php if ($jumlah_data > 0): ?>
            <table class="table table-bordered">
                <thead><tr><th width="8%">No</th><th>Nama</th><th width="20%">Username</th><th width="15%">Role</th><th width="12%">Aksi</th></tr></thead>
                <tbody>
                <?php $no=1; while ($row = mysqli_fetch_assoc($user_list)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td style="text-align:left;padding-left:15px;"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><span class="badge-status <?php echo $row['role']=='admin'?'badge-draft':($row['role']=='ketua_yayasan'?'badge-submitted':'badge-verified'); ?>"><?php echo $row['role']=='ketua_yayasan'?'Ketua Yayasan':ucfirst($row['role']); ?></span></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-icon edit me-1" title="Edit"><i class="fa fa-edit"></i></a>
                            <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-icon delete" title="Hapus" onclick="return confirm('Hapus user?')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?><div class="text-center py-5 text-muted"><p>Belum ada data user.</p></div><?php endif; ?>
        </div>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?>
</body>
</html>
