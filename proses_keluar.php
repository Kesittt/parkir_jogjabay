<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: landing.php");
    exit;
}

$id_parkir = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_parkir <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data transaksi parkir dan tarif
$query = mysqli_query($koneksi, "
    SELECT t.*, k.plat_nomor, k.jenis_kendaraan, tr.tarif_per_jam 
    FROM tb_transaksi t 
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
    WHERE t.id_parkir = $id_parkir AND t.status = 'masuk'
");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data transaksi tidak ditemukan atau kendaraan sudah keluar!'); window.location='index.php';</script>";
    exit;
}

// Hitung durasi dan biaya secara otomatis
$waktu_masuk = new DateTime($data['waktu_masuk']);
$waktu_keluar = new DateTime(); // Waktu sekarang
$durasi = $waktu_masuk->diff($waktu_keluar);
$jam = $durasi->h + ($durasi->days * 24);
if ($jam == 0) {
    $jam = 1; // Minimal hitung 1 jam
}

$tarif_per_jam = isset($data['tarif_per_jam']) ? $data['tarif_per_jam'] : 3000; // Default tarif jika kosong
$biaya_total = $jam * $tarif_per_jam;

// Jika tombol konfirmasi pembayaran ditekan
if (isset($_POST['konfirmasi_bayar'])) {
    $metode = isset($_POST['metode_pembayaran']) ? $_POST['metode_pembayaran'] : 'Cash';
    $waktu_keluar_str = $waktu_keluar->format('Y-m-d H:i:s');

    // Update database: ubah status jadi 'keluar', simpan waktu keluar, biaya, durasi, dan metode pembayaran
    $update = mysqli_query($koneksi, "
        UPDATE tb_transaksi SET 
        waktu_keluar = '$waktu_keluar_str', 
        durasi_jam = $jam, 
        biaya_total = $biaya_total, 
        status = 'keluar', 
        metode_pembayaran = '$metode' 
        WHERE id_parkir = $id_parkir
    ");

    if ($update) {
        // Langsung arahkan ke nota_pembayaran.php dengan membawa ID dan metode pembayaran
        header("Location: nota_pembayaran.php?id=" . $id_parkir . "&metode=" . urlencode($metode));
        exit;
    } else {
        echo "<script>alert('Gagal memproses pembayaran ke database!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Jogja Bay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0b1329; }
        .glass-panel { background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.12); }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="glass-panel p-6 md:p-8 rounded-2xl max-w-lg w-full space-y-6 border border-amber-500/30 shadow-2xl">
        <div class="text-center space-y-2 border-b border-white/10 pb-4">
            <h1 class="text-base font-extrabold text-amber-400 uppercase tracking-wider">Konfirmasi Pembayaran Parkir</h1>
            <p class="text-xs text-slate-400">Jogja Bay Luxury Parking System</p>
        </div>

        <!-- Detail Tagihan -->
        <div class="space-y-3 text-xs bg-slate-900/60 p-4 rounded-xl border border-white/10">
            <div class="flex justify-between"><span class="text-slate-400">No. Transaksi:</span> <span class="font-mono text-white">#<?= $data['id_parkir']; ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Plat Nomor:</span> <span class="font-mono font-bold text-white uppercase"><?= htmlspecialchars($data['plat_nomor']); ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Jenis Kendaraan:</span> <span class="uppercase text-slate-300"><?= htmlspecialchars($data['jenis_kendaraan']); ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Waktu Masuk:</span> <span class="text-slate-300"><?= $data['waktu_masuk']; ?></span></div>
            <div class="flex justify-between"><span class="text-slate-400">Estimasi Durasi:</span> <span class="text-slate-300"><?= $jam; ?> Jam</span></div>
            <div class="flex justify-between pt-2 border-t border-white/10 text-sm font-bold">
                <span class="text-amber-300">Total Tagihan:</span> 
                <span class="text-emerald-400 text-base">Rp <?= number_format($biaya_total, 0, ',', '.'); ?></span>
            </div>
        </div>

        <!-- Form Pilih Metode Pembayaran -->
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Pilih Metode Pembayaran</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="metode_pembayaran" value="Cash" checked onclick="toggleQR(false)" class="peer hidden">
                        <div class="p-3 rounded-xl border border-white/15 bg-slate-900/60 text-center peer-checked:border-amber-400 peer-checked:bg-amber-500/10 transition">
                            <i class="fa-solid fa-money-bill-wave text-amber-400 text-lg mb-1"></i>
                            <p class="text-xs font-bold text-white">CASH (TUNAI)</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="metode_pembayaran" value="QRIS" onclick="toggleQR(true)" class="peer hidden">
                        <div class="p-3 rounded-xl border border-white/15 bg-slate-900/60 text-center peer-checked:border-cyan-400 peer-checked:bg-cyan-500/10 transition">
                            <i class="fa-solid fa-qrcode text-cyan-400 text-lg mb-1"></i>
                            <p class="text-xs font-bold text-white">QRIS (SCAN)</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Bagian Muncul Gambar QRIS jika dipilih -->
            <div id="qris-container" class="hidden text-center space-y-3 bg-white p-4 rounded-xl text-slate-950">
                <p class="text-xs font-bold">Scan QRIS "Kesitt Web" (NMID: ID1026525739276)</p>
                <img src="qris.jpg" alt="QRIS Kesitt Web" class="w-48 h-48 mx-auto object-contain border border-slate-300 rounded-lg">
                <p class="text-[10px] text-slate-500">Pastikan pembayaran sukses sebelum klik tombol selesai.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="index.php" class="w-1/2 bg-slate-800 hover:bg-slate-700 text-slate-300 py-3 rounded-xl text-xs font-semibold text-center transition">Batal</a>
                <button type="submit" name="konfirmasi_bayar" class="w-1/2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 rounded-xl text-xs transition shadow-lg">
                    <i class="fa-solid fa-check mr-1"></i> Selesaikan Bayar
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleQR(show) {
            const qrisDiv = document.getElementById('qris-container');
            if (show) {
                qrisDiv.classList.remove('hidden');
            } else {
                qrisDiv.classList.add('hidden');
            }
        }
    </script>
</body>
</html>