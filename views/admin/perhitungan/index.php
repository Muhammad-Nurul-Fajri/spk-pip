<?php
session_start();
require_once '../../../config/koneksi.php';
require_once '../../../app/helpers/AhpWpHelper.php';
require_role('admin');

// Process WP calculation using AHP weights
$wp_res = AhpWpHelper::processWP($koneksi);

$pesan_sukses = '';
$pesan_error = '';

// Handle Save WP Results
if (isset($_GET['simpan']) && $_GET['simpan'] == 1 && $wp_res['can_calculate']) {
    mysqli_begin_transaction($koneksi);
    try {
        mysqli_query($koneksi, "DELETE FROM hasil_wp");
        
        $st = mysqli_prepare($koneksi, "INSERT INTO hasil_wp (id_siswa, nilai_s, nilai_v, ranking, status_verifikasi) VALUES (?, ?, ?, ?, 'menunggu_penilaian')");
        $su = mysqli_prepare($koneksi, "UPDATE siswa SET status_pendaftaran='processed' WHERE id=?");

        foreach ($wp_res['vektor_v'] as $id_siswa => $v_val) {
            $s_val = $wp_res['vektor_s'][$id_siswa];
            $r = $wp_res['ranking'][$id_siswa];

            mysqli_stmt_bind_param($st, "iddi", $id_siswa, $s_val, $v_val, $r);
            mysqli_stmt_execute($st);

            // Synchronize student status
            mysqli_stmt_bind_param($su, "i", $id_siswa);
            mysqli_stmt_execute($su);
        }
        mysqli_stmt_close($st);
        mysqli_stmt_close($su);

        mysqli_commit($koneksi);
        $pesan_sukses = 'Hasil perhitungan Weighted Product (WP) berbasis bobot AHP berhasil disimpan ke database!';
        
        // Refresh wp_res after saving
        $wp_res = AhpWpHelper::processWP($koneksi);
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $pesan_error = 'Gagal menyimpan hasil: ' . $e->getMessage();
    }
}

$page_title = 'Perhitungan Weighted Product';
$active_menu = 'perhitungan';
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
            <i class="fa fa-calculator"></i>
            <h4>Perhitungan Weighted Product (Bobot AHP)</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
        </div>
    </div>

    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success" style="border-radius:10px;"><i class="fa fa-check-circle me-2"></i><?php echo $pesan_sukses; ?></div>
    <?php endif; ?>

    <?php if ($pesan_error): ?>
        <div class="alert alert-danger" style="border-radius:10px;"><i class="fa fa-times-circle me-2"></i><?php echo $pesan_error; ?></div>
    <?php endif; ?>

    <?php if (!$wp_res['can_calculate']): ?>
        <div class="card-custom text-center py-5">
            <i class="fa fa-triangle-exclamation text-warning mb-3" style="font-size:50px;"></i>
            <h5>Proses Perhitungan Ditolak</h5>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($wp_res['message']); ?></p>
        </div>
    <?php else: ?>

    <!-- STEP 1: NORMALIZED AHP WEIGHTS -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;">
            <span class="badge bg-success me-2">1</span>Bobot AHP Ternormalisasi (W<sub>j</sub>)
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr class="table-light">
                        <th>Kode</th>
                        <th>Nama Kriteria</th>
                        <th>Jenis</th>
                        <th>Bobot AHP Ternormalisasi (W<sub>j</sub>)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_norm_w = 0;
                    foreach ($wp_res['kriteria'] as $kr): 
                        $norm_w = floatval($kr['bobot']);
                        $total_norm_w += $norm_w;
                    ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($kr['kode_kriteria']); ?></td>
                            <td class="text-start ps-3"><?php echo htmlspecialchars($kr['nama_kriteria']); ?></td>
                            <td><span class="badge-<?php echo $kr['jenis']; ?>"><?php echo ucfirst($kr['jenis']); ?></span></td>
                            <td class="fw-bold text-success"><?php echo number_format($norm_w, 3); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STEP 2: DECISION MATRIX -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;">
            <span class="badge bg-success me-2">2</span>Matriks Keputusan (X)
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr class="table-light">
                        <th>Alternatif</th>
                        <th>Nama Siswa</th>
                        <?php foreach ($wp_res['kriteria'] as $kr): ?>
                            <th><?php echo htmlspecialchars($kr['kode_kriteria']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wp_res['siswa'] as $siswa): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($siswa['kode_alternatif']); ?></td>
                            <td class="text-start ps-3"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                            <?php foreach ($wp_res['kriteria'] as $kr): ?>
                                <td><?php echo $wp_res['penilaian_map'][$siswa['id']][$kr['id']] ?? '-'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STEP 3: VECTOR S -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;">
            <span class="badge bg-success me-2">3</span>Perhitungan Vektor S
        </h6>
        <p class="text-muted small">Formula: S<sub>i</sub> = ∏ X<sub>ij</sub><sup>W<sub>j</sub></sup></p>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr class="table-light">
                        <th>Alternatif</th>
                        <th>Nama Siswa</th>
                        <th>Nilai Vektor S</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wp_res['siswa'] as $siswa): 
                        if (!isset($wp_res['vektor_s'][$siswa['id']])) continue;
                    ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($siswa['kode_alternatif']); ?></td>
                            <td class="text-start ps-3"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                            <td class="fw-bold"><?php echo number_format($wp_res['vektor_s'][$siswa['id']], 5); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-secondary fw-bold">
                        <td colspan="2" class="text-end pe-3">Total Vektor S (ΣS)</td>
                        <td><?php echo number_format($wp_res['total_s'], 5); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STEP 4: VECTOR V & RANKING -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;">
            <span class="badge bg-success me-2">4</span>Perhitungan Vektor V &amp; Perangkingan Akhir
        </h6>
        <p class="text-muted small">Formula: V<sub>i</sub> = S<sub>i</sub> / ΣS</p>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr class="table-light">
                        <th width="10%">Ranking</th>
                        <th width="12%">Kode</th>
                        <th>Nama Siswa</th>
                        <th width="18%">Nilai Vektor S</th>
                        <th width="18%">Nilai Preferensi (V)</th>
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
                            <td class="fw-bold"><?php echo htmlspecialchars($siswa['kode_alternatif'] ?? '-'); ?></td>
                            <td class="text-start ps-3"><strong><?php echo htmlspecialchars($siswa['nama'] ?? '-'); ?></strong></td>
                            <td><?php echo ($v_s !== null) ? number_format(floatval($v_s), 5) : '<span class="text-muted small">Belum Lengkap</span>'; ?></td>
                            <td class="fw-bold text-success fs-6"><?php echo ($v_v !== null) ? number_format(floatval($v_v), 5) : '<span class="text-muted small">Belum Lengkap</span>'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-end">
            <a href="index.php?simpan=1" class="btn btn-success-custom px-4 py-2" onclick="return confirm('Simpan hasil perangkingan berbasis bobot AHP ke database?')">
                <i class="fa fa-save me-1"></i> Simpan Hasil Rekomendasi PIP
            </a>
        </div>
    </div>

    <?php endif; ?>
</div>

<?php include '../../layouts/footer.php'; ?>
</body>
</html>
