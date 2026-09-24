<?php
include 'koneksi.php';

$id_parkir = isset($_GET['id']) ? intval($_GET['id']) : 0;
$metode_pembayaran = isset($_GET['metode']) ? htmlspecialchars($_GET['metode']) : 'Cash';

$query = mysqli_query($koneksi, "
    SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik, a.nama_area 
    FROM tb_transaksi t 
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area 
    WHERE t.id_parkir = $id_parkir
");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data transaksi tidak ditemukan!");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pembayaran Parkir - Jogja Bay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; color: black !important; }
            .no-print { display: none !important; }
            .nota-box { border: none !important; box-shadow: none !important; width: 100% !important; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4">

    <div class="nota-box bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl w-full max-w-md space-y-4">
        
        <div class="text-center border-b border-slate-700 pb-4">
            <h2 class="text-xl font-bold tracking-wider text-amber-400">JOGJA BAY WATERPARK</h2>
            <p class="text-xs text-slate-400">STRUK BUKTI PEMBAYARAN PARKIR</p>
        </div>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-400">No. Transaksi / ID:</span>
                <span class="font-mono font-bold">#<?= $data['id_parkir']; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Kode Booking:</span>
                <span class="font-mono text-amber-300"><?= $data['kode_booking'] ?? '-'; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Plat Nomor:</span>
                <span class="font-mono font-bold text-white"><?= htmlspecialchars($data['plat_nomor']); ?> (<?= htmlspecialchars($data['jenis_kendaraan']); ?>)</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Pemilik:</span>
                <span><?= htmlspecialchars($data['pemilik'] ?? '-'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Metode Bayar:</span>
                <span class="font-semibold text-cyan-300"><?= $metode_pembayaran; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Waktu Masuk:</span>
                <span><?= $data['waktu_masuk']; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Waktu Keluar:</span>
                <span><?= $data['waktu_keluar']; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Durasi Parkir:</span>
                <span><?= $data['durasi_jam']; ?> Jam</span>
            </div>
        </div>

        <div class="border-t border-slate-700 pt-3 flex justify-between items-center">
            <span class="font-bold text-base">TOTAL BAYAR:</span>
            <span class="text-xl font-extrabold text-amber-400">Rp <?= number_format($data['biaya_total'], 0, ',', '.'); ?></span>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-4 space-y-2 no-print">
            <button onclick="window.print()" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-xl transition shadow-lg">
                Cetak Struk Pembayaran
            </button>
            <a href="index.php" class="block text-center text-xs text-slate-400 hover:text-white py-2">
                Kembali ke Beranda / Dashboard
            </a>
        </div>

    </div>

</body>
</html>