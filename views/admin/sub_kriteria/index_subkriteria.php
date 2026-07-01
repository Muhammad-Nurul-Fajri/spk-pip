<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$sub_list = mysqli_query($koneksi, "SELECT sk.*, k.kode_kriteria, k.nama_kriteria FROM sub_kriteria sk JOIN kriteria k ON sk.id_kriteria=k.id ORDER BY k.kode_kriteria ASC, sk.nilai DESC");
$jumlah_data = mysqli_num_rows($sub_list);
$page_title = 'Data Sub Kriteria'; $active_menu = 'sub_kriteria'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom">
        <div class="page-title"><i class="fa fa-layer-group"></i><h4>Data Sub Kriteria</h4></div>
        <div class="user-box"><div class="user-icon"><i class="fa fa-user"></i></div><div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div></div>
    </div>
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h5 class="m-0 text-muted">Daftar Sub Kriteria</h5>
            <a href="tambah_subkriteria.php" class="btn btn-add"><i class="fa fa-plus me-1"></i>Tambah</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th width="5%">No</th><th width="12%">Kriteria</th><th>Nama Sub Kriteria</th><th width="10%">Nilai</th><th width="12%">Aksi</th></tr></thead>
                <tbody>
                <?php $no=1; while($row = mysqli_fetch_assoc($sub_list)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['kode_kriteria']); ?></span></td>
                        <td style="text-align:left;padding-left:15px;"><?php echo htmlspecialchars($row['nama_sub']); ?></td>
                        <td><span class="badge-benefit"><?php echo $row['nilai']; ?></span></td>
                        <td>
                            <a href="edit_subkriteria.php?id=<?php echo $row['id']; ?>" class="btn-icon edit me-1"><i class="fa fa-edit"></i></a>
                            <a href="hapus_subkriteria.php?id=<?php echo $row['id']; ?>" class="btn-icon delete" onclick="return confirm('Hapus?')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>