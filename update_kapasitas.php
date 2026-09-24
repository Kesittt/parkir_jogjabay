<?php
session_start();
include 'koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['login']) || (strtolower($_SESSION['role']) != 'admin' && strtolower($_SESSION['role']) != 'administrator')) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update_kapasitas'])) {
    $id_area     = mysqli_real_escape_string($koneksi, $_POST['id_area']);
    $kapasitas   = (int)$_POST['kapasitas'];

    // Update kapasitas di tabel tb_area_parkir
    $query = "UPDATE tb_area_parkir SET kapasitas = '$kapasitas' WHERE id_area = '$id_area'";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: admin_panel.php#kelola-area");
        exit;
    } else {
        echo "Gagal memperbarui kapasitas: " . mysqli_error($koneksi);
    }
} else {
    header("Location: admin_panel.php");
    exit;
}
?>