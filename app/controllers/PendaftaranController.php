<?php
/**
 * PendaftaranController — handles student PIP application form submissions.
 * Maps application data to penilaian table for WP calculation.
 */
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'siswa') {
    header("Location: ../../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Get siswa record for this user
$stmt = mysqli_prepare($koneksi, "SELECT * FROM siswa WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$siswa) {
    echo "<script>alert('Data siswa tidak ditemukan.');window.location='../../views/siswa/dashboard.php';</script>";
    exit;
}

$id_siswa = $siswa['id'];

// Only allow edit if status is draft or submitted
if (!in_array($siswa['status_pendaftaran'], ['draft', 'submitted'])) {
    echo "<script>alert('Data sudah diverifikasi dan tidak dapat diubah.');window.location='../../views/siswa/dashboard.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/siswa/pendaftaran.php");
    exit;
}

// Collect form data
$nama           = trim($_POST['nama'] ?? '');
$nis            = trim($_POST['nis'] ?? '');
$nisn           = trim($_POST['nisn'] ?? '');
$kelas          = trim($_POST['kelas'] ?? '');
$tempat_lahir   = trim($_POST['tempat_lahir'] ?? '');
$tanggal_lahir  = $_POST['tanggal_lahir'] ?? '';
$alamat         = trim($_POST['alamat'] ?? '');
$no_hp          = trim($_POST['no_hp'] ?? '');
$pekerjaan_ortu = $_POST['pekerjaan_ortu'] ?? '';
$penghasilan_ortu = $_POST['penghasilan_ortu'] ?? '';
$jumlah_tanggungan = intval($_POST['jumlah_tanggungan'] ?? 0);
$status_kartu   = $_POST['status_kartu_miskin'] ?? '';
$nilai_akhir    = floatval($_POST['nilai_akhir_semester'] ?? 0);
$hafalan_quran  = intval($_POST['hafalan_quran'] ?? 0);
$action         = $_POST['action'] ?? 'save_draft'; // save_draft or submit

// Server-side validation
$errors = [];
if (empty($nama)) $errors[] = 'Nama lengkap wajib diisi';
if (empty($nis)) $errors[] = 'NIS wajib diisi';
if (empty($kelas)) $errors[] = 'Kelas wajib diisi';
if (empty($pekerjaan_ortu)) $errors[] = 'Pekerjaan orang tua wajib dipilih';
if (empty($penghasilan_ortu)) $errors[] = 'Penghasilan orang tua wajib dipilih';
if ($jumlah_tanggungan < 1) $errors[] = 'Jumlah tanggungan minimal 1';
if (empty($status_kartu)) $errors[] = 'Status kartu kemiskinan wajib dipilih';
if ($nilai_akhir <= 0 || $nilai_akhir > 100) $errors[] = 'Nilai akhir semester harus antara 1-100';
if ($hafalan_quran < 0) $errors[] = 'Hafalan Quran tidak boleh negatif';

if (!empty($errors)) {
    $_SESSION['pendaftaran_errors'] = $errors;
    $_SESSION['pendaftaran_old'] = $_POST;
    header("Location: ../../views/siswa/pendaftaran.php");
    exit;
}

// Handle photo upload
$foto = $siswa['foto']; // keep existing
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 2 * 1024 * 1024; // 2MB
    if (in_array($_FILES['foto']['type'], $allowed) && $_FILES['foto']['size'] <= $max_size) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = 'foto_' . $id_siswa . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../../public/uploads/foto/' . $foto_name);
        $foto = $foto_name;
    }
}

// Handle document uploads
$doc_types = ['kk', 'sertifikat_hafalan', 'kartu_bantuan', 'raport'];
foreach ($doc_types as $dt) {
    if (isset($_FILES[$dt]) && $_FILES[$dt]['error'] === UPLOAD_ERR_OK) {
        $allowed_doc = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $max_doc = 5 * 1024 * 1024; // 5MB
        if (in_array($_FILES[$dt]['type'], $allowed_doc) && $_FILES[$dt]['size'] <= $max_doc) {
            $ext = pathinfo($_FILES[$dt]['name'], PATHINFO_EXTENSION);
            $doc_name = $dt . '_' . $id_siswa . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES[$dt]['tmp_name'], '../../public/uploads/dokumen/' . $doc_name);

            // Check if doc exists for this siswa+type
            $cek = mysqli_prepare($koneksi, "SELECT id FROM dokumen_pendaftaran WHERE id_siswa = ? AND jenis_dokumen = ?");
            mysqli_stmt_bind_param($cek, "is", $id_siswa, $dt);
            mysqli_stmt_execute($cek);
            $cek_res = mysqli_stmt_get_result($cek);
            if (mysqli_num_rows($cek_res) > 0) {
                $upd = mysqli_prepare($koneksi, "UPDATE dokumen_pendaftaran SET nama_file = ?, uploaded_at = NOW() WHERE id_siswa = ? AND jenis_dokumen = ?");
                mysqli_stmt_bind_param($upd, "sis", $doc_name, $id_siswa, $dt);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            } else {
                $ins = mysqli_prepare($koneksi, "INSERT INTO dokumen_pendaftaran (id_siswa, jenis_dokumen, nama_file) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($ins, "iss", $id_siswa, $dt, $doc_name);
                mysqli_stmt_execute($ins);
                mysqli_stmt_close($ins);
            }
            mysqli_stmt_close($cek);
        }
    }
}

