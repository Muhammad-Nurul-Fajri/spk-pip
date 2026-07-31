<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');

$query_ranking = mysqli_query($koneksi, "SELECT h.*, s.nama, s.kode_alternatif, s.kelas FROM hasil_wp h JOIN siswa s ON h.id_siswa=s.id ORDER BY h.ranking ASC");
$jumlah_data = mysqli_num_rows($query_ranking);

$page_title = 'Hasil Akhir';
$active_menu = 'hasil';
$asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../../layouts/head.php'; ?></head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom">
        <div class="page-title"><i class="fa fa-trophy"></i><h4>Data Hasil Akhir</h4></div>
        <div class="user-box"><div class="user-icon"><i class="fa fa-user"></i></div><div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div></div>
    </div>
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div><h5 class="mb-1">Perangkingan Penerima Bantuan PIP</h5><p class="text-muted mb-0 small">Metode Analytical Hierarchy Process (AHP) &amp; Weighted Product (WP)</p></div>
            <?php if ($jumlah_data > 0): ?>
            <form action="cetak_pdf.php" method="GET" target="_blank" class="d-flex align-items-center gap-2 no-print">
                <select name="filter" class="form-select" style="width: auto; height: 42px;">
                    <option value="semua">Semua Status</option>
                    <option value="layak">Layak</option>
                    <option value="tidak_layak">Tidak Layak</option>
                </select>
                <button type="submit" class="btn-add"><i class="fa fa-file-pdf me-1"></i>Ekspor PDF</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <?php if ($jumlah_data > 0): ?>
            <table class="table table-bordered">
                <thead><tr><th width="8%">Ranking</th><th width="10%">Kode</th><th width="15%">Kelas</th><th>Nama Siswa</th><th width="12%">Nilai S</th><th width="12%">Nilai V</th><th width="20%">Status</th></tr></thead>
                <tbody>
                <?php 
                $top_badges = [1 => 'danger', 2 => 'warning text-dark', 3 => 'success'];
                while ($row = mysqli_fetch_assoc($query_ranking)): 
                    $r = intval($row['ranking'] ?? 0);
                ?>
                    <tr>
                        <td>
                            <?php if ($r >= 1 && $r <= 3 && isset($top_badges[$r])): ?>
                                <span class="badge bg-<?php echo $top_badges[$r]; ?> fs-6">
                                    <?php if($r === 1): ?><i class="fa fa-trophy"></i> <?php endif; echo $r; ?>
                                </span>
                            <?php elseif ($r > 0): ?>
                                <span class="badge bg-secondary"><?php echo $r; ?></span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['kode_alternatif'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['kelas'] ?? '-'); ?></td>
                        <td style="text-align:left;padding-left:15px;"><strong><?php echo htmlspecialchars($row['nama'] ?? '-'); ?></strong></td>
                        <td><?php echo number_format(floatval($row['nilai_s'] ?? 0), 5); ?></td>
                        <td><strong><?php echo number_format(floatval($row['nilai_v'] ?? 0), 5); ?></strong></td>
                        <td>
                            <?php
                            if ($row['status_verifikasi'] === 'menunggu_penilaian') {
                                echo '<span class="badge-menunggu"><i class="bi bi-hourglass-split me-1"></i>Menunggu Penilaian Ketua</span>';
                            } elseif ($row['status_verifikasi'] === 'layak') {
                                echo '<span class="badge-layak"><i class="bi bi-check-circle-fill me-1"></i>Layak</span>';
                            } else {
                                echo '<span class="badge-tidak-layak"><i class="bi bi-x-circle-fill me-1"></i>Tidak Layak</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="text-center py-5 text-muted"><i class="fa fa-trophy mb-3" style="font-size:40px;color:#ddd;"></i><p>Belum ada hasil. Lakukan perhitungan terlebih dahulu.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?>
</body>
</html>
