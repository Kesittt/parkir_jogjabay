<?php
session_start();
include 'koneksi.php';

// Cek hak akses: Hanya Admin yang boleh akses
if (!isset($_SESSION['login']) || (strtolower($_SESSION['role']) != 'admin' && strtolower($_SESSION['role']) != 'administrator')) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];

// Ambil filter bulan dan tahun dari input user, default ke bulan dan tahun saat ini
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query untuk mengambil ringkasan total pendapatan dan jumlah transaksi dalam 1 bulan
$q_ringkasan = "SELECT COUNT(*) as total_transaksi, SUM(biaya_total) as total_pendapatan 
                FROM tb_transaksi 
                WHERE status = 'keluar' 
                AND MONTH(waktu_keluar) = '$bulan_pilih' 
                AND YEAR(waktu_keluar) = '$tahun_pilih'";
$res_ringkasan = mysqli_query($koneksi, $q_ringkasan);
$data_ringkasan = mysqli_fetch_assoc($res_ringkasan);

$total_trx = $data_ringkasan['total_transaksi'] ?? 0;
$total_pendapatan = $data_ringkasan['total_pendapatan'] ?? 0;

// Query untuk mengambil detail riwayat transaksi keluar selama bulan tersebut
$q_detail = "SELECT t.*, k.plat_nomor, tr.jenis_kendaraan, a.nama_area 
             FROM tb_transaksi t 
             LEFT JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
             LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
             LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area 
             WHERE t.status = 'keluar' 
             AND MONTH(t.waktu_keluar) = '$bulan_pilih' 
             AND YEAR(t.waktu_keluar) = '$tahun_pilih' 
             ORDER BY t.waktu_keluar DESC";
$res_detail = mysqli_query($koneksi, $q_detail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Bulanan - Jogja Bay Luxury Parking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(rgba(10, 25, 47, 0.85), rgba(10, 25, 47, 0.95)), 
                        url('https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?q=80&w=2070&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .gold-gradient-text {
            background: linear-gradient(135deg, #FFE082 0%, #FFB300 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Pengaturan khusus saat mode cetak/print */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .glass-panel {
                background: white !important;
                border: 1px solid #cbd5e1 !important;
                backdrop-filter: none !important;
                box-shadow: none !important;
            }
            .text-slate-100, .text-slate-300, .text-slate-400 {
                color: #1e293b !important;
            }
            .gold-gradient-text {
                background: none !important;
                -webkit-text-fill-color: #0f172a !important;
            }
            table th, table td {
                color: #0f172a !important;
                border-color: #e2e8f0 !important;
            }
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col justify-between p-4 md:p-6">

    <!-- Header (Disembunyikan saat print dengan class no-print) -->
    <header class="w-full max-w-7xl mx-auto flex justify-between items-center glass-panel px-6 py-4 rounded-2xl mb-6 no-print">
        <div class="flex items-center space-x-3">
            <div class="p-2.5 bg-emerald-500/20 rounded-xl border border-emerald-500/30">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-400 text-xl"></i>
            </div>
            <div>
                <h1 class="font-extrabold tracking-wider text-sm gold-gradient-text">LAPORAN KEUANGAN BULANAN</h1>
                <p class="text-[10px] text-emerald-300 uppercase tracking-widest font-semibold">Jogja Bay Luxury Parking</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="admin_panel.php" class="bg-slate-800 hover:bg-slate-700 border border-white/10 text-slate-200 px-4 py-2 rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Panel Admin
            </a>
            <button onclick="window.print()" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 shadow-lg">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </button>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="w-full max-w-7xl mx-auto space-y-6 my-auto">
        
        <!-- Filter Form Bulan & Tahun (Disembunyikan saat print) -->
        <div class="glass-panel p-5 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 border border-emerald-500/30 no-print">
            <div class="flex items-center space-x-2 text-slate-300">
                <i class="fa-solid fa-filter text-emerald-400"></i>
                <span class="text-xs font-bold uppercase">Filter Periode Laporan:</span>
            </div>
            <form method="GET" action="" class="flex items-center gap-3 w-full sm:w-auto">
                <select name="bulan" class="bg-slate-900 border border-white/15 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-emerald-400">
                    <?php
                    $array_bulan = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    foreach ($array_bulan as $num => $nama_bln) {
                        $selected = ($num == $bulan_pilih) ? 'selected' : '';
                        echo "<option value='$num' $selected>$nama_bln</option>";
                    }
                    ?>
                </select>
                <select name="tahun" class="bg-slate-900 border border-white/15 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-emerald-400">
                    <?php
                    $tahun_sekarang = date('Y');
                    for ($t = $tahun_sekarang; $t >= $tahun_sekarang - 3; $t--) {
                        $selected = ($t == $tahun_pilih) ? 'selected' : '';
                        echo "<option value='$t' $selected>$t</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs transition">
                    Tampilkan
                </button>
            </form>
        </div>

        <!-- Judul Khusus Cetak (Hanya muncul saat dicetak) -->
        <div class="hidden print:block text-center mb-6">
            <h2 class="text-xl font-extrabold uppercase">Laporan Keuangan Bulanan - Jogja Bay Luxury Parking</h2>
            <p class="text-sm font-semibold">Periode: <?= $array_bulan[$bulan_pilih] . ' ' . $tahun_pilih; ?></p>
        </div>

        <!-- Kartu Ringkasan Pendapatan 1 Bulan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border border-white/10">
                <div>
                    <p class="text-[11px] text-slate-400 uppercase font-semibold">Total Kendaraan Keluar (Bulan Ini)</p>
                    <h3 class="text-2xl font-extrabold text-white mt-1"><?= number_format($total_trx, 0, ',', '.'); ?> <span class="text-xs font-normal text-slate-400">Transaksi</span></h3>
                </div>
                <div class="p-3 bg-cyan-500/20 rounded-xl text-cyan-400 border border-cyan-500/30 no-print"><i class="fa-solid fa-receipt text-lg"></i></div>
            </div>
            <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border border-emerald-500/40">
                <div>
                    <p class="text-[11px] text-slate-400 uppercase font-semibold">Total Pendapatan (Bulan Ini)</p>
                    <h3 class="text-2xl font-extrabold text-emerald-400 mt-1">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                </div>
                <div class="p-3 bg-emerald-500/20 rounded-xl text-emerald-400 border border-emerald-500/30 no-print"><i class="fa-solid fa-wallet text-lg"></i></div>
            </div>
        </div>

        <!-- Tabel Detail Transaksi Bulanan -->
        <div class="glass-panel p-6 rounded-2xl space-y-4 border border-white/10">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2 text-emerald-300">
                    <i class="fa-solid fa-list-check text-base no-print"></i>
                    <h2 class="text-xs font-bold uppercase tracking-wider">Rincian Pendapatan Periode <?= $array_bulan[$bulan_pilih] . ' ' . $tahun_pilih; ?></h2>
                </div>
                <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-1 rounded-lg font-semibold">Verified Log</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-white/10 text-slate-400">
                            <th class="py-2 px-3 font-semibold">ID</th>
                            <th class="py-2 px-3 font-semibold">PLAT NOMOR</th>
                            <th class="py-2 px-3 font-semibold">JENIS</th>
                            <th class="py-2 px-3 font-semibold">AREA</th>
                            <th class="py-2 px-3 font-semibold">WAKTU KELUAR</th>
                            <th class="py-2 px-3 font-semibold text-right">BIAYA (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (mysqli_num_rows($res_detail) > 0) : ?>
                            <?php while ($row = mysqli_fetch_assoc($res_detail)) : ?>
                                <tr>
                                    <td class="py-3 px-3 text-slate-400">#<?= $row['id_parkir']; ?></td>
                                    <td class="py-3 px-3 font-mono font-bold text-amber-300"><?= htmlspecialchars($row['plat_nomor'] ?? '-'); ?></td>
                                    <td class="py-3 px-3 text-slate-300 uppercase"><?= htmlspecialchars($row['jenis_kendaraan'] ?? 'Kendaraan'); ?></td>
                                    <td class="py-3 px-3 text-slate-300"><?= htmlspecialchars($row['nama_area'] ?? 'Area Utama'); ?></td>
                                    <td class="py-3 px-3 text-slate-300"><?= $row['waktu_keluar']; ?></td>
                                    <td class="py-3 px-3 text-right text-emerald-400 font-semibold">Rp <?= number_format($row['biaya_total'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 italic">Tidak ada data transaksi pendapatan pada periode bulan ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="w-full text-center text-[11px] text-slate-500 py-4 mt-8 border-t border-white/10 no-print">
        © 2026 Jogja Bay Waterpark. All Rights Reserved.
    </footer>

</body>
</html>