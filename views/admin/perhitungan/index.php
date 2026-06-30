<?php
require_once '../../../config/koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fetch kriteria
$kriteria_query = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
$kriteria_arr = [];
while ($k = mysqli_fetch_assoc($kriteria_query)) {
    $kriteria_arr[] = $k;
}
$jumlah_kriteria = count($kriteria_arr);

// Fetch siswa
$siswa_query = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY kode_alternatif ASC");
$siswa_arr = [];
while ($s = mysqli_fetch_assoc($siswa_query)) {
    $siswa_arr[] = $s;
}
$jumlah_siswa = count($siswa_arr);

// Fetch penilaian
$penilaian_map = [];
$pen_query = mysqli_query($koneksi, "SELECT * FROM penilaian");
while ($p = mysqli_fetch_assoc($pen_query)) {
    $penilaian_map[$p['id_siswa']][$p['id_kriteria']] = $p['nilai'];
}

// =====================
// WEIGHTED PRODUCT CALC
// =====================
$total_bobot = 0;
foreach ($kriteria_arr as $kr) {
    $total_bobot += $kr['bobot'];
}

// Step 1: Normalize weights (W)
$bobot_normal = [];
foreach ($kriteria_arr as $kr) {
    $w = ($total_bobot > 0) ? $kr['bobot'] / $total_bobot : 0;
    // If cost, make negative
    if ($kr['jenis'] == 'cost') {
        $w = -$w;
    }
    $bobot_normal[$kr['id']] = $w;
}

// Step 2: Calculate Vector S
$vektor_s = [];
$can_calculate = ($jumlah_siswa > 0 && $jumlah_kriteria > 0 && $total_bobot > 0);

if ($can_calculate) {
    foreach ($siswa_arr as $siswa) {
        $s = 1;
        $complete = true;
        foreach ($kriteria_arr as $kr) {
            $nilai = isset($penilaian_map[$siswa['id']][$kr['id']]) ? $penilaian_map[$siswa['id']][$kr['id']] : 0;
            if ($nilai <= 0) {
                $complete = false;
                break;
            }
            $s *= pow($nilai, $bobot_normal[$kr['id']]);
        }
        if ($complete) {
            $vektor_s[$siswa['id']] = $s;
        }
    }
}

// Step 3: Calculate Vector V
$total_s = array_sum($vektor_s);
$vektor_v = [];
if ($total_s > 0) {
    foreach ($vektor_s as $id_siswa => $s_val) {
        $vektor_v[$id_siswa] = $s_val / $total_s;
    }
}

// Step 4: Rank by V descending
arsort($vektor_v);
$ranking = [];
$rank = 1;
foreach ($vektor_v as $id_siswa => $v_val) {
    $ranking[$id_siswa] = $rank++;
}

// Handle save action
$pesan_sukses = '';
if (isset($_GET['simpan']) && $_GET['simpan'] == 1 && !empty($vektor_v)) {
    // Clear old results
    mysqli_query($koneksi, "DELETE FROM hasil_wp");

    foreach ($vektor_v as $id_siswa => $v_val) {
        $s_val = $vektor_s[$id_siswa];
        $r = $ranking[$id_siswa];
        mysqli_query($koneksi, "INSERT INTO hasil_wp (id_siswa, nilai_s, nilai_v, ranking) VALUES ($id_siswa, $s_val, $v_val, $r)");
    }
    $pesan_sukses = 'Hasil perhitungan berhasil disimpan ke database!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Perhitungan WP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
body { background: #f4f7f1; overflow-x: hidden; }
.sidebar { width: 270px; height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, #4caf50, #c6d166); color: white; overflow-y: auto; }
.sidebar::-webkit-scrollbar { width: 0px; }
.logo { padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.15); }
.logo img { width: 95px; height: 95px; object-fit: contain; margin-bottom: 12px; }
.logo h4 { font-size: 15px; font-weight: bold; line-height: 1.6; margin-bottom: 8px; }
.logo p { font-size: 12px; margin: 0; opacity: 0.95; }
.menu { padding: 18px 0; margin: 0; }
.menu li { list-style: none; }
.menu li a { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: white; text-decoration: none; font-size: 14px; transition: 0.3s; }
.menu li a:hover, .menu li a.active { background: rgba(255,255,255,0.15); }
.menu li a i { width: 22px; text-align: center; }
.content { margin-left: 270px; padding: 22px; min-height: 100vh; }
.navbar-custom { background: white; padding: 16px 22px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 25px; }
.page-title { display: flex; align-items: center; gap: 12px; }
.page-title i { font-size: 22px; color: #4caf50; }
.page-title h4 { margin: 0; font-size: 23px; font-weight: bold; color: #333; }
.user-box { display: flex; align-items: center; gap: 12px; }
.user-icon { width: 38px; height: 38px; border-radius: 50%; background: #e8f5e9; color: #4caf50; display: flex; justify-content: center; align-items: center; font-size: 16px; border: 1px solid #c8e6c9; }
.user-name { font-size: 14px; font-weight: bold; color: #555; }
.card-custom { background: white; border-radius: 18px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.04); padding: 24px; margin-bottom: 25px; }
.table thead th { background: #4caf50; color: white; text-align: center; vertical-align: middle; font-size: 13px; }
.table tbody td { text-align: center; vertical-align: middle; background: white; font-size: 13px; }
.table tbody tr:hover td { background: #e8f5e9; }
.step-title { color: #2e7d32; font-weight: bold; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
.step-title .step-num { background: #4caf50; color: white; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }
.badge-benefit { background: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 5px; font-size: 11px; }
.badge-cost { background: #fce4ec; color: #c62828; padding: 3px 8px; border-radius: 5px; font-size: 11px; }
.btn-simpan-hasil { background: linear-gradient(135deg, #4caf50, #C6D166); color: white; border: none; border-radius: 10px; padding: 10px 24px; font-weight: bold; transition: 0.3s; text-decoration: none; }
.btn-simpan-hasil:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(46,125,50,0.2); color: white; }
@media(max-width: 900px){ .sidebar { width: 100%; height: auto; position: relative; } .content { margin-left: 0; } }
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../../../public/assets/img/logo.png">
        <h4>Sistem Pendukung Keputusan Seleksi Penerima Bantuan PIP</h4>
        <p>Pondok Pesantren Haji Maqbul Hasibuan</p>
    </div>
    <ul class="menu">
        <li><a href="../dashboard.php"><i class="fa fa-house"></i> Dashboard</a></li>
        <li><a href="../kriteria/index.php"><i class="fa fa-list"></i> Data Kriteria</a></li>
        <li><a href="../sub_kriteria/index_subkriteria.php"><i class="fa fa-layer-group"></i> Data Sub Kriteria</a></li>
        <li><a href="../alternatif/index.php"><i class="fa fa-user-graduate"></i> Data Alternatif</a></li>
        <li><a href="../penilaian/index.php"><i class="fa fa-check-circle"></i> Data Penilaian</a></li>
        <li><a href="index.php" class="active"><i class="fa fa-calculator"></i> Data Perhitungan</a></li>
        <li><a href="../hasil/index.php"><i class="fa fa-trophy"></i> Data Hasil Akhir</a></li>
        <li><a href="../user/index.php"><i class="fa fa-users"></i> Data User</a></li>
        <li><a href="#"><i class="fa fa-window-maximize"></i> Kelola Halaman</a></li>
        <li><a href="../../../logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-calculator"></i>
            <h4>Perhitungan Metode Weighted Product</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name">Admin</div>
        </div>
    </div>

    <?php if (!empty($pesan_sukses)): ?>
        <div class="alert alert-success" style="border-radius: 10px;">
            <i class="fa fa-check-circle me-2"></i><?php echo $pesan_sukses; ?>
        </div>
    <?php endif; ?>

    <?php if (!$can_calculate): ?>
        <div class="card-custom text-center py-5">
            <i class="fa fa-triangle-exclamation text-warning mb-3" style="font-size: 50px;"></i>
            <h5>Data Belum Lengkap</h5>
            <p class="text-muted">Pastikan data kriteria, alternatif (siswa), dan penilaian sudah tersedia sebelum melakukan perhitungan.</p>
        </div>
    <?php else: ?>

    <!-- STEP 1: Normalisasi Bobot -->
    <div class="card-custom">
        <div class="step-title">
            <span class="step-num">1</span>
            Normalisasi Bobot Kriteria (W)
        </div>
        <p class="text-muted small mb-3">Bobot dinormalisasi dengan membagi setiap bobot kriteria dengan total bobot. Untuk kriteria <strong>cost</strong>, bobot menjadi negatif.</p>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kriteria</th>
                        <th>Jenis</th>
                        <th>Bobot</th>
                        <th>Bobot Normal (W)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kriteria_arr as $kr): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($kr['kode_kriteria']); ?></td>
                            <td style="text-align: left; padding-left: 15px;"><?php echo htmlspecialchars($kr['nama_kriteria']); ?></td>
                            <td>
                                <span class="badge-<?php echo $kr['jenis']; ?>">
                                    <?php echo ucfirst($kr['jenis']); ?>
                                </span>
                            </td>
                            <td><?php echo $kr['bobot']; ?></td>
                            <td><strong><?php echo number_format($bobot_normal[$kr['id']], 4); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #f5f5f5;">
                        <td colspan="3" style="text-align: right;"><strong>Total Bobot</strong></td>
                        <td><strong><?php echo $total_bobot; ?></strong></td>
                        <td>—</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STEP 2: Matriks Penilaian -->
    <div class="card-custom">
        <div class="step-title">
            <span class="step-num">2</span>
            Matriks Keputusan (X)
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Alternatif</th>
                        <th>Nama</th>
                        <?php foreach ($kriteria_arr as $kr): ?>
                            <th><?php echo htmlspecialchars($kr['kode_kriteria']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siswa_arr as $siswa): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($siswa['kode_alternatif']); ?></td>
                            <td style="text-align: left; padding-left: 15px;"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                            <?php foreach ($kriteria_arr as $kr): ?>
                                <td><?php echo isset($penilaian_map[$siswa['id']][$kr['id']]) ? $penilaian_map[$siswa['id']][$kr['id']] : '-'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STEP 3: Vektor S -->
    <div class="card-custom">
        <div class="step-title">
            <span class="step-num">3</span>
            Perhitungan Vektor S
        </div>
        <p class="text-muted small mb-3">S<sub>i</sub> = ∏ X<sub>ij</sub><sup>W<sub>j</sub></sup> (produk dari setiap nilai dipangkatkan dengan bobot normalnya)</p>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Alternatif</th>
                        <th>Nama</th>
                        <th>Rumus Perhitungan</th>
                        <th>Nilai S</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siswa_arr as $siswa):
                        if (!isset($vektor_s[$siswa['id']])) continue;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($siswa['kode_alternatif']); ?></td>
                            <td style="text-align: left; padding-left: 15px;"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                            <td style="text-align: left; padding-left: 15px; font-size: 12px;">
                                <?php
                                $parts = [];
                                foreach ($kriteria_arr as $kr) {
                                    $n = $penilaian_map[$siswa['id']][$kr['id']] ?? 0;
                                    $w = $bobot_normal[$kr['id']];
                                    $parts[] = "({$n})<sup>" . number_format($w, 4) . "</sup>";
                                }
                                echo implode(' × ', $parts);
                                ?>
                            </td>
                            <td><strong><?php echo number_format($vektor_s[$siswa['id']], 5); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #f5f5f5;">
                        <td colspan="3" style="text-align: right;"><strong>Total Vektor S</strong></td>
                        <td><strong><?php echo number_format($total_s, 5); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STEP 4: Vektor V & Ranking -->
    <div class="card-custom">
        <div class="step-title">
            <span class="step-num">4</span>
            Perhitungan Vektor V &amp; Peringkat
        </div>
        <p class="text-muted small mb-3">V<sub>i</sub> = S<sub>i</sub> / ∑S (preferensi relatif dari setiap alternatif)</p>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="10%">Ranking</th>
                        <th width="12%">Alternatif</th>
                        <th>Nama</th>
                        <th width="15%">Nilai S</th>
                        <th width="15%">Nilai V</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Sort by ranking
                    $sorted = [];
                    foreach ($siswa_arr as $s) {
                        if (isset($ranking[$s['id']])) {
                            $sorted[] = $s;
                        }
                    }
                    usort($sorted, function($a, $b) use ($ranking) {
                        return $ranking[$a['id']] - $ranking[$b['id']];
                    });

                    foreach ($sorted as $siswa):
                        $r = $ranking[$siswa['id']];
                    ?>
                        <tr>
                            <td>
                                <?php if ($r == 1): ?>
                                    <span class="badge bg-danger fs-6"><i class="fa fa-trophy"></i> 1</span>
                                <?php elseif ($r == 2): ?>
                                    <span class="badge bg-warning text-dark fs-6">2</span>
                                <?php elseif ($r == 3): ?>
                                    <span class="badge bg-success fs-6">3</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo $r; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($siswa['kode_alternatif']); ?></td>
                            <td style="text-align: left; padding-left: 15px;"><strong><?php echo htmlspecialchars($siswa['nama']); ?></strong></td>
                            <td><?php echo number_format($vektor_s[$siswa['id']], 5); ?></td>
                            <td><strong><?php echo number_format($vektor_v[$siswa['id']], 5); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-end">
            <a href="index.php?simpan=1" class="btn-simpan-hasil" onclick="return confirm('Simpan hasil perhitungan ke database?')">
                <i class="fa fa-save me-1"></i> Simpan Hasil ke Database
            </a>
        </div>
    </div>

    <?php endif; ?>
</div>

</body>
</html>
