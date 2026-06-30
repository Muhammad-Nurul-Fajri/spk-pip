<?php

$koneksi = mysqli_connect(
    "localhost",
    "root",
    "",
    "spk_pip_wp"
);

if(!$koneksi){
    die("Koneksi gagal");
}