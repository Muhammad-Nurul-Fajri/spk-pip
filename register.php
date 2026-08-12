<?php
session_start();
require_once 'config/koneksi.php';

$pesan_error  = '';
$pesan_sukses = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Server-side validation
    if (empty($nama) || empty($username) || empty($password)) {
        $pesan_error = 'Semua field wajib diisi!';
    } elseif (strlen($password) < 6) {
        $pesan_error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm) {
        $pesan_error = 'Konfirmasi password tidak cocok!';
    } else {
        // Check duplicate username
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $pesan_error = 'Username sudah terdaftar!';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Create user with role siswa
            $stmt2 = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, 'siswa')");
            mysqli_stmt_bind_param($stmt2, "sss", $nama, $username, $hashed);

            if (mysqli_stmt_execute($stmt2)) {
                $id_user = mysqli_insert_id($koneksi);

                // Generate next registration_order & kode_alternatif
                $last = mysqli_query($koneksi, "SELECT MAX(registration_order) as max_order FROM siswa");
                $row = mysqli_fetch_assoc($last);
                $next_order = ($row && isset($row['max_order'])) ? (intval($row['max_order']) + 1) : 1;
                $kode = 'A' . $next_order;

                // Create siswa record (draft status — to be completed via pendaftaran form)
                $stmt3 = mysqli_prepare($koneksi, "INSERT INTO siswa (id_user, registration_order, kode_alternatif, nama, status_pendaftaran) VALUES (?, ?, ?, ?, 'draft')");
                mysqli_stmt_bind_param($stmt3, "iiss", $id_user, $next_order, $kode, $nama);
                mysqli_stmt_execute($stmt3);
                mysqli_stmt_close($stmt3);

                $pesan_sukses = 'Registrasi berhasil! Silakan login dan lengkapi data pendaftaran PIP.';
            } else {
                $pesan_error = 'Gagal menyimpan: ' . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt2);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun — SPK PIP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
body { min-height: 100vh; display: flex; justify-content: center; align-items: center; background: linear-gradient(135deg, #f5f9f2, #edf5e7); padding: 20px; }
.register-box { width: 480px; background: white; border-radius: 22px; padding: 40px; box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
.register-box img { width: 80px; display: block; margin: 0 auto 10px; }
.register-box h2 { text-align: center; color: #2e7d32; font-size: 24px; font-weight: bold; margin-bottom: 6px; }
.register-box p.sub { text-align: center; color: #777; font-size: 13px; margin-bottom: 24px; }
.form-control { height: 44px; border-radius: 10px; border: 1px solid #d6e4d3; background: #fafdf8; padding-left: 14px; font-size: 13px; }
.form-control:focus { border-color: #8bc34a; box-shadow: 0 0 0 0.15rem rgba(46,125,50,0.15); background: white; }
.btn-register-submit { width: 100%; height: 44px; border: none; border-radius: 10px; background: linear-gradient(135deg, #2e7d32, #8bc34a); color: white; font-size: 14px; font-weight: bold; transition: 0.3s; }
.btn-register-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(46,125,50,0.2); }
.back-link { display: block; text-align: center; margin-top: 16px; color: #1565c0; font-size: 13px; font-weight: 600; }
.back-link:hover { color: #1b5e20; }
@media(max-width: 576px) { .register-box { width: 100%; padding: 25px 20px; border-radius: 14px; } }
</style>
</head>
<body>

<div class="register-box">
    <img src="public/assets/img/logo.png" alt="Logo">
    <h2>Daftar Akun Siswa</h2>
    <p class="sub">Buat akun untuk mengajukan permohonan bantuan PIP</p>

    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success" style="border-radius: 10px; font-size: 13px;">
            <i class="fa fa-check-circle me-1"></i><?php echo htmlspecialchars($pesan_sukses); ?>
            <br><a href="login.php" class="fw-bold">Klik di sini untuk login →</a>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    text: 'Pendaftaran berhasil. Silakan login menggunakan akun yang telah didaftarkan.',
                    confirmButtonColor: '#2e7d32',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = 'login.php';
                });
            });
        </script>
    <?php endif; ?>
    <?php if ($pesan_error): ?>
        <div class="alert alert-danger" style="border-radius: 10px; font-size: 13px;">
            <?php echo $pesan_error; ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" id="formRegister">
        <div class="mb-3">
            <label class="form-label fw-bold" style="font-size:13px;">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required
                   value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold" style="font-size:13px;">Username</label>
            <input type="text" name="username" class="form-control" required
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold" style="font-size:13px;">Password</label>
            <input type="password" name="password" class="form-control" required minlength="6" id="pw">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold" style="font-size:13px;">Konfirmasi Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6" id="cpw">
            <div id="pw-error" style="color:#dc3545;font-size:12px;margin-top:4px;display:none;">Password tidak cocok!</div>
        </div>
        <button type="submit" class="btn-register-submit">DAFTAR</button>
    </form>
    <a href="login.php" class="back-link">← Sudah punya akun? Login</a>
</div>

<script>
document.getElementById('formRegister').addEventListener('submit', function(e){
    var pw = document.getElementById('pw').value;
    var cpw = document.getElementById('cpw').value;
    if(pw !== cpw) { e.preventDefault(); document.getElementById('pw-error').style.display='block'; }
});
</script>
</body>
</html>
