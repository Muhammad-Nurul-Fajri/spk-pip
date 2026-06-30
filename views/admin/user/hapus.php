<?php
require_once '../../../config/koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    mysqli_query($koneksi, "DELETE FROM users WHERE id = $id");
}

header('Location: index.php');
exit;
