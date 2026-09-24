<?php
session_start();
include 'koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['login']) || (strtolower($_SESSION['role']) != 'admin' && strtolower($_SESSION['role']) != 'administrator')) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_val = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Cek kolom primary key yang tersedia di tabel tb_user
    $kolom_pk = 'id'; // default
    $cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_user LIKE 'id_user'");
    if (mysqli_num_rows($cek_kolom) > 0) {
        $kolom_pk = 'id_user';
    } else {
        $cek_kolom2 = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_user LIKE 'id_pengguna'");
        if (mysqli_num_rows($cek_kolom2) > 0) {
            $kolom_pk = 'id_pengguna';
        }
    }

    // Eksekusi hapus menggunakan kolom primary key yang ditemukan
    $query = "DELETE FROM tb_user WHERE $kolom_pk = '$id_val'";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: admin_panel.php#kelola-user");
        exit;
    } else {
        echo "Gagal menghapus user: " . mysqli_error($koneksi);
    }
} else {
    header("Location: admin_panel.php");
    exit;
}
?>