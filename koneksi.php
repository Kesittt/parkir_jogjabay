<?php
$host = "localhost";
$user = "root";       // Default username XAMPP
$pass = "";           // Default password XAMPP biasanya kosong
$db   = "parkirgw";  // Sesuaikan dengan nama database baru di phpMyAdmin lokal

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>