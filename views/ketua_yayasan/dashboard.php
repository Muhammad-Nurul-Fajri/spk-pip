<?php
session_start();
require_once '../../config/koneksi.php';
require_role('ketua_yayasan');

// === SUMMARY CARDS DATA ===
$total_pendaftar = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM siswa"))['c'];
$total_layak = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM hasil_wp WHERE ranking <= 3"))['c'];
$total_tidak = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM hasil_wp WHERE ranking > 3"))['c'];
$total_hasil = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM hasil_wp"))['c'];

// === PIE CHART: Eligible vs Ineligible ===
// Using top 3 as "Layak" threshold (adjustable)
$pie_layak = $total_layak;
$pie_tidak = max(0, $total_hasil - $total_layak);

// === BAR CHART: Top 10 students by Vi score ===
$top10 = [];
$tq = mysqli_query($koneksi, "SELECT h.nilai_v, s.nama FROM hasil_wp h JOIN siswa s ON h.id_siswa=s.id ORDER BY h.ranking ASC LIMIT 10");
while ($r = mysqli_fetch_assoc($tq)) $top10[] = $r;

// === LINE CHART: PIP recipients per year ===
$yearly = [];
$yq = mysqli_query($koneksi, "SELECT tahun_ajaran, jumlah_penerima FROM rekap_pip_tahunan ORDER BY tahun_ajaran ASC");
while ($r = mysqli_fetch_assoc($yq)) $yearly[] = $r;
// Add current year from hasil_wp
$current_year_count = $total_layak;
$yearly[] = ['tahun_ajaran' => '2025-2026', 'jumlah_penerima' => $current_year_count];

// === BAR CHART: Average score per criterion ===
$avg_criteria = [];
$cq = mysqli_query($koneksi, "SELECT k.kode_kriteria, k.nama_kriteria, AVG(p.nilai) as avg_val FROM penilaian p JOIN kriteria k ON p.id_kriteria=k.id GROUP BY k.id ORDER BY k.kode_kriteria");
while ($r = mysqli_fetch_assoc($cq)) $avg_criteria[] = $r;

// === FULL RANKING TABLE ===
$ranking_data = [];
$rq = mysqli_query($koneksi, "SELECT h.*, s.nama, s.kode_alternatif, s.kelas FROM hasil_wp h JOIN siswa s ON h.id_siswa=s.id ORDER BY h.ranking ASC");
while ($r = mysqli_fetch_assoc($rq)) $ranking_data[] = $r;

$page_title = 'Dashboard Ketua Yayasan';
$active_menu = 'dashboard';
$asset_depth = 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../layouts/head.php'; ?>
</head>
<body>
<?php include '../layouts/sidebar_ketua.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-house"></i>
            <h4>Dashboard Ketua Yayasan</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Ketua Yayasan'); ?></div>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-3">
        <div class="col-md-6 col-lg-3">
            <div class="summary-card sc-blue">
                <i class="fa fa-users sc-icon"></i>
                <h3><?php echo $total_pendaftar; ?></h3>
                <p>Total Pendaftar</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="summary-card sc-green">
                <i class="fa fa-circle-check sc-icon"></i>
                <h3><?php echo $total_layak; ?></h3>
                <p>Layak Menerima PIP</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="summary-card sc-red">
                <i class="fa fa-circle-xmark sc-icon"></i>
                <h3><?php echo $pie_tidak; ?></h3>
                <p>Tidak Layak / Cadangan</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="summary-card sc-orange">
                <i class="fa fa-chart-line sc-icon"></i>
                <h3><?php echo $total_hasil; ?></h3>
                <p>Sudah Diproses WP</p>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW 1 -->
    <div class="row g-3 mb-3">
        <!-- PIE: Eligible vs Ineligible -->
        <div class="col-lg-5">
            <div class="card-custom">
                <h6 style="font-weight:bold;margin-bottom:14px;"><i class="fa fa-chart-pie me-2" style="color:var(--primary);"></i>Proporsi Kelayakan</h6>
                <div style="position:relative;height:280px;">
                    <canvas id="chartPie"></canvas>
                </div>
            </div>
        </div>
        <!-- BAR: Top 10 -->
        <div class="col-lg-7">
            <div class="card-custom">
                <h6 style="font-weight:bold;margin-bottom:14px;"><i class="fa fa-ranking-star me-2" style="color:#ff9800;"></i>Top Skor Preferensi (Vi)</h6>
                <div style="position:relative;height:280px;">
                    <canvas id="chartBar"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW 2 -->
    <div class="row g-3 mb-3">
        <!-- LINE: Yearly -->
        <div class="col-lg-6">
            <div class="card-custom">
                <h6 style="font-weight:bold;margin-bottom:14px;"><i class="fa fa-chart-line me-2" style="color:#1e88e5;"></i>Penerima PIP per Tahun Ajaran</h6>
                <div style="position:relative;height:260px;">
                    <canvas id="chartLine"></canvas>
                </div>
            </div>
        </div>
        <!-- BAR: Avg per criterion -->
        <div class="col-lg-6">
            <div class="card-custom">
                <h6 style="font-weight:bold;margin-bottom:14px;"><i class="fa fa-bar-chart me-2" style="color:#7b1fa2;"></i>Rata-rata Skor per Kriteria</h6>
                <div style="position:relative;height:260px;">
                    <canvas id="chartCriteria"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- RANKING TABLE -->
    <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h6 style="font-weight:bold;margin:0;"><i class="fa fa-trophy me-2" style="color:#ff9800;"></i>Perangkingan Penerima PIP</h6>
            <button onclick="window.print()" class="btn-add no-print" style="font-size:12px;padding:8px 16px;">
                <i class="fa fa-print me-1"></i> Cetak Laporan
            </button>
        </div>
        <div class="table-responsive">
            <?php if (!empty($ranking_data)): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="8%">Ranking</th>
                        <th width="10%">Kode</th>
                        <th>Nama Siswa</th>
                        <th width="14%">Kelas</th>
                        <th width="12%">Nilai S</th>
                        <th width="12%">Nilai V</th>
                        <th width="12%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking_data as $row): ?>
                    <tr>
                        <td>
                            <?php if ($row['ranking'] <= 3): ?>
                                <span class="badge bg-<?php echo ['','danger','warning text-dark','success'][$row['ranking']]; ?> fs-6">
                                    <?php if($row['ranking']==1): ?><i class="fa fa-trophy"></i> <?php endif; echo $row['ranking']; ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo $row['ranking']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['kode_alternatif']); ?></td>
                        <td style="text-align:left;padding-left:15px;"><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                        <td><?php echo number_format($row['nilai_s'], 5); ?></td>
                        <td><strong><?php echo number_format($row['nilai_v'], 5); ?></strong></td>
                        <td>
                            <?php if ($row['ranking'] <= 3): ?>
                                <span class="badge bg-success p-2">Layak</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark p-2">Cadangan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fa fa-trophy mb-2" style="font-size:36px;color:#ddd;"></i>
                    <p>Belum ada hasil perhitungan. Hubungi admin untuk melakukan proses WP.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<?php include '../layouts/footer.php'; ?>

