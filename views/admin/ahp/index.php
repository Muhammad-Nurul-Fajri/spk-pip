<?php
session_start();
require_once '../../../config/koneksi.php';
require_once '../../../app/helpers/AhpWpHelper.php';
require_role('admin');

// Redirect admin away from hidden AHP weight results page
header("Location: ../dashboard.php");
exit;

$ahp_res = AhpWpHelper::processAHP($koneksi);

$pesan_sukses = $_SESSION['ahp_success'] ?? '';
$pesan_warning = $_SESSION['ahp_warning'] ?? '';
unset($_SESSION['ahp_success'], $_SESSION['ahp_warning']);

$page_title = 'Hasil Perhitungan AHP';
$active_menu = 'ahp';
$asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layouts/head.php'; ?>
    <style>
        .metric-box {
            background: #fff;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            text-align: center;
        }
        .metric-box h6 {
            color: var(--text-muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .metric-box .metric-value {
            font-size: 24px;
            font-weight: 800;
            color: var(--text);
        }
    </style>
</head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-chart-pie"></i>
            <h4>Hasil Perhitungan AHP (Analytical Hierarchy Process)</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
        </div>
    </div>

    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success" style="border-radius:10px;"><i class="fa fa-check-circle me-2"></i><?php echo $pesan_sukses; ?></div>
    <?php endif; ?>

    <?php if ($pesan_warning): ?>
        <div class="alert alert-warning" style="border-radius:10px;"><i class="fa fa-exclamation-triangle me-2"></i><?php echo $pesan_warning; ?></div>
    <?php endif; ?>

    <?php if (!$ahp_res['success']): ?>
        <div class="card-custom text-center py-5">
            <i class="fa fa-triangle-exclamation text-warning mb-3" style="font-size:50px;"></i>
            <h5>Data Kriteria Belum Tersedia</h5>
            <p class="text-muted">Silakan atur data kriteria terlebih dahulu.</p>
        </div>
    <?php else: ?>

    <!-- SUMMARY METRICS ROW -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-box">
                <h6>Max Eigenvalue (λmax)</h6>
                <div class="metric-value text-primary"><?php echo number_format($ahp_res['lambda_max'], 4); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-box">
                <h6>Consistency Index (CI)</h6>
                <div class="metric-value text-info"><?php echo number_format($ahp_res['ci'], 4); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-box">
                <h6>Random Index (RI)</h6>
                <div class="metric-value text-secondary"><?php echo number_format($ahp_res['ri'], 2); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-box">
                <h6>Consistency Ratio (CR)</h6>
                <div class="metric-value <?php echo $ahp_res['is_consistent'] ? 'text-success' : 'text-danger'; ?>">
                    <?php echo number_format($ahp_res['cr'], 4); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CONSISTENCY STATUS BANNER -->
    <div class="card-custom mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="mb-1">Status Uji Konsistensi AHP:</h5>
                <p class="text-muted mb-0 small">Syarat konsistensi matriks perbandingan berpasangan adalah CR ≤ 0.10 (10%).</p>
            </div>
            <div>
                <?php if ($ahp_res['is_consistent']): ?>
                    <span class="badge bg-success fs-5 px-3 py-2">
                        <i class="fa fa-check-circle me-2"></i>KONSISTEN (CR = <?php echo number_format($ahp_res['cr'], 4); ?> ≤ 0.10)
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger fs-5 px-3 py-2">
                        <i class="fa fa-times-circle me-2"></i>TIDAK KONSISTEN (CR = <?php echo number_format($ahp_res['cr'], 4); ?> > 0.10)
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- STEP 1: PAIRWISE MATRIX -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;">
            <span class="badge bg-success me-2">1</span>Matriks Perbandingan Berpasangan (Pairwise Matrix)
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr class="table-light">
                        <th>Kriteria</th>
                        <?php foreach ($ahp_res['kriteria'] as $k): ?>
                            <th><?php echo htmlspecialchars($k['kode_kriteria']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ahp_res['kriteria'] as $k1): ?>
                        <tr>
                            <td class="fw-bold text-start ps-3 table-light"><?php echo htmlspecialchars($k1['kode_kriteria']); ?></td>
                            <?php foreach ($ahp_res['kriteria'] as $k2): 
                                $v = $ahp_res['matrix'][$k1['id']][$k2['id']];
                            ?>
                                <td><?php echo (abs($v - round($v)) < 0.0001) ? round($v) : number_format($v, 4); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-secondary fw-bold">
                        <td class="text-start ps-3">Jumlah Kolom (Σ)</td>
                        <?php foreach ($ahp_res['kriteria'] as $k): ?>
                            <td><?php echo number_format($ahp_res['col_sums'][$k['id']], 4); ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STEP 2: NORMALIZED MATRIX & PRIORITY VECTOR -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;">
            <span class="badge bg-success me-2">2</span>Matriks Normalisasi &amp; Bobot Prioritas (Priority Vector / W)
        </h6>
        <p class="text-muted small">Normalisasi: r<sub>ij</sub> = x<sub>ij</sub> / Σx<sub>kj</sub> | Priority Vector: W<sub>i</sub> = Σr<sub>ij</sub> / n</p>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr class="table-light">
                        <th>Kode</th>
                        <th>Nama Kriteria</th>
                        <?php foreach ($ahp_res['kriteria'] as $k): ?>
                            <th><?php echo htmlspecialchars($k['kode_kriteria']); ?></th>
                        <?php endforeach; ?>
                        <th class="table-success">Priority Vector (AHP Weight)</th>
                        <th class="table-success">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ahp_res['kriteria'] as $k1): 
                        $pv = $ahp_res['priority_vector'][$k1['id']];
                    ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($k1['kode_kriteria']); ?></td>
                            <td class="text-start ps-3"><?php echo htmlspecialchars($k1['nama_kriteria']); ?></td>
                            <?php foreach ($ahp_res['kriteria'] as $k2): ?>
                                <td><?php echo number_format($ahp_res['normalized'][$k1['id']][$k2['id']], 4); ?></td>
                            <?php endforeach; ?>
                            <td class="table-success fw-bold"><?php echo number_format($pv, 4); ?></td>
                            <td class="table-success fw-bold"><?php echo number_format($pv * 100, 2); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-secondary fw-bold">
                        <td colspan="2" class="text-end pe-3">Total</td>
                        <?php foreach ($ahp_res['kriteria'] as $k): ?>
                            <td>1.0000</td>
                        <?php endforeach; ?>
                        <td class="table-success">1.0000</td>
                        <td class="table-success">100.00%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ACTION FOOTER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="../perbandingan/index.php" class="btn btn-secondary-custom">
            <i class="fa fa-edit me-1"></i> Edit Perbandingan Berpasangan
        </a>

        <?php if ($ahp_res['is_consistent']): ?>
            <a href="../perhitungan/index.php" class="btn btn-success-custom">
                Lanjut ke Perhitungan Weighted Product (WP) <i class="fa fa-arrow-right ms-1"></i>
            </a>
        <?php else: ?>
            <button class="btn btn-danger disabled" title="Matriks belum konsisten">
                <i class="fa fa-lock me-1"></i> Perhitungan WP Diterkunci (Perbaiki Matriks AHP)
            </button>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>

<?php include '../../layouts/footer.php'; ?>
</body>
</html>
