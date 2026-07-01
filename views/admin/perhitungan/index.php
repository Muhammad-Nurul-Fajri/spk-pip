<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');

// Fetch data
$kriteria_arr = [];
$kq = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
while ($k = mysqli_fetch_assoc($kq)) $kriteria_arr[] = $k;
$jumlah_kriteria = count($kriteria_arr);

$siswa_arr = [];
$sq = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY kode_alternatif ASC");
while ($s = mysqli_fetch_assoc($sq)) $siswa_arr[] = $s;
$jumlah_siswa = count($siswa_arr);

$penilaian_map = [];
$pq = mysqli_query($koneksi, "SELECT * FROM penilaian");
while ($p = mysqli_fetch_assoc($pq)) $penilaian_map[$p['id_siswa']][$p['id_kriteria']] = $p['nilai'];

// === WEIGHTED PRODUCT CALCULATION ===
$total_bobot = 0;
foreach ($kriteria_arr as $kr) $total_bobot += $kr['bobot'];

// Step 1: Normalize weights
$bobot_normal = [];
foreach ($kriteria_arr as $kr) {
    $w = ($total_bobot > 0) ? $kr['bobot'] / $total_bobot : 0;
    if ($kr['jenis'] == 'cost') $w = -$w;
    $bobot_normal[$kr['id']] = $w;
}

// Step 2: Vector S
$vektor_s = [];
$can_calculate = ($jumlah_siswa > 0 && $jumlah_kriteria > 0 && $total_bobot > 0);
if ($can_calculate) {
    foreach ($siswa_arr as $siswa) {
        $s = 1; $complete = true;
        foreach ($kriteria_arr as $kr) {
            $nilai = $penilaian_map[$siswa['id']][$kr['id']] ?? 0;
            if ($nilai <= 0) { $complete = false; break; }
            $s *= pow($nilai, $bobot_normal[$kr['id']]);
        }
        if ($complete) $vektor_s[$siswa['id']] = $s;
    }
}

// Step 3: Vector V
$total_s = array_sum($vektor_s);
$vektor_v = [];
if ($total_s > 0) foreach ($vektor_s as $id => $sv) $vektor_v[$id] = $sv / $total_s;

// Step 4: Rank
arsort($vektor_v);
$ranking = []; $rank = 1;
foreach ($vektor_v as $id => $vv) $ranking[$id] = $rank++;