// Determine status
$new_status = ($action === 'submit') ? 'submitted' : 'draft';

// Update siswa record
$stmt_upd = mysqli_prepare($koneksi,
    "UPDATE siswa SET nama=?, nis=?, nisn=?, kelas=?, tempat_lahir=?, tanggal_lahir=?, alamat=?, no_hp=?, foto=?,
     pekerjaan_ortu=?, penghasilan_ortu=?, jumlah_tanggungan=?, status_kartu_miskin=?,
     nilai_akhir_semester=?, hafalan_quran=?, status_pendaftaran=?
     WHERE id=?"
);
mysqli_stmt_bind_param($stmt_upd, "sssssssssssisidsi",
    $nama, $nis, $nisn, $kelas, $tempat_lahir, $tanggal_lahir, $alamat, $no_hp, $foto,
    $pekerjaan_ortu, $penghasilan_ortu, $jumlah_tanggungan, $status_kartu,
    $nilai_akhir, $hafalan_quran, $new_status, $id_siswa
);
mysqli_stmt_execute($stmt_upd);
mysqli_stmt_close($stmt_upd);

// Also update user nama
$stmt_user = mysqli_prepare($koneksi, "UPDATE users SET nama = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt_user, "si", $nama, $id_user);
mysqli_stmt_execute($stmt_user);
mysqli_stmt_close($stmt_user);

// ===================================================
// AUTO-MAP to penilaian table (WP calculation input)
// ===================================================
// Map application data to sub_kriteria nilai scores

$sub_map = [];
$sq = mysqli_query($koneksi, "SELECT id_kriteria, nama_sub, nilai FROM sub_kriteria ORDER BY id_kriteria, nilai DESC");
while ($r = mysqli_fetch_assoc($sq)) {
    $sub_map[$r['id_kriteria']][$r['nama_sub']] = $r['nilai'];
}

$scores = [];
// C1 Pekerjaan
$scores[1] = $sub_map[1][$pekerjaan_ortu] ?? 1;
// C2 Penghasilan
$scores[2] = $sub_map[2][$penghasilan_ortu] ?? 1;
// C3 Jumlah Tanggungan
if ($jumlah_tanggungan >= 8) $scores[3] = 5;
elseif ($jumlah_tanggungan >= 6) $scores[3] = 4;
elseif ($jumlah_tanggungan >= 4) $scores[3] = 3;
elseif ($jumlah_tanggungan >= 2) $scores[3] = 2;
else $scores[3] = 1;
// C4 Status Kartu
$scores[4] = $sub_map[4][$status_kartu] ?? 1;
// C5 Nilai Akhir
if ($nilai_akhir >= 86) $scores[5] = 5;
elseif ($nilai_akhir >= 81) $scores[5] = 4;
elseif ($nilai_akhir >= 76) $scores[5] = 3;
elseif ($nilai_akhir >= 70) $scores[5] = 2;
else $scores[5] = 1;
// C6 Hafalan (Exact mapping: <=1 Juz -> 1, 2 Juz -> 3, >=3 Juz -> 5)
if ($hafalan_quran >= 3) $scores[6] = 5;
elseif ($hafalan_quran == 2) $scores[6] = 3;
else $scores[6] = 1;

// Upsert penilaian for each criterion
foreach ($scores as $id_kriteria => $nilai) {
    $cek_p = mysqli_prepare($koneksi, "SELECT id FROM penilaian WHERE id_siswa = ? AND id_kriteria = ?");
    mysqli_stmt_bind_param($cek_p, "ii", $id_siswa, $id_kriteria);
    mysqli_stmt_execute($cek_p);
    $res_p = mysqli_stmt_get_result($cek_p);
    if (mysqli_num_rows($res_p) > 0) {
        $upd_p = mysqli_prepare($koneksi, "UPDATE penilaian SET nilai = ? WHERE id_siswa = ? AND id_kriteria = ?");
        mysqli_stmt_bind_param($upd_p, "dii", $nilai, $id_siswa, $id_kriteria);
        mysqli_stmt_execute($upd_p);
        mysqli_stmt_close($upd_p);
    } else {
        $ins_p = mysqli_prepare($koneksi, "INSERT INTO penilaian (id_siswa, id_kriteria, nilai) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($ins_p, "iid", $id_siswa, $id_kriteria, $nilai);
        mysqli_stmt_execute($ins_p);
        mysqli_stmt_close($ins_p);
    }
    mysqli_stmt_close($cek_p);
}

$_SESSION['pendaftaran_success'] = ($action === 'submit')
    ? 'Pendaftaran berhasil diajukan! Data Anda akan diverifikasi oleh admin.'
    : 'Data berhasil disimpan sebagai draft.';

header("Location: ../../views/siswa/dashboard.php");
exit;