<script>
// === PIE CHART ===
new Chart(document.getElementById('chartPie'), {
    type: 'doughnut',
    data: {
        labels: ['Layak', 'Tidak Layak / Cadangan'],
        datasets: [{
            data: [<?php echo $pie_layak; ?>, <?php echo $pie_tidak; ?>],
            backgroundColor: ['#43a047', '#ef5350'],
            borderWidth: 2, borderColor: '#fff'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } }
        }
    }
});

// === BAR CHART: Top 10 ===
new Chart(document.getElementById('chartBar'), {
    type: 'bar',
    data: {
        labels: [<?php echo implode(',', array_map(function($r){ return "'".addslashes($r['nama'])."'"; }, $top10)); ?>],
        datasets: [{
            label: 'Nilai Vi',
            data: [<?php echo implode(',', array_map(function($r){ return round($r['nilai_v'],5); }, $top10)); ?>],
            backgroundColor: 'rgba(76,175,80,0.7)',
            borderColor: '#4caf50', borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { font: { size: 11 } } }, y: { ticks: { font: { size: 11 } } } }
    }
});

// === LINE CHART: Yearly ===
new Chart(document.getElementById('chartLine'), {
    type: 'line',
    data: {
        labels: [<?php echo implode(',', array_map(function($r){ return "'".addslashes($r['tahun_ajaran'])."'"; }, $yearly)); ?>],
        datasets: [{
            label: 'Jumlah Penerima',
            data: [<?php echo implode(',', array_map(function($r){ return $r['jumlah_penerima']; }, $yearly)); ?>],
            borderColor: '#1e88e5', backgroundColor: 'rgba(30,136,229,0.1)',
            fill: true, tension: 0.3, pointRadius: 5, pointBackgroundColor: '#1e88e5'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 5, font: { size: 11 } } }, x: { ticks: { font: { size: 11 } } } }
    }
});

// === BAR CHART: Avg per Criterion ===
new Chart(document.getElementById('chartCriteria'), {
    type: 'bar',
    data: {
        labels: [<?php echo implode(',', array_map(function($r){ return "'".addslashes($r['kode_kriteria'].' - '.$r['nama_kriteria'])."'"; }, $avg_criteria)); ?>],
        datasets: [{
            label: 'Rata-rata Skor',
            data: [<?php echo implode(',', array_map(function($r){ return round($r['avg_val'],2); }, $avg_criteria)); ?>],
            backgroundColor: ['#ef5350','#ff9800','#66bb6a','#42a5f5','#ab47bc','#26a69a'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, max: 5, ticks: { stepSize: 1, font: { size: 11 } } }, x: { ticks: { font: { size: 10 }, maxRotation: 45 } } }
    }
});
</script>
</body>
</html>
