<?php

session_start();

require_once '../../config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM users
     WHERE username='$username'
     AND password='$password'"
);

if(!$query){
    die(mysqli_error($koneksi));
}

$cek = mysqli_num_rows($query);

if($cek > 0){

    $data = mysqli_fetch_assoc($query);

    $_SESSION['id_user']  = $data['id'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['level']    = $data['role'];

    if($data['role'] == 'admin'){

        header("Location: ../../views/admin/dashboard.php");

    }elseif($data['role'] == 'siswa'){

        header("Location: ../../views/siswa/dashboard.php");

    }elseif($data['role'] == 'ketua_yayasan'){

        header("Location: ../../views/ketua_yayasan/dashboard.php");

    }

}else{

    echo "
    <script>
        alert('Username atau Password Salah');
        window.location='../../login.php';
    </script>
    ";

}