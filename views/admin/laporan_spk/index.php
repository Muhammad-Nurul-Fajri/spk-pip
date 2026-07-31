<?php
session_start();
require_once '../../../config/koneksi.php';
require_once '../../../app/helpers/AhpWpHelper.php';
require_role('admin');

$ahp_res = AhpWpHelper::processAHP($koneksi);
$wp_res = AhpWpHelper::processWP($koneksi);

$page_title = 'Laporan Lengkap SPK (AHP + WP)';
$active_menu = 'laporan_spk';
$asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layouts/head.php'; ?>
</head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-file-lines"></i>
            <h4>Laporan Lengkap SPK Method (AHP + WP)</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
        </div>
    </div>

    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h5 class="mb-1">Laporan Evaluasi Metodologi AHP + Weighted Product</h5>
                <p class="text-muted mb-0 small">Menampilkan seluruh tahapan kalkulasi AHP (Penentuan Bobot) &amp; WP (Perangkingan Alternatif)</p>
            </div>
            <a href="cetak.php" target="_blank" class="btn btn-add">
                <i class="fa fa-print me-1"></i> Cetak Laporan Lengkap PDF
            </a>
        </div>

        <!-- SECTION 1: AHP SUMMARY -->
        <h6 class="fw-bold text-success border-bottom pb-2 mb-3"><i class="fa fa-chart-pie me-2"></i>1. Ringkasan Bobot &amp; Konsistensi AHP</h6>
        <?php if ($ahp_res['success']): ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered text-center">
                        <tr class="table-light"><th colspan="2">Parameter Konsistensi</th></tr>
                        <tr><td class="text-start ps-3">Max Eigenvalue (λmax)</td><td class="fw-bold"><?php echo number_format($ahp_res['lambda_max'], 4); ?></td></tr>
                        <tr><td class="text-start ps-3">Consistency Index (CI)</td><td class="fw-bold"><?php echo number_format($ahp_res['ci'], 4); ?></td></tr>
                        <tr><td class="text-start ps-3">Random Index (RI)</td><td class="fw-bold"><?php echo number_format($ahp_res['ri'], 2); ?></td></tr>
                        <tr><td class="text-start ps-3">Consistency Ratio (CR)</td><td class="fw-bold <?php echo $ahp_res['is_consistent'] ? 'text-success' : 'text-danger'; ?>"><?php echo number_format($ahp_res['cr'], 4); ?></td></tr>
                        <tr><td class="text-start ps-3">Status Konsistensi</td><td><span class="badge bg-<?php echo $ahp_res['is_consistent'] ? 'success' : 'danger'; ?>"><?php echo strtoupper($ahp_res['status']); ?></span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered text-center">
                        <tr class="table-light"><th>Kode</th><th>Kriteria</th><th>Bobot AHP (W<sub>j</sub>)</th></tr>
                        <?php foreach ($ahp_res['kriteria'] as $k): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($k['kode_kriteria']); ?></strong></td>
                                <td class="text-start ps-3"><?php echo htmlspecialchars($k['nama_kriteria']); ?></td>
                                <td class="fw-bold text-success"><?php echo number_format($ahp_res['priority_vector'][$k['id']], 4); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="text-muted">Data AHP belum diisi.</p>
        <?php endif; ?>

        <!-- SECTION 2: WEIGHTED PRODUCT SUMMARY -->
        <h6 class="fw-bold text-success border-bottom pb-2 mb-3 mt-4"><i class="fa fa-trophy me-2"></i>2. Hasil Perangkingan Weighted Product (WP)</h6>
        <?php if ($wp_res['can_calculate']): ?>
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr class="table-light">
                            <th width="8%">Ranking</th>
                            <th width="10%">Kode</th>
                            <th>Nama Siswa</th>
                            <th width="12%">Kelas</th>
                            <th width="15%">Nilai S</th>
                            <th width="15%">Nilai V (Preferensi)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sorted = $wp_res['siswa'] ?? [];
                        usort($sorted, function($a, $b) use ($wp_res) {
                            $rA = $wp_res['ranking'][$a['id']] ?? 9999;
                            $rB = $wp_res['ranking'][$b['id']] ?? 9999;
                            return $rA - $rB;
                        });
                        $top_badges = [1 => 'danger', 2 => 'warning text-dark', 3 => 'success'];
                        foreach ($sorted as $siswa):
                            $s_id = $siswa['id'] ?? 0;
                            $r = $wp_res['ranking'][$s_id] ?? null;
                            $v_s = $wp_res['vektor_s'][$s_id] ?? null;
                            $v_v = $wp_res['vektor_v'][$s_id] ?? null;
                            $is_ranked = is_numeric($r) && intval($r) > 0;
                        ?>
                            <tr>
                                <td>
                                    <?php if ($is_ranked && intval($r) <= 3 && isset($top_badges[intval($r)])): ?>
                                        <span class="badge bg-<?php echo $top_badges[intval($r)]; ?> fs-6">
                                            <?php if(intval($r) === 1): ?><i class="fa fa-trophy"></i> <?php endif; echo htmlspecialchars($r); ?>
                                        </span>
                                    <?php elseif ($is_ranked): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($r); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($siswa['kode_alternatif'] ?? '-'); ?></td>
                                <td class="text-start ps-3"><strong><?php echo htmlspecialchars($siswa['nama'] ?? '-'); ?></strong></td>
                                <td><?php echo htmlspecialchars($siswa['kelas'] ?: '-'); ?></td>
                                <td><?php echo ($v_s !== null) ? number_format(floatval($v_s), 5) : '<span class="text-muted small">Belum Lengkap</span>'; ?></td>
                                <td class="fw-bold text-success"><?php echo ($v_v !== null) ? number_format(floatval($v_v), 5) : '<span class="text-muted small">Belum Lengkap</span>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Proses WP belum dapat dijalankan (Matriks AHP belum konsisten).</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>
</body>
</html>
