<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../app/helpers/AhpWpHelper.php';

echo "Testing WP Calculation Engine...\n\n";

$wp_res = AhpWpHelper::processWP($koneksi);

if (!$wp_res['can_calculate']) {
    echo "WP Error: " . $wp_res['message'] . "\n";
    exit(1);
}

echo "1. Normalized AHP Weights:\n";
foreach ($wp_res['kriteria'] as $kr) {
    echo sprintf("   %s (%s): %0.3f\n", $kr['kode_kriteria'], $kr['nama_kriteria'], $kr['bobot']);
}

echo "\n2. Decision Matrix & Vector S / V / Ranking:\n";
echo sprintf("%-6s | %-6s | %-20s | C1 | C2 | C3 | C4 | C5 | C6 | Vector S | Vector V | Rank\n", "RegOrd", "Code", "Name");
echo str_repeat("-", 100) . "\n";

foreach ($wp_res['siswa'] as $s) {
    $sid = $s['id'];
    $c1 = $wp_res['penilaian_map'][$sid][1] ?? 0;
    $c2 = $wp_res['penilaian_map'][$sid][2] ?? 0;
    $c3 = $wp_res['penilaian_map'][$sid][3] ?? 0;
    $c4 = $wp_res['penilaian_map'][$sid][4] ?? 0;
    $c5 = $wp_res['penilaian_map'][$sid][5] ?? 0;
    $c6 = $wp_res['penilaian_map'][$sid][6] ?? 0;
    $s_val = $wp_res['vektor_s'][$sid] ?? 0;
    $v_val = $wp_res['vektor_v'][$sid] ?? 0;
    $rank = $wp_res['ranking'][$sid] ?? 0;

    echo sprintf("%-6d | %-6s | %-20s | %2d | %2d | %2d | %2d | %2d | %2d | %8.5f | %8.5f | %4d\n",
        $s['registration_order'], $s['kode_alternatif'], $s['nama'],
        $c1, $c2, $c3, $c4, $c5, $c6, $s_val, $v_val, $rank
    );
}

// Automatically save to database
mysqli_query($koneksi, "DELETE FROM hasil_wp");
$st = mysqli_prepare($koneksi, "INSERT INTO hasil_wp (id_siswa, nilai_s, nilai_v, ranking, status_verifikasi) VALUES (?, ?, ?, ?, 'layak')");
foreach ($wp_res['vektor_v'] as $id_siswa => $v_val) {
    $s_val = $wp_res['vektor_s'][$id_siswa];
    $r = $wp_res['ranking'][$id_siswa];
    mysqli_stmt_bind_param($st, "iddi", $id_siswa, $s_val, $v_val, $r);
    mysqli_stmt_execute($st);
}
mysqli_stmt_close($st);

echo "\nResults saved to hasil_wp table successfully!\n";
