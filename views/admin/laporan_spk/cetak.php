<?php
session_start();
require_once '../../../config/koneksi.php';
require_once '../../../app/helpers/AhpWpHelper.php';
require_role('admin');

$ahp_res = AhpWpHelper::processAHP($koneksi);
$wp_res  = AhpWpHelper::processWP($koneksi);

$logo_path = '../../../public/assets/img/logo.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Komprehensif SPK AHP + WP - PIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #000; background: #fff; padding: 10px; }
        @page { size: A4 landscape; margin: 12mm; }
        .header-container { display: flex; align-items: center; justify-content: center; border-bottom: 4px double #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header-logo { width: 75px; height: auto; margin-right: 20px; }
        .header-text { text-align: center; }
        .header-text h2 { font-size: 18px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .header-text h3 { font-size: 15px; margin: 4px 0 0 0; text-transform: uppercase; }
        .header-text p { font-size: 11px; margin: 3px 0 0 0; font-style: italic; }
        .report-title { text-align: center; margin-bottom: 15px; }
        .report-title h4 { font-size: 15px; font-weight: bold; margin: 0; text-decoration: underline; text-transform: uppercase; }
        .table-custom { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .table-custom th { background-color: #f2f2f2 !important; color: #000 !important; border: 1px solid #000 !important; font-weight: bold; text-align: center; padding: 6px; font-size: 11px; text-transform: uppercase; }
        .table-custom td { border: 1px solid #000 !important; padding: 6px; font-size: 11px; text-align: center; }
        .signature-container { display: flex; justify-content: space-between; margin-top: 30px; font-size: 12px; page-break-inside: avoid; }
        .signature-box { text-align: center; width: 220px; }
        .signature-box.right { margin-left: auto; }
        .signature-space { height: 60px; }
        @media print { .no-print { display: none !important; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row mb-3 no-print justify-content-end">
            <div class="col-auto">
                <button onclick="window.print()" class="btn btn-primary me-2"><i class="fa fa-print"></i> Cetak</button>
                <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
            </div>
        </div>

        <div class="header-container">
            <img src="<?php echo $logo_path; ?>" class="header-logo" alt="Logo Sekolah">
            <div class="header-text">
                <h2>Pondok Pesantren Haji Maqbul Hasibuan</h2>
                <h3>Laporan Metodologi SPK (AHP + Weighted Product)</h3>
                <p>Alamat: Sibuhuan, Kab. Padang Lawas, Sumatera Utara - Kode Pos: 22763</p>
            </div>
        </div>

        <div class="report-title">
            <h4>Laporan Hasil Evaluasi Penentuan Penerima Bantuan PIP</h4>
            <p style="font-size: 12px; margin-top: 4px;">Penerapan Metode Analytical Hierarchy Process (AHP) &amp; Weighted Product (WP)</p>
        </div>

        <!-- AHP Summary Table -->
        <h6 style="font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px;">1. Bobot Kriteria &amp; Uji Konsistensi AHP</h6>
        <?php if ($ahp_res['success']): ?>
            <div class="row mb-3">
                <div class="col-6">
                    <table class="table table-custom">
                        <thead>
                            <tr><th colspan="2">Parameter Konsistensi Matriks</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="text-start">Max Eigenvalue (λmax)</td><td><strong><?php echo number_format($ahp_res['lambda_max'], 4); ?></strong></td></tr>
                            <tr><td class="text-start">Consistency Index (CI)</td><td><strong><?php echo number_format($ahp_res['ci'], 4); ?></strong></td></tr>
                            <tr><td class="text-start">Random Index (RI)</td><td><strong><?php echo number_format($ahp_res['ri'], 2); ?></strong></td></tr>
                            <tr><td class="text-start">Consistency Ratio (CR)</td><td><strong><?php echo number_format($ahp_res['cr'], 4); ?></strong></td></tr>
                            <tr><td class="text-start">Status Konsistensi</td><td><strong><?php echo strtoupper($ahp_res['status']); ?> (CR ≤ 0.10)</strong></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-6">
                    <table class="table table-custom">
                        <thead>
                            <tr><th>Kode</th><th>Nama Kriteria</th><th>Bobot Priority Vector</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ahp_res['kriteria'] as $k): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($k['kode_kriteria']); ?></strong></td>
                                    <td class="text-start"><?php echo htmlspecialchars($k['nama_kriteria']); ?></td>
                                    <td><strong><?php echo number_format($ahp_res['priority_vector'][$k['id']], 4); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- WP Ranking Table -->
        <h6 style="font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px;">2. Hasil Rekomendasi &amp; Perangkingan Weighted Product</h6>
        <?php if ($wp_res['can_calculate']): ?>
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th width="8%">Ranking</th>
                        <th width="10%">Kode</th>
                        <th width="15%">NIS / NISN</th>
                        <th>Nama Lengkap Siswa</th>
                        <th width="12%">Kelas</th>
                        <th width="12%">Nilai S</th>
                        <th width="12%">Nilai Preferensi (V)</th>
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
                    foreach ($sorted as $siswa): 
                        $s_id = $siswa['id'] ?? 0;
                        $r = $wp_res['ranking'][$s_id] ?? '-';
                        $v_s = $wp_res['vektor_s'][$s_id] ?? null;
                        $v_v = $wp_res['vektor_v'][$s_id] ?? null;
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($r); ?></strong></td>
                            <td><?php echo htmlspecialchars($siswa['kode_alternatif'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($siswa['nis'] ?? '-'); ?> / <?php echo htmlspecialchars($siswa['nisn'] ?: '-'); ?></td>
                            <td class="text-start"><strong><?php echo htmlspecialchars($siswa['nama'] ?? '-'); ?></strong></td>
                            <td><?php echo htmlspecialchars($siswa['kelas'] ?? '-'); ?></td>
                            <td><?php echo ($v_s !== null) ? number_format(floatval($v_s), 5) : '-'; ?></td>
                            <td><strong><?php echo ($v_v !== null) ? number_format(floatval($v_v), 5) : '-'; ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="signature-container">
            <div class="signature-box">
                <p>Mengetahui,</p>
                <p class="fw-bold">Panitia Pelaksana PIP</p>
                <div class="signature-space"></div>
                <p class="fw-bold text-decoration-underline"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?></p>
                <p class="text-muted" style="font-size: 10px; margin-top: -3px;">Staf Administrasi</p>
            </div>
            <div class="signature-box right">
                <p>Sibuhuan, <?php echo date('d F Y'); ?></p>
                <p class="fw-bold">Ketua Yayasan</p>
                <div class="signature-space"></div>
                <p class="fw-bold text-decoration-underline">H. Maqbul Hasibuan</p>
                <p class="text-muted" style="font-size: 10px; margin-top: -3px;">Ketua Yayasan PP Haji Maqbul</p>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>
