<?php
/**
 * Authentication Controller
 * Uses prepared statements + password_verify for bcrypt hashes.
 */
session_start();
require_once '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo "<script>alert('Username dan Password wajib diisi!');window.location='../../login.php';</script>";
    exit;
}

// Prepared statement — prevent SQL injection
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);

    // Verify password with bcrypt hash
    if (password_verify($password, $data['password'])) {
        $_SESSION['id_user']  = $data['id'];
        $_SESSION['nama']     = $data['nama'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['level']    = $data['role'];

        switch ($data['role']) {
            case 'admin':
                header("Location: ../../views/admin/dashboard.php");
                break;
            case 'siswa':
                header("Location: ../../views/siswa/dashboard.php");
                break;
            case 'ketua_yayasan':
                header("Location: ../../views/ketua_yayasan/dashboard.php");
                break;
            default:
                header("Location: ../../login.php");
        }
        exit;
    }
}

mysqli_stmt_close($stmt);

echo "<script>alert('Username atau Password Salah!');window.location='../../login.php';</script>";
exit;