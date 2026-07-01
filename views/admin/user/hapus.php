<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
header("Location: index.php"); exit;