// Handle save
$pesan_sukses = '';
if (isset($_GET['simpan']) && $_GET['simpan'] == 1 && !empty($vektor_v)) {
    mysqli_query($koneksi, "DELETE FROM hasil_wp");
    foreach ($vektor_v as $id_siswa => $v_val) {
        $s_val = $vektor_s[$id_siswa]; $r = $ranking[$id_siswa];
        $st = mysqli_prepare($koneksi, "INSERT INTO hasil_wp (id_siswa, nilai_s, nilai_v, ranking) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, "iddi", $id_siswa, $s_val, $v_val, $r);
        mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
    }
    $pesan_sukses = 'Hasil perhitungan berhasil disimpan ke database!';
}

$page_title = 'Perhitungan WP';
$active_menu = 'perhitungan';
$asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../../layouts/head.php'; ?></head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom">
        <div class="page-title"><i class="fa fa-calculator"></i><h4>Perhitungan Weighted Product</h4></div>
        <div class="user-box"><div class="user-icon"><i class="fa fa-user"></i></div><div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div></div>
    </div>

    <?php if ($pesan_sukses): ?><div class="alert alert-success" style="border-radius:10px;"><i class="fa fa-check-circle me-2"></i><?php echo $pesan_sukses; ?></div><?php endif; ?>

    <?php if (!$can_calculate): ?>
        <div class="card-custom text-center py-5"><i class="fa fa-triangle-exclamation text-warning mb-3" style="font-size:50px;"></i><h5>Data Belum Lengkap</h5><p class="text-muted">Pastikan data kriteria, alternatif, dan penilaian sudah tersedia.</p></div>
    <?php else: ?>

    <!-- STEP 1 -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;"><span class="badge bg-success me-2">1</span>Normalisasi Bobot (W)</h6>
        <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Kode</th><th>Nama Kriteria</th><th>Jenis</th><th>Bobot</th><th>W</th></tr></thead><tbody>
        <?php foreach ($kriteria_arr as $kr): ?>
            <tr><td><?php echo $kr['kode_kriteria']; ?></td><td style="text-align:left;padding-left:15px;"><?php echo $kr['nama_kriteria']; ?></td><td><span class="badge-<?php echo $kr['jenis']; ?>"><?php echo ucfirst($kr['jenis']); ?></span></td><td><?php echo $kr['bobot']; ?></td><td><strong><?php echo number_format($bobot_normal[$kr['id']], 4); ?></strong></td></tr>
        <?php endforeach; ?>
            <tr style="background:#f5f5f5;"><td colspan="3" style="text-align:right;"><strong>Total</strong></td><td><strong><?php echo $total_bobot; ?></strong></td><td>—</td></tr>
        </tbody></table></div>
    </div>

    <!-- STEP 2 -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;"><span class="badge bg-success me-2">2</span>Matriks Keputusan (X)</h6>
        <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Alternatif</th><th>Nama</th><?php foreach ($kriteria_arr as $kr): ?><th><?php echo $kr['kode_kriteria']; ?></th><?php endforeach; ?></tr></thead><tbody>
        <?php foreach ($siswa_arr as $siswa): ?>
            <tr><td><?php echo $siswa['kode_alternatif']; ?></td><td style="text-align:left;padding-left:15px;"><?php echo $siswa['nama']; ?></td>
            <?php foreach ($kriteria_arr as $kr): ?><td><?php echo $penilaian_map[$siswa['id']][$kr['id']] ?? '-'; ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    </div>

    <!-- STEP 3 -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;"><span class="badge bg-success me-2">3</span>Vektor S</h6>
        <p class="text-muted small">S<sub>i</sub> = ∏ X<sub>ij</sub><sup>W<sub>j</sub></sup></p>
        <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Alternatif</th><th>Nama</th><th>Nilai S</th></tr></thead><tbody>
        <?php foreach ($siswa_arr as $siswa): if (!isset($vektor_s[$siswa['id']])) continue; ?>
            <tr><td><?php echo $siswa['kode_alternatif']; ?></td><td style="text-align:left;padding-left:15px;"><?php echo $siswa['nama']; ?></td><td><strong><?php echo number_format($vektor_s[$siswa['id']], 5); ?></strong></td></tr>
        <?php endforeach; ?>
            <tr style="background:#f5f5f5;"><td colspan="2" style="text-align:right;"><strong>Total S</strong></td><td><strong><?php echo number_format($total_s, 5); ?></strong></td></tr>
        </tbody></table></div>
    </div>

    <!-- STEP 4 -->
    <div class="card-custom">
        <h6 style="color:var(--primary);font-weight:bold;margin-bottom:12px;"><span class="badge bg-success me-2">4</span>Vektor V &amp; Peringkat</h6>
        <p class="text-muted small">V<sub>i</sub> = S<sub>i</sub> / ∑S</p>
        <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Ranking</th><th>Alternatif</th><th>Nama</th><th>Nilai S</th><th>Nilai V</th></tr></thead><tbody>
        <?php
        $sorted = [];
        foreach ($siswa_arr as $s) if (isset($ranking[$s['id']])) $sorted[] = $s;
        usort($sorted, function($a,$b) use ($ranking) { return $ranking[$a['id']] - $ranking[$b['id']]; });
        foreach ($sorted as $siswa): $r = $ranking[$siswa['id']];
        ?>
            <tr>
                <td><?php if($r<=3): ?><span class="badge bg-<?php echo ['','danger','warning text-dark','success'][$r]; ?> fs-6"><?php if($r==1): ?><i class="fa fa-trophy"></i> <?php endif; echo $r; ?></span><?php else: ?><span class="badge bg-secondary"><?php echo $r; ?></span><?php endif; ?></td>
                <td><?php echo $siswa['kode_alternatif']; ?></td>
                <td style="text-align:left;padding-left:15px;"><strong><?php echo $siswa['nama']; ?></strong></td>
                <td><?php echo number_format($vektor_s[$siswa['id']], 5); ?></td>
                <td><strong><?php echo number_format($vektor_v[$siswa['id']], 5); ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <div class="mt-3 text-end">
            <a href="index.php?simpan=1" class="btn-simpan" onclick="return confirm('Simpan hasil perhitungan?')"><i class="fa fa-save me-1"></i>Simpan Hasil</a>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include '../../layouts/footer.php'; ?>
</body>
</html>
