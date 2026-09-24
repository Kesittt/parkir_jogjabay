<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_parkir = intval($_POST['id_parkir']);
    $metode = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);

    // Ambil data waktu masuk & tarif
    $query_cek = mysqli_query($koneksi, "
        SELECT t.waktu_masuk, tf.tarif_perjam 
        FROM tb_transaksi t 
        JOIN tb_tarif tf ON t.id_tarif = tf.id_tarif 
        WHERE t.id_parkir = $id_parkir
    ");
    $data = mysqli_fetch_assoc($query_cek);

    if ($data) {
        $waktu_masuk = strtotime($data['waktu_masuk']);
        $waktu_keluar = time();
        $selisih_detik = $waktu_keluar - $waktu_masuk;
        $durasi_jam = ceil($selisih_detik / 3600);
        if ($durasi_jam < 1) $durasi_jam = 1;

        $biaya_total = $durasi_jam * $data['tarif_perjam'];
        $waktu_keluar_sql = date('Y-m-d H:i:s', $waktu_keluar);

        // Update database (status keluar + simpan metode pembayaran jika ada kolomnya, atau langsung ke nota)
        $update = mysqli_query($koneksi, "
            UPDATE tb_transaksi 
            SET waktu_keluar = '$waktu_keluar_sql', 
                durasi_jam = $durasi_jam, 
                biaya_total = $biaya_total, 
                status = 'keluar' 
            WHERE id_parkir = $id_parkir
        ");

        if ($update) {
            // Lempar ke nota dengan membawa informasi metode pembayaran
            header("Location: nota_pembayaran.php?id=" . $id_parkir . "&metode=" . urlencode($metode));
            exit();
        } else {
            die("Gagal memproses pembayaran: " . mysqli_error($koneksi));
        }
    } else {
        die("Data transaksi tidak ditemukan.");
    }
}
?>