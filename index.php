<?php
session_start();

if (isset($_SESSION['level'])) {
    if ($_SESSION['level'] == 'admin') {
        header("Location: views/admin/dashboard.php");
        exit;
    } elseif ($_SESSION['level'] == 'siswa') {
        header("Location: views/siswa/dashboard.php");
        exit;
    } elseif ($_SESSION['level'] == 'ketua_yayasan') {
        header("Location: views/ketua_yayasan/dashboard.php");
        exit;
    }
}

// If not logged in, redirect to login page
header("Location: login.php");
exit;
