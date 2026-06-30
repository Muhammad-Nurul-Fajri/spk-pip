<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login SPK PIP</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
    height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    background: linear-gradient(
        135deg,
        #f5f9f2,
        #edf5e7
    );
}

/* =========================
   BOX LOGIN
========================= */

.login-box{
    width: 900px;
    height: 500px;

    background: white;

    border-radius: 28px;

    overflow: hidden;

    display: flex;

    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}

/* =========================
   BAGIAN KIRI
========================= */

.left-side{
    width: 50%;

    background: linear-gradient(
        135deg,
        #4caf50,
        #C6D166
    );

    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;

    padding: 35px;

    color: white;

    position: relative;

    text-align: center;
}

/* EFEK */

.left-side::before{
    content: '';

    position: absolute;

    width: 230px;
    height: 230px;

    background: rgba(255,255,255,0.08);

    border-radius: 50%;

    top: -70px;
    left: -70px;
}

.left-side::after{
    content: '';

    position: absolute;

    width: 150px;
    height: 150px;

    background: rgba(255,255,255,0.08);

    border-radius: 50%;

    bottom: -40px;
    right: -40px;
}

/* LOGO */

.left-side img{
    width: 170px;

    margin-bottom: 20px;

    z-index: 2;
}

/* JUDUL */

.left-side h2{
    font-size: 26px;

    font-weight: bold;

    line-height: 1.5;

    z-index: 2;

    margin-bottom: 12px;
}

/* SUB JUDUL */

.left-side h3{
    font-size: 15px;

    font-weight: 500;

    line-height: 1.6;

    z-index: 2;

    color: #f8fafc;
}

/* =========================
   GARIS
========================= */

.middle-line{
    width: 1px;

    background: #e5e7eb;
}

/* =========================
   BAGIAN KANAN
========================= */

.right-side{
    width: 50%;

    display: flex;
    justify-content: center;
    align-items: center;

    background: white;
}

/* =========================
   FORM BOX
========================= */

.form-box{
    width: 78%;
}

/* LOGO */

.form-box img{
    width: 105px;

    display: block;

    margin: auto;

    margin-bottom: 10px;
}

/* TITLE */

.title{
    text-align: center;

    color: #8aa12d;

    font-size: 27px;

    font-weight: bold;

    margin-bottom: 24px;
}

/* =========================
   INPUT
========================= */

.form-control{
    height: 42px;

    border-radius: 10px;

    border: 1px solid #d6e4d3;

    background: #fafdf8;

    padding-left: 14px;

    font-size: 13px;
}

.form-control:focus{
    border-color: #C6D166;

    box-shadow: 0 0 0 0.15rem rgba(76,175,80,0.15);

    background: white;
}

/* =========================
   BUTTON LOGIN
========================= */

.btn-login{
    width: 100%;

    height: 42px;

    border: none;

    border-radius: 10px;

    background: linear-gradient(
        135deg,
        #4caf50,
        #C6D166
    );

    color: white;

    font-size: 13px;

    font-weight: bold;

    transition: 0.3s;
}

.btn-login:hover{
    transform: translateY(-2px);

    box-shadow: 0 8px 15px rgba(46,125,50,0.2);
}

/* =========================
   REGISTER
========================= */

.register{
    margin-top: 18px;
}

/* GARIS ATAU */

.line-text{
    text-align: center;

    position: relative;

    margin-bottom: 14px;
}

.line-text::before{
    content: '';

    position: absolute;

    width: 40%;
    height: 1px;

    background: #d1d5db;

    left: 0;
    top: 50%;
}

.line-text::after{
    content: '';

    position: absolute;

    width: 40%;
    height: 1px;

    background: #d1d5db;

    right: 0;
    top: 50%;
}

.line-text span{
    background: white;

    padding: 0 10px;

    color: #9ca3af;

    font-size: 12px;

    position: relative;

    z-index: 2;
}

/* BUTTON REGISTER */

.btn-register{
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

.btn-register:hover{
    background: #e8f5e9;

    color: #1b5e20;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width: 900px){

    .login-box{
        width: 95%;

        height: auto;

        flex-direction: column;
    }

    .left-side{
        width: 100%;

        height: 240px;
    }

    .middle-line{
        display: none;
    }

    .right-side{
        width: 100%;

        padding: 30px 0;
    }

}

</style>

</head>

<body>

<div class="login-box">

    <!-- KIRI -->

    <div class="left-side">

        <img src="public/assets/img/logo.png">

        <h2>
            Sistem Pendukung Keputusan
            Penerima Bantuan PIP
        </h2>

        <h3>
            Pondok Pesantren Haji Maqbul Hasibuan<br>
            Sibuhuan, Padang Lawas
        </h3>

    </div>

    <!-- GARIS -->

    <div class="middle-line"></div>

    <!-- KANAN -->

    <div class="right-side">

        <div class="form-box">

            <img src="public/assets/img/logo.png">

            <h2 class="title">
                Login
            </h2>

            <!-- FORM LOGIN -->

            <form action="app/controllers/AuthController.php"
            method="POST">

                <div class="mb-3">

                    <input type="text"
                    name="username"
                    class="form-control"
                    placeholder="Username"
                    required>

                </div>

                <div class="mb-3">

                    <input type="password"
                    name="password"
                    class="form-control"
                    placeholder="Password"
                    required>

                </div>

                <button type="submit"
                class="btn-login">

                    LOGIN

                </button>

            </form>

            <!-- REGISTER -->

            <div class="register">

                <div class="line-text">

                    <span>atau</span>

                </div>

                <a href="register.php"
                class="btn-register">

                    👤 Daftar

                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>