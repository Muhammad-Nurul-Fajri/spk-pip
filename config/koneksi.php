<?php
/**
 * Database connection — mysqli with charset.
 * All queries should use prepared statements.
 */
$koneksi = mysqli_connect("localhost", "root", "", "spk_pip_wp");

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");

/**
 * Helper: check session and role guard.
 * Usage: require_role($required_role);
 */
function require_role($role) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['level']) || $_SESSION['level'] !== $role) {
        // Determine login path relative to current script
        $depth = substr_count(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/') - 1;
        $login_path = str_repeat('../', max(0, $depth)) . 'login.php';
        header("Location: $login_path");
        exit;
    }
}