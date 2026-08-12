<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../app/helpers/AhpWpHelper.php';

echo "=== RUNNING ALL SYSTEM TEST CASES ===\n\n";

$pass_count = 0;
$total_tests = 0;

function assertTest($name, $cond) {
    global $pass_count, $total_tests;
    $total_tests++;
    if ($cond) {
        echo "[PASS] $name\n";
        $pass_count++;
    } else {
        echo "[FAIL] $name\n";
    }
}

// 1. Check Criteria Order and Normalized Weights
$kriteria = AhpWpHelper::getKriteria($koneksi);
$expected_weights = [
    'C1' => -0.168,
    'C2' => -0.371,
    'C3' => 0.249,
    'C4' => 0.107,
    'C5' => 0.065,
    'C6' => 0.040
];

$codes = array_column($kriteria, 'kode_kriteria');
assertTest("Criteria Order is C1 -> C6", implode(',', $codes) === 'C1,C2,C3,C4,C5,C6');

foreach ($kriteria as $kr) {
    $code = $kr['kode_kriteria'];
    $exp = $expected_weights[$code];
    $actual = floatval($kr['bobot']);
    assertTest("Weight $code is $exp (Actual: $actual)", abs($actual - $exp) < 0.001);
}

// 2. C6 Mapping Test Cases
function mapC6($hafalan) {
    if ($hafalan >= 3) return 5;
    elseif ($hafalan == 2) return 3;
    else return 1;
}

assertTest("TEST 1: Input <= 1 Juz -> C6 = 1", mapC6(1) === 1 && mapC6(0) === 1);
assertTest("TEST 2: Input 2 Juz -> C6 = 3", mapC6(2) === 3);
assertTest("TEST 3: Input 3 Juz -> C6 = 5", mapC6(3) === 5);
assertTest("TEST 4: Input 5 Juz -> C6 = 5", mapC6(5) === 5);

// 3. Decision Matrix C6 score verification
$wp_res = AhpWpHelper::processWP($koneksi);
$all_valid_c6 = true;
foreach ($wp_res['siswa'] as $s) {
    $c6_val = $wp_res['penilaian_map'][$s['id']][6] ?? 0;
    if (!in_array($c6_val, [1, 3, 5])) {
        $all_valid_c6 = false;
        echo "Invalid C6 score for student ID {$s['id']}: $c6_val\n";
    }
}
assertTest("TEST 5: Decision Matrix C6 values are mapped scores (1, 3, or 5)", $all_valid_c6);

// 4. Alternative Code Stability Verification
$reg_orders = [];
$alt_codes = [];
$res = mysqli_query($koneksi, "SELECT id, registration_order, kode_alternatif FROM siswa ORDER BY registration_order ASC");
while ($r = mysqli_fetch_assoc($res)) {
    $reg_orders[] = $r['registration_order'];
    $alt_codes[] = $r['kode_alternatif'];
}

$is_stable = true;
for ($i = 0; $i < count($reg_orders); $i++) {
    if ('A' . $reg_orders[$i] !== $alt_codes[$i]) {
        $is_stable = false;
    }
}
assertTest("Alternative Code matches A + registration_order", $is_stable);

echo "\n======================================\n";
echo "SUMMARY: $pass_count / $total_tests tests passed.\n";
echo "======================================\n";
