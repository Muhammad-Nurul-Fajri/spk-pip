<?php
session_start();
require_once 'config/koneksi.php';

$pesan_error = '';
$pesan_sukses = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    
    if (empty($nama) || empty($username) || empty($password)) {
        $pesan_error = 'Semua field wajib diisi!';
    } elseif ($password !== $konfirmasi) {
        $pesan_error = 'Konfirmasi password tidak cocok!';
    } else {
        // Cek username duplikat
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek) > 0) {
            $pesan_error = 'Username sudah terdaftar!';
        } else {
            // Mulai transaksi
            mysqli_begin_transaction($koneksi);
            try {
                // Insert user
                $query_user = mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', 'siswa')");
                if (!$query_user) {
                    throw new Exception("Gagal membuat user account: " . mysqli_error($koneksi));
                }
                
                // Dapatkan id terakhir
                $user_id = mysqli_insert_id($koneksi);
                
                // Cari kode alternatif berikutnya
                $res = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa");
                $row = mysqli_fetch_assoc($res);
                $next_num = $row['total'] + 1;
                $kode_alt = "A" . $next_num;
                
                // Pastikan kode_alternatif unik
                while (mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM siswa WHERE kode_alternatif = '$kode_alt'")) > 0) {
                    $next_num++;
                    $kode_alt = "A" . $next_num;
                }
                
                // Insert siswa (menggunakan id yang sama dengan users untuk mempermudah relasi)
                $query_siswa = mysqli_query($koneksi, "INSERT INTO siswa (id, kode_alternatif, nama, kelas) VALUES ($user_id, '$kode_alt', '$nama', 'Belum Diatur')");
                if (!$query_siswa) {
                    throw new Exception("Gagal membuat profil siswa: " . mysqli_error($koneksi));
                }
                
                // Insert penilaian default untuk kriteria
                $kriterias = mysqli_query($koneksi, "SELECT id FROM kriteria");
                while ($kr = mysqli_fetch_assoc($kriterias)) {
                    $id_krit = $kr['id'];
                    mysqli_query($koneksi, "INSERT INTO penilaian (id_siswa, id_kriteria, nilai) VALUES ($user_id, $id_krit, 1)");
                }
                
                mysqli_commit($koneksi);
                $pesan_sukses = 'Pendaftaran berhasil! Silakan login.';
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $pesan_error = 'Pendaftaran gagal: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun SPK PIP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}
body {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #f5f9f2, #edf5e7);
}
.register-box {
    width: 900px;
    height: 550px;
    background: white;
    border-radius: 28px;
    overflow: hidden;
    display: flex;
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}
.left-side {
    width: 50%;
    background: linear-gradient(135deg, #4caf50, #C6D166);
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    padding: 35px;
    color: white;
    position: relative;
    text-align: center;
}
.left-side::before {
    content: '';
    position: absolute;
    width: 230px;
    height: 230px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    top: -70px;
    left: -70px;
}
.left-side::after {
    content: '';
    position: absolute;
    width: 150px;
    height: 150px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    bottom: -40px;
    right: -40px;
}
.left-side img {
    width: 170px;
    margin-bottom: 20px;
    z-index: 2;
}
.left-side h2 {
    font-size: 26px;
    font-weight: bold;
    line-height: 1.5;
    z-index: 2;
    margin-bottom: 12px;
}
.left-side h3 {
    font-size: 15px;
    font-weight: 500;
    line-height: 1.6;
    z-index: 2;
    color: #f8fafc;
}
.middle-line {
    width: 1px;
    background: #e5e7eb;
}
.right-side {
    width: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: white;
}
.form-box {
    width: 78%;
}
.title {
    text-align: center;
    color: #8aa12d;
    font-size: 27px;
    font-weight: bold;
    margin-bottom: 20px;
}
.form-control {
    height: 40px;
    border-radius: 10px;
    border: 1px solid #d6e4d3;
    background: #fafdf8;
    padding-left: 14px;
    font-size: 13px;
}
.form-control:focus {
    border-color: #C6D166;
    box-shadow: 0 0 0 0.15rem rgba(76,175,80,0.15);
    background: white;
}
.btn-register {
    width: 100%;
    height: 42px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #4caf50, #C6D166);
    color: white;
    font-size: 13px;
    font-weight: bold;
    transition: 0.3s;
}
.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(46,125,50,0.2);
}
.login-link {
    margin-top: 18px;
}
.line-text {
    text-align: center;
    position: relative;
    margin-bottom: 14px;
}
.line-text::before {
    content: '';
    position: absolute;
    width: 35%;
    height: 1px;
    background: #d1d5db;
    left: 0;
    top: 50%;
}
.line-text::after {
    content: '';
    position: absolute;
    width: 35%;
    height: 1px;
    background: #d1d5db;
    right: 0;
    top: 50%;
}
.line-text span {
    background: white;
    padding: 0 10px;
    color: #9ca3af;
    font-size: 12px;
    position: relative;
    z-index: 2;
}
.btn-login {
    width: 100%;
    height: 42px;
    border: 1px solid #b7d7b2;
    border-radius: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    color: #3762da;
    font-weight: 600;
    font-size: 13px;
    background: #f5fbf3;
    transition: 0.3s;
}
.btn-login:hover {
    background: #e8f5e9;
    color: #1b5e20;
}
@media(max-width: 900px){
    .register-box {
        width: 95%;
        height: auto;
        flex-direction: column;
    }
    .left-side {
        width: 100%;
        height: 180px;
        padding: 20px;
    }
    .middle-line {
        display: none;
    }
    .right-side {
        width: 100%;
        padding: 30px 0;
    }
}
</style>
</head>
<body>

<div class="register-box">
    <!-- KIRI -->
    <div class="left-side">
        <img src="public/assets/img/logo.png">
        <h2>Sistem Pendukung Keputusan Penerima Bantuan PIP</h2>
        <h3>Pondok Pesantren Haji Maqbul Hasibuan<br>Sibuhuan, Padang Lawas</h3>
    </div>

    <!-- GARIS -->
    <div class="middle-line"></div>

    <!-- KANAN -->
    <div class="right-side">
        <div class="form-box">
            <h2 class="title">Daftar Akun</h2>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger p-2 fs-7 text-center" style="font-size: 12px; border-radius: 8px;">
                    <?php echo $pesan_error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success p-2 fs-7 text-center" style="font-size: 12px; border-radius: 8px;">
                    <?php echo $pesan_sukses; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="konfirmasi" class="form-control" placeholder="Konfirmasi Password" required>
                </div>
                <button type="submit" class="btn-register">DAFTAR</button>
            </form>

            <!-- LOGIN -->
            <div class="login-link">
                <div class="line-text">
                    <span>Sudah punya akun?</span>
                </div>
                <a href="login.php" class="btn-login">🔑 Login Sekarang</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
