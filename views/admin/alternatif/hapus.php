<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    // Delete related penilaian and hasil_wp first (cascade should handle, but be explicit)
    $stmt1 = mysqli_prepare($koneksi, "DELETE FROM penilaian WHERE id_siswa=?");
    mysqli_stmt_bind_param($stmt1, "i", $id); mysqli_stmt_execute($stmt1); mysqli_stmt_close($stmt1);
    $stmt2 = mysqli_prepare($koneksi, "DELETE FROM hasil_wp WHERE id_siswa=?");
    mysqli_stmt_bind_param($stmt2, "i", $id); mysqli_stmt_execute($stmt2); mysqli_stmt_close($stmt2);
    $stmt3 = mysqli_prepare($koneksi, "DELETE FROM dokumen_pendaftaran WHERE id_siswa=?");
    mysqli_stmt_bind_param($stmt3, "i", $id); mysqli_stmt_execute($stmt3); mysqli_stmt_close($stmt3);
    $stmt4 = mysqli_prepare($koneksi, "DELETE FROM siswa WHERE id=?");
    mysqli_stmt_bind_param($stmt4, "i", $id); mysqli_stmt_execute($stmt4); mysqli_stmt_close($stmt4);
}
header("Location: index.php"); exit;
