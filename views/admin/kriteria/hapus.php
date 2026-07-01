<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');
$id = intval($_GET['id'] ?? 0);
if ($id > 0) { $s = mysqli_prepare($koneksi, "DELETE FROM kriteria WHERE id=?"); mysqli_stmt_bind_param($s, "i", $id); mysqli_stmt_execute($s); mysqli_stmt_close($s); }
header("Location: index.php"); exit;