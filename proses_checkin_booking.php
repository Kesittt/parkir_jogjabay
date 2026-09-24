<?php
session_start();
include 'koneksi.php';

// Set zona waktu agar sinkron dengan WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');

$pesan_judul = "";
$pesan_teks = "";
$status_tipe = ""; // 'success' atau 'error'

if (isset($_POST['checkin'])) {
    $kode_booking = strtoupper(trim(mysqli_real_escape_string($koneksi, $_POST['kode_booking'])));
    $plat_nomor   = strtoupper(trim(mysqli_real_escape_string($koneksi, $_POST['plat_nomor'])));

    // 1. Cari data kendaraan berdasarkan plat nomor
    $q_kendaraan = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan WHERE REPLACE(UPPER(plat_nomor), ' ', '') = REPLACE('$plat_nomor', ' ', '')");
    
    if ($q_kendaraan && mysqli_num_rows($q_kendaraan) > 0) {
        $d_kendaraan  = mysqli_fetch_assoc($q_kendaraan);
        $id_kendaraan = $d_kendaraan['id_kendaraan'];

        // 2. Cek apakah transaksi booking cocok dan statusnya 'booking'
        $q_transaksi = mysqli_query($koneksi, "SELECT * FROM tb_transaksi 
                                               WHERE id_kendaraan = '$id_kendaraan' 
                                               AND UPPER(kode_booking) = '$kode_booking' 
                                               AND status = 'booking'");

        if ($q_transaksi && mysqli_num_rows($q_transaksi) > 0) {
            $d_transaksi  = mysqli_fetch_assoc($q_transaksi);
            $id_parkir    = $d_transaksi['id_parkir'];
            $id_area      = $d_transaksi['id_area'] ?? 1;
            
            // Ambil tanggal dan jam booking dari database
            $tanggal_booking = $d_transaksi['tanggal_booking'];
            $jam_booking     = $d_transaksi['jam_booking'];
            
            if (!empty($tanggal_booking) && !empty($jam_booking)) {
                $waktu_rencana_str = $tanggal_booking . ' ' . $jam_booking;
                $timestamp_rencana = strtotime($waktu_rencana_str);
                $batas_toleransi   = $timestamp_rencana + 3600; // Toleransi 1 jam
                $waktu_sekarang    = time();

                // 3. Validasi Batas Toleransi 1 Jam
                if ($waktu_sekarang > $batas_toleransi) {
                    mysqli_query($koneksi, "UPDATE tb_transaksi SET status = 'hangus' WHERE id_parkir = '$id_parkir'");
                    
                    $pesan_judul = "Reservasi Hangus";
                    $pesan_teks  = "Maaf, waktu reservasi telah hangus! Batas toleransi keterlambatan adalah 1 jam dari jadwal booking ($jam_booking).";
                    $status_tipe = "error";
                    goto tampilkan_halaman;
                }
            }

            // 4. Jika sukses check-in
            $waktu_masuk_str = date('Y-m-d H:i:s');
            $u_transaksi = "UPDATE tb_transaksi 
                            SET status = 'masuk', waktu_masuk = '$waktu_masuk_str' 
                            WHERE id_parkir = '$id_parkir'";

            if (mysqli_query($koneksi, $u_transaksi)) {
                mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = '$id_area'");
                
                $pesan_judul = "Check-In Berhasil!";
                $pesan_teks  = "Kendaraan dengan plat nomor <b>{$plat_nomor}</b> berhasil tercatat masuk pada " . date('H:i') . " WIB.";
                $status_tipe = "success";
            } else {
                $pesan_judul = "Gagal Sistem";
                $pesan_teks  = "Gagal mengupdate transaksi: " . mysqli_error($koneksi);
                $status_tipe = "error";
            }
        } else {
            $q_aktif = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE id_kendaraan = '$id_kendaraan' AND status = 'masuk'");
            if ($q_aktif && mysqli_num_rows($q_aktif) > 0) {
                $pesan_judul = "Kendaraan Sudah di Dalam";
                $pesan_teks  = "Kendaraan dengan Plat <b>{$plat_nomor}</b> sudah berada di dalam area parkir!";
                $status_tipe = "error";
            } else {
                $pesan_judul = "Data Tidak Ditemukan";
                $pesan_teks  = "Kode Booking atau Plat Nomor tidak ditemukan, tidak cocok, atau sudah diproses!";
                $status_tipe = "error";
            }
        }
    } else {
        $pesan_judul = "Plat Tidak Terdaftar";
        $pesan_teks  = "Plat nomor tidak terdaftar dalam sistem!";
        $status_tipe = "error";
    }
} else {
    header("Location: index.php");
    exit();
}

tampilkan_halaman:
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Check-In Parkir</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full mx-4 text-center border border-slate-200">
        <!-- Icon Status -->
        <?php if ($status_tipe === 'success'): ?>
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                ✓
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2"><?php echo $pesan_judul; ?></h2>
        <?php else: ?>
            <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                ✕
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2"><?php echo $pesan_judul; ?></h2>
        <?php endif; ?>

        <!-- Pesan Keterangan -->
        <p class="text-slate-600 mb-6 leading-relaxed">
            <?php echo $pesan_teks; ?>
        </p>

        <!-- Tombol Kembali -->
        <a href="index.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition duration-200 shadow-md">
            Kembali ke Beranda
        </a>
    </div>

</body>
</html>