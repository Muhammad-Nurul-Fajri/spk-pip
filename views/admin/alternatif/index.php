<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');

// Handle status update (verify)
if (isset($_GET['verify']) && intval($_GET['verify']) > 0) {
    $vid = intval($_GET['verify']);
    $stmt = mysqli_prepare($koneksi, "UPDATE siswa SET status_pendaftaran='verified' WHERE id=? AND status_pendaftaran='submitted'");
    mysqli_stmt_bind_param($stmt, "i", $vid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: index.php");
    exit;
}

$siswa_list = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY kode_alternatif ASC");
$jumlah_data = mysqli_num_rows($siswa_list);

$page_title = 'Data Alternatif';
$active_menu = 'alternatif';
$asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../../layouts/head.php'; ?>
</head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-user-graduate"></i>
            <h4>Data Alternatif (Siswa)</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
        </div>
    </div>

    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h5 class="m-0 text-muted">Daftar Siswa Pendaftar PIP</h5>
            <a href="tambah.php" class="btn btn-add"><i class="fa fa-plus me-1"></i>Tambah</a>
        </div>

        <div class="table-responsive">
            <?php if ($jumlah_data > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="8%">Kode</th>
                        <th>Nama Siswa</th>
                        <th width="10%">Kelas</th>
                        <th width="12%">Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($siswa_list)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['kode_alternatif']); ?></td>
                        <td style="text-align:left;padding-left:15px;"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td><?php echo htmlspecialchars($row['kelas'] ?? '-'); ?></td>
                        <td>
                            <span class="badge-status badge-<?php echo $row['status_pendaftaran']; ?>" style="font-size:11px;">
                                <?php echo ucfirst($row['status_pendaftaran']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-icon edit me-1" title="Edit"><i class="fa fa-edit"></i></a>
                            <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-icon delete me-1" title="Hapus"
                               onclick="return confirm('Hapus siswa \'<?php echo htmlspecialchars($row['nama']); ?>\'?')"><i class="fa fa-trash"></i></a>
                            <?php if ($row['status_pendaftaran'] === 'submitted'): ?>
                                <a href="index.php?verify=<?php echo $row['id']; ?>" class="btn-icon" style="background:#1e88e5;" title="Verifikasi"
                                   onclick="return confirm('Verifikasi data siswa ini?')"><i class="fa fa-check"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-user-graduate mb-3" style="font-size:40px;color:#ddd;"></i>
                    <p>Belum ada data siswa.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>
</body>
</html>
