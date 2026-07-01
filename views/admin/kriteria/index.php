<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$kriteria_list = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
$jumlah_data = mysqli_num_rows($kriteria_list);
$page_title = 'Data Kriteria'; $active_menu = 'kriteria'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom">
        <div class="page-title"><i class="fa fa-list"></i><h4>Data Kriteria</h4></div>
        <div class="user-box"><div class="user-icon"><i class="fa fa-user"></i></div><div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div></div>
    </div>
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h5 class="m-0 text-muted">Daftar Kriteria Penilaian (WP)</h5>
            <a href="tambah.php" class="btn btn-add"><i class="fa fa-plus me-1"></i>Tambah</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th width="5%">No</th><th width="10%">Kode</th><th>Nama Kriteria</th><th width="12%">Bobot</th><th width="12%">Jenis</th><th width="12%">Aksi</th></tr></thead>
                <tbody>
                <?php $no=1; while($row = mysqli_fetch_assoc($kriteria_list)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['kode_kriteria']); ?></td>
                        <td style="text-align:left;padding-left:15px;"><?php echo htmlspecialchars($row['nama_kriteria']); ?></td>
                        <td><?php echo $row['bobot']; ?></td>
                        <td><span class="badge-<?php echo $row['jenis']; ?>"><?php echo ucfirst($row['jenis']); ?></span></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-icon edit me-1"><i class="fa fa-edit"></i></a>
                            <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-icon delete" onclick="return confirm('Hapus kriteria?')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>