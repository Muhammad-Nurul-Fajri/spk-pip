<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard Admin</title>

<!-- BOOTSTRAP -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FONT AWESOME -->

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

/* =========================
   RESET
========================= */

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

/* =========================
   BODY
========================= */

body{
    background: #f4f7f1;
    overflow-x: hidden;
    overflow-y: scroll;
}

/* HILANGKAN SCROLLBAR HALAMAN */

body::-webkit-scrollbar{
    width: 0px;
}

html{
    scrollbar-width: none;
}

/* =========================
   SIDEBAR
========================= */

.sidebar{

    width: 270px;

    height: 100vh;

    position: fixed;

    left: 0;
    top: 0;

    background: linear-gradient(
        180deg,
        #4caf50,
        #c6d166
    );

    color: white;

    overflow-y: auto;
    overflow-x: hidden;
}

/* HILANGKAN SCROLLBAR SIDEBAR */

.sidebar::-webkit-scrollbar{
    width: 0px;
}

/* =========================
   LOGO
========================= */

.logo{

    padding: 25px 20px;

    text-align: center;

    border-bottom: 1px solid rgba(255,255,255,0.15);
}

.logo img{

    width: 95px;
    height: 95px;

    object-fit: contain;

    margin-bottom: 12px;
}

.logo h4{

    font-size: 15px;

    font-weight: bold;

    line-height: 1.6;

    margin-bottom: 8px;
}

.logo p{

    font-size: 12px;

    margin: 0;

    opacity: 0.95;
}

/* =========================
   MENU
========================= */

.menu{

    padding: 18px 0;
    margin: 0;
}

.menu li{

    list-style: none;
}

.menu li a{

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px 24px;

    color: white;

    text-decoration: none;

    font-size: 14px;

    transition: 0.3s;
}

.menu li a:hover{

    background: rgba(255,255,255,0.15);
}

.menu li a i{

    width: 22px;

    text-align: center;
}

/* =========================
   CONTENT
========================= */

.content{

    margin-left: 270px;

    padding: 22px;

    min-height: 100vh;
}

/* =========================
   NAVBAR
========================= */

.navbar-custom{

    background: white;

    padding: 16px 22px;

    border-radius: 18px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow: 0 4px 12px rgba(0,0,0,0.05);

    margin-bottom: 25px;
}

/* =========================
   DASHBOARD TITLE
========================= */

.dashboard-title{

    display: flex;

    align-items: center;

    gap: 12px;
}

.dashboard-title i{

    font-size: 22px;

    color: #4caf50;
}

.dashboard-title h4{

    margin: 0;

    font-size: 23px;

    font-weight: bold;

    color: #333;
}

/* =========================
   USER
========================= */

.user-box{

    display: flex;

    align-items: center;

    gap: 12px;
}

.user-icon{

    width: 42px;
    height: 42px;

    border-radius: 50%;

    background: #4caf50;

    display: flex;

    justify-content: center;

    align-items: center;

    color: white;

    font-size: 18px;
}

.user-name{

    font-size: 14px;

    font-weight: bold;

    color: #333;
}

/* =========================
   CARD MENU
========================= */

.menu-card{

    background: white;

    border-radius: 16px;

    padding: 22px;

    position: relative;

    overflow: hidden;

    box-shadow: 0 3px 10px rgba(0,0,0,0.05);

    transition: 0.3s;

    height: 100%;

    cursor: pointer;
}

.menu-card:hover{

    transform: translateY(-5px);
}

/* GARIS WARNA */

.menu-card::before{

    content: '';

    position: absolute;

    left: 0;
    top: 0;

    width: 5px;
    height: 100%;
}

/* WARNA */

.border-green::before{
    background: #4caf50;
}

.border-blue::before{
    background: #2196f3;
}

.border-orange::before{
    background: #ff9800;
}

.border-red::before{
    background: #e91e63;
}

/* ICON */

.menu-card i{

    position: absolute;

    right: 18px;
    top: 18px;

    font-size: 30px;

    color: #e5e7eb;
}

/* TEXT */

.menu-card h5{

    font-size: 16px;

    font-weight: bold;

    margin-bottom: 8px;

    color: #333;
}

.menu-card p{

    font-size: 13px;

    color: #777;

    margin: 0;
}

/* =========================
   WELCOME BOX
========================= */

.welcome-box{

    margin-top: 28px;

    background: linear-gradient(
        135deg,
        #4caf50,
        #c6d166
    );

    border-radius: 22px;

    padding: 45px;

    color: white;

    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

/* JUDUL */

.welcome-box h1{

    text-align: center;

    font-size: 38px;

    font-weight: bold;

    margin-bottom: 20px;
}

/* DESKRIPSI */

.welcome-box .description{

    text-align: justify;

    font-size: 15px;

    line-height: 1.9;

    margin-bottom: 30px;
}

/* SEKOLAH */

.welcome-box .school{

    text-align: center;

    font-size: 13px;

    margin: 0;

    opacity: 0.95;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width: 900px){

    .sidebar{

        width: 100%;

        height: auto;

        position: relative;
    }

    .content{

        margin-left: 0;
    }

}

</style>

</head>

<body>

<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <!-- LOGO -->

    <div class="logo">

        <img src="../../public/assets/img/logo.png">

        <h4>
            Sistem Pendukung Keputusan
            Seleksi Penerima Bantuan PIP
        </h4>

        <p>
            Pondok Pesantren Haji Maqbul Hasibuan
        </p>

    </div>

    <!-- MENU -->

    <ul class="menu">

        <li>
            <a href="dashboard.php" class="active">
                <i class="fa fa-house"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="kriteria/index.php">
                <i class="fa fa-list"></i>
                Data Kriteria
            </a>
        </li>

        <li>
            <a href="sub_kriteria/index_subkriteria.php"> 
            <i class="fa fa-layer-group"></i>
            Data Sub Kriteria
        </a>
        </li>

        <li>
            <a href="alternatif/index.php">
                <i class="fa fa-user-graduate"></i>
                Data Alternatif
            </a>
        </li>

        <li>
            <a href="penilaian/index.php">
                <i class="fa fa-check-circle"></i>
                Data Penilaian
            </a>
        </li>

        <li>
            <a href="perhitungan/index.php">
                <i class="fa fa-calculator"></i>
                Data Perhitungan
            </a>
        </li>

        <li>
            <a href="hasil/index.php">
                <i class="fa fa-trophy"></i>
                Data Hasil Akhir
            </a>
        </li>

        <li>
            <a href="user/index.php">
                <i class="fa fa-users"></i>
                Data User
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fa fa-window-maximize"></i>
                Kelola Halaman
            </a>
        </li>

        <li>
            <a href="../../logout.php">
                <i class="fa fa-right-from-bracket"></i>
                Logout
            </a>
        </li>

    </ul>

</div>

<!-- =========================
     CONTENT
========================= -->

<div class="content">

    <!-- NAVBAR -->

    <div class="navbar-custom">

        <div class="dashboard-title">

            <i class="fa fa-house"></i>

            <h4>
                Dashboard
            </h4>

        </div>

        <div class="user-box">

            <div class="user-icon">

                <i class="fa fa-user"></i>

            </div>

            <div class="user-name">

                Admin

            </div>

        </div>

    </div>

    <!-- CARD MENU -->

    <div class="row g-4">

        <div class="col-md-4">

            <div class="menu-card border-green">

                <i class="fa fa-list"></i>

                <h5>Data Kriteria</h5>

                <p>
                    Kelola data kriteria penilaian
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="menu-card border-blue">

                <i class="fa fa-layer-group"></i>

                <h5>Data Sub Kriteria</h5>

                <p>
                    Kelola data sub kriteria penilaian
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="menu-card border-orange">

                <i class="fa fa-user-graduate"></i>

                <h5>Data Alternatif</h5>

                <p>
                    Kelola data siswa penerima PIP
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="menu-card border-blue">

                <i class="fa fa-check-circle"></i>

                <h5>Data Penilaian</h5>

                <p>
                    Kelola data penilaian siswa
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="menu-card border-green">

                <i class="fa fa-calculator"></i>

                <h5>Data Perhitungan</h5>

                <p>
                    Perhitungan metode Weighted Product
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="menu-card border-orange">

                <i class="fa fa-trophy"></i>

                <h5>Data Hasil Akhir</h5>

                <p>
                    Hasil akhir penerima bantuan PIP
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="menu-card border-red">

                <i class="fa fa-users"></i>

                <h5>Data User</h5>

                <p>
                    Kelola akun pengguna sistem
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="menu-card border-green">

                <i class="fa fa-window-maximize"></i>

                <h5>Kelola Halaman</h5>

                <p>
                    Kelola tampilan halaman website
                </p>

            </div>

        </div>

    </div>

    <!-- WELCOME -->

    <div class="welcome-box">

        <h1>
            Selamat Datang
        </h1>

        <div class="description">

            Sistem Pendukung Keputusan Seleksi
            Penerima Bantuan Program Indonesia Pintar (PIP)
            menggunakan metode Weighted Product (WP)
            untuk membantu proses penyeleksian penerima
            bantuan agar lebih cepat, objektif, tepat sasaran,
            serta mempermudah pihak sekolah dalam menentukan
            siswa yang layak menerima bantuan berdasarkan
            kriteria yang telah ditetapkan.

        </div>

        <p class="school">

            Pondok Pesantren Haji Maqbul Hasibuan

        </p>

    </div>

</div>

</body>
</html>