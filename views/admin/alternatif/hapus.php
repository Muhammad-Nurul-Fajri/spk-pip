<?php
require_once '../../../config/koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Delete related penilaian first
    mysqli_query($koneksi, "DELETE FROM penilaian WHERE id_siswa = $id");
    // Delete related hasil_wp
    mysqli_query($koneksi, "DELETE FROM hasil_wp WHERE id_siswa = $id");
    // Delete siswa
    mysqli_query($koneksi, "DELETE FROM siswa WHERE id = $id");
}

header('Location: index.php');
exit;
