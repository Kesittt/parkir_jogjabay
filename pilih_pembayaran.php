<?php
session_start();
include 'koneksi.php';

$id_parkir = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data transaksi sementara untuk menampilkan informasi total yang harus dibayar
$query = mysqli_query($koneksi, "
    SELECT t.*, k.plat_nomor, k.jenis_kendaraan, tf.* 
    FROM tb_transaksi t 
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    JOIN tb_tarif tf ON t.id_tarif = tf.id_tarif 
    WHERE t.id_parkir = $id_parkir
");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data transaksi tidak ditemukan!");
}

// Hitung estimasi biaya sementara jika belum keluar
$waktu_masuk = strtotime($data['waktu_masuk']);
$waktu_sekarang = time();
$selisih_jam = ceil(($waktu_sekarang - $waktu_masuk) / 3600);
if ($selisih_jam < 1) $selisih_jam = 1;
$estimasi_biaya = $selisih_jam * $data['tarif_perjam'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Metode Pembayaran - Jogja Bay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl w-full max-w-md space-y-6">
        
        <div class="text-center border-b border-slate-700 pb-4">
            <h2 class="text-xl font-bold tracking-wider text-amber-400">PILIH METODE PEMBAYARAN</h2>
            <p class="text-xs text-slate-400 mt-1">Plat Nomor: <span class="font-mono text-white font-bold"><?= $data['plat_nomor']; ?></span></p>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-700 text-center">
            <span class="text-xs text-slate-400 block">Estimasi Total Tagihan</span>
            <div class="text-2xl font-extrabold text-amber-400 mt-1">Rp <?= number_format($estimasi_biaya, 0, ',', '.'); ?></div>
            <span class="text-[10px] text-slate-500 block mt-1">Durasi: ± <?= $selisih_jam; ?> Jam</span>
        </div>

        <!-- Form Pilih Metode Pembayaran -->
        <form action="proses_pembayaran_aksi.php" method="POST" class="space-y-4">
            <input type="hidden" name="id_parkir" value="<?= $id_parkir; ?>">
            
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Pilih Metode:</label>
            
            <div class="grid grid-cols-3 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="metode_pembayaran" value="Cash" required class="peer hidden">
                    <div class="border border-slate-700 peer-checked:border-amber-400 peer-checked:bg-amber-500/10 rounded-xl p-3 text-center transition hover:bg-slate-700/50">
                        <i class="fa-solid fa-money-bill-wave text-amber-400 text-lg mb-1"></i>
                        <span class="block text-xs font-bold text-white">Cash</span>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" name="metode_pembayaran" value="E-Wallet" required class="peer hidden">
                    <div class="border border-slate-700 peer-checked:border-amber-400 peer-checked:bg-amber-500/10 rounded-xl p-3 text-center transition hover:bg-slate-700/50">
                        <i class="fa-solid fa-wallet text-cyan-400 text-lg mb-1"></i>
                        <span class="block text-xs font-bold text-white">E-Wallet</span>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" name="metode_pembayaran" value="QRIS" required class="peer hidden">
                    <div class="border border-slate-700 peer-checked:border-amber-400 peer-checked:bg-amber-500/10 rounded-xl p-3 text-center transition hover:bg-slate-700/50">
                        <i class="fa-solid fa-qrcode text-emerald-400 text-lg mb-1"></i>
                        <span class="block text-xs font-bold text-white">QRIS</span>
                    </div>
                </label>
            </div>

            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-xl transition shadow-lg mt-4">
                Konfirmasi & Cetak Nota
            </button>
        </form>

        <div class="text-center">
            <a href="index.php" class="text-xs text-slate-400 hover:text-white">Batal / Kembali</a>
        </div>

    </div>

</body>
</html>