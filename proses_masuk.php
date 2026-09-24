<?php
session_start();
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $plat_nomor  = strtoupper(trim(mysqli_real_escape_string($koneksi, $_POST['plat_nomor'])));
    $id_tarif    = mysqli_real_escape_string($koneksi, $_POST['id_tarif']);
    $warna       = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $id_area     = mysqli_real_escape_string($koneksi, $_POST['id_area']);
    $pemilik     = mysqli_real_escape_string($koneksi, $_POST['pemilik']);

    // 1. Tangani id_user dari Session secara aman untuk Foreign Key
    if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
        $id_user_val = $_SESSION['id_user'];
        // Cek apakah id_user ini benar-benar ada di tb_user
        $q_user = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE id_user = '$id_user_val'");
        if ($q_user && mysqli_num_rows($q_user) > 0) {
            $id_user_sql = "'" . mysqli_real_escape_string($koneksi, $id_user_val) . "'";
        } else {
            $id_user_sql = "NULL";
        }
    } else {
        $id_user_sql = "NULL";
    }

    // 2. Cek apakah kendaraan dengan plat nomor ini sudah ada di tb_kendaraan
    $q_cek_k = mysqli_query($koneksi, "SELECT id_kendaraan FROM tb_kendaraan WHERE REPLACE(UPPER(plat_nomor), ' ', '') = REPLACE('$plat_nomor', ' ', '')");
    
    if ($q_cek_k && mysqli_num_rows($q_cek_k) > 0) {
        $d_k = mysqli_fetch_assoc($q_cek_k);
        $id_kendaraan = $d_k['id_kendaraan'];
    } else {
        // Ambil jenis kendaraan dari tb_tarif
        $q_tarif = mysqli_query($koneksi, "SELECT jenis_kendaraan FROM tb_tarif WHERE id_tarif = '$id_tarif'");
        $d_tarif = mysqli_fetch_assoc($q_tarif);
        $jenis   = $d_tarif['jenis_kendaraan'] ?? 'Motor';

        // Insert kendaraan baru dengan id_user berupa NULL jika tidak ditemukan
        $query_ins_k = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik, id_user) 
                        VALUES ('$plat_nomor', '$jenis', '$warna', '$pemilik', $id_user_sql)";
        
        if (mysqli_query($koneksi, $query_ins_k)) {
            $id_kendaraan = mysqli_insert_id($koneksi);
        } else {
            die("Gagal menambah kendaraan: " . mysqli_error($koneksi));
        }
    }

    // 3. Cek apakah kendaraan sedang aktif parkir
    $q_cek_p = mysqli_query($koneksi, "SELECT id_parkir FROM tb_transaksi WHERE id_kendaraan = '$id_kendaraan' AND status = 'masuk'");
    if ($q_cek_p && mysqli_num_rows($q_cek_p) > 0) {
        echo "<script>
                alert('Kendaraan dengan plat {$plat_nomor} masih tercatat di dalam area parkir!');
                window.location.href = 'index.php';
              </script>";
        exit();
    }

    // 4. Catat transaksi masuk
    $waktu_masuk = date('Y-m-d H:i:s');
    $query_trans = "INSERT INTO tb_transaksi (id_kendaraan, id_tarif, id_area, waktu_masuk, status, id_user) 
                    VALUES ('$id_kendaraan', '$id_tarif', '$id_area', '$waktu_masuk', 'masuk', $id_user_sql)";

    if (mysqli_query($koneksi, $query_trans)) {
        // Tambah jumlah terisi di tb_area_parkir
        mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = '$id_area'");

        echo "<script>
                alert('Tiket Berhasil Dicetak! Kendaraan {$plat_nomor} tercatat masuk.');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "Gagal mencatat transaksi: " . mysqli_error($koneksi);
    }

} else {
    header("Location: index.php");
    exit();
}
?>