<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "Running Migration V5...\n";

// 1. Execute DDL statements
$ddl_statements = [
    "ALTER TABLE kriteria MODIFY COLUMN bobot DOUBLE NOT NULL",
    "UPDATE kriteria SET nama_kriteria = 'Pekerjaan Orang Tua', bobot = -0.168, jenis = 'cost' WHERE kode_kriteria = 'C1'",
    "UPDATE kriteria SET nama_kriteria = 'Penghasilan Orang Tua', bobot = -0.371, jenis = 'cost' WHERE kode_kriteria = 'C2'",
    "UPDATE kriteria SET nama_kriteria = 'Jumlah Tanggungan', bobot = 0.249, jenis = 'benefit' WHERE kode_kriteria = 'C3'",
    "UPDATE kriteria SET nama_kriteria = 'Status Pemegang Kartu Kemiskinan', bobot = 0.107, jenis = 'benefit' WHERE kode_kriteria = 'C4'",
    "UPDATE kriteria SET nama_kriteria = 'Nilai Akhir Semester', bobot = 0.065, jenis = 'benefit' WHERE kode_kriteria = 'C5'",
    "UPDATE kriteria SET nama_kriteria = 'Hafalan Al-Qur\'an', bobot = 0.040, jenis = 'benefit' WHERE kode_kriteria = 'C6'"
];

foreach ($ddl_statements as $sql) {
    if (!mysqli_query($koneksi, $sql)) {
        echo "Error: " . mysqli_error($koneksi) . " on SQL: $sql\n";
    }
}

// 2. Truncate and insert sub_kriteria
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($koneksi, "TRUNCATE TABLE sub_kriteria");
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");

$sub_sql = "INSERT INTO sub_kriteria (id, id_kriteria, nama_sub, nilai) VALUES
(1, 1, 'Buruh Harian', 1),
(2, 1, 'Petani / Nelayan', 2),
(3, 1, 'Wiraswasta / Pedagang', 3),
(4, 1, 'Karyawan Swasta', 4),
(5, 1, 'PNS / TNI / Polri', 5),

(6, 2, '<= Rp 500.000', 1),
(7, 2, 'Rp 501.000 - Rp 999.000', 2),
(8, 2, 'Rp 1.000.000 - Rp 1.999.000', 3),
(9, 2, 'Rp 2.000.000 - Rp 4.999.000', 4),
(10, 2, '> Rp 5.000.000', 5),

(11, 3, '1 Orang', 1),
(12, 3, '2 - 3 Orang', 2),
(13, 3, '4 - 5 Orang', 3),
(14, 3, '6 - 7 Orang', 4),
(15, 3, '>= 8 Orang', 5),

(16, 4, 'Tidak Memiliki Kartu Bantuan', 1),
(17, 4, 'Pemegang KKS / PKH / KPS / SKTM', 4),
(18, 4, 'Pemegang KIP (Kartu Indonesia Pintar)', 5),

(19, 5, '< 70', 1),
(20, 5, '70 - 75', 2),
(21, 5, '76 - 80', 3),
(22, 5, '81 - 85', 4),
(23, 5, '86 - 100', 5),

(24, 6, '<= 1 Juz', 1),
(25, 6, '2 Juz', 3),
(26, 6, '>= 3 Juz', 5)";

mysqli_query($koneksi, $sub_sql);

// 3. Add registration_order column if not exists
$check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM siswa LIKE 'registration_order'");
if (mysqli_num_rows($check_col) == 0) {
    mysqli_query($koneksi, "ALTER TABLE siswa ADD COLUMN registration_order INT AFTER id");
}

// 4. Update registration_order & kode_alternatif for existing students
$res = mysqli_query($koneksi, "SELECT id FROM siswa ORDER BY id ASC");
$order = 1;
while ($row = mysqli_fetch_assoc($res)) {
    $sid = $row['id'];
    $kode = 'A' . $order;
    mysqli_query($koneksi, "UPDATE siswa SET registration_order = $order, kode_alternatif = '$kode' WHERE id = $sid");
    $order++;
}

// 5. Update dokumen_pendaftaran column
mysqli_query($koneksi, "ALTER TABLE dokumen_pendaftaran MODIFY COLUMN jenis_dokumen ENUM('kk','ktp_ortu','sertifikat_hafalan','kartu_bantuan','raport')");
mysqli_query($koneksi, "UPDATE dokumen_pendaftaran SET jenis_dokumen = 'sertifikat_hafalan' WHERE jenis_dokumen = 'ktp_ortu'");
mysqli_query($koneksi, "ALTER TABLE dokumen_pendaftaran MODIFY COLUMN jenis_dokumen ENUM('kk','sertifikat_hafalan','kartu_bantuan','raport')");

// 6. Re-map all student penilaian matrix based on updated criteria definition & student data
$sq = mysqli_query($koneksi, "SELECT * FROM siswa");
while ($s = mysqli_fetch_assoc($sq)) {
    $sid = $s['id'];
    
    // C1 Pekerjaan
    $p = $s['pekerjaan_ortu'] ?? '';
    if (str_contains($p, 'Buruh')) $c1 = 1;
    elseif (str_contains($p, 'Petani') || str_contains($p, 'Nelayan')) $c1 = 2;
    elseif (str_contains($p, 'Wiraswasta') || str_contains($p, 'Pedagang')) $c1 = 3;
    elseif (str_contains($p, 'Karyawan')) $c1 = 4;
    elseif (str_contains($p, 'PNS') || str_contains($p, 'TNI') || str_contains($p, 'Polri')) $c1 = 5;
    else $c1 = 1;

    // C2 Penghasilan
    $inc = $s['penghasilan_ortu'] ?? '';
    if (str_contains($inc, '500')) $c2 = 1;
    elseif (str_contains($inc, '501') || str_contains($inc, '999')) $c2 = 2;
    elseif (str_contains($inc, '1.000.000') || str_contains($inc, '1.999.000') || str_contains($inc, '2.000.000')) $c2 = 3;
    elseif (str_contains($inc, '2.000.001') || str_contains($inc, '3.000.000') || str_contains($inc, '4.999.000') || str_contains($inc, '5.000.000')) $c2 = 4;
    elseif (str_contains($inc, '> 5.000.000') || str_contains($inc, '> Rp 5.000.000')) $c2 = 5;
    else $c2 = 3;

    // C3 Tanggungan
    $t = intval($s['jumlah_tanggungan'] ?? 1);
    if ($t >= 8) $c3 = 5;
    elseif ($t >= 6) $c3 = 4;
    elseif ($t >= 4) $c3 = 3;
    elseif ($t >= 2) $c3 = 2;
    else $c3 = 1;

    // C4 Status Kartu
    $k = $s['status_kartu_miskin'] ?? '';
    if (str_contains($k, 'KIP')) $c4 = 5;
    elseif (str_contains($k, 'PKH') || str_contains($k, 'KKS') || str_contains($k, 'KPS') || str_contains($k, 'SKTM')) $c4 = 4;
    else $c4 = 1;

    // C5 Nilai Akhir
    $n = floatval($s['nilai_akhir_semester'] ?? 0);
    if ($n >= 86) $c5 = 5;
    elseif ($n >= 81) $c5 = 4;
    elseif ($n >= 76) $c5 = 3;
    elseif ($n >= 70) $c5 = 2;
    else $c5 = 1;

    // C6 Hafalan Quran
    $h = intval($s['hafalan_quran'] ?? 0);
    if ($h >= 3) $c6 = 5;
    elseif ($h == 2) $c6 = 3;
    else $c6 = 1;

    $scores = [1 => $c1, 2 => $c2, 3 => $c3, 4 => $c4, 5 => $c5, 6 => $c6];
    foreach ($scores as $kid => $score) {
        $check = mysqli_query($koneksi, "SELECT id FROM penilaian WHERE id_siswa = $sid AND id_kriteria = $kid");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($koneksi, "UPDATE penilaian SET nilai = $score WHERE id_siswa = $sid AND id_kriteria = $kid");
        } else {
            mysqli_query($koneksi, "INSERT INTO penilaian (id_siswa, id_kriteria, nilai) VALUES ($sid, $kid, $score)");
        }
    }
}

echo "Migration V5 completed successfully!\n";
