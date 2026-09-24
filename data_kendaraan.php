<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: landing.php");
    exit;
}

$role = strtolower($_SESSION['role'] ?? '');
if ($role != 'admin' && $role != 'administrator' && $role != 'petugas') {
    header("Location: index.php");
    exit;
}

$result = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan ORDER BY id_kendaraan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kendaraan - Jogja Bay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0b1329; }
        .glass-panel { background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.12); }
        .gold-gradient-text { background: linear-gradient(135deg, #FFE082 0%, #FFB300 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col md:flex-row">
    <aside class="w-full md:w-64 glass-panel border-r border-white/10 p-4 md:p-6 flex flex-col justify-between shrink-0">
        <div class="space-y-6">
            <div class="flex items-center space-x-3 pb-6 border-b border-white/10">
                <div class="p-2.5 bg-amber-500/20 rounded-xl border border-amber-500/30">
                    <i class="fa-solid fa-water-ladder text-amber-400 text-xl"></i>
                </div>
                <div>
                    <h1 class="font-extrabold tracking-wider text-xs gold-gradient-text">JOGJA BAY</h1>
                    <p class="text-[9px] text-cyan-300 uppercase tracking-widest font-semibold">Luxury Parking</p>
                </div>
            </div>
            <nav class="flex md:flex-col gap-2 text-xs">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 transition"><i class="fa-solid fa-gauge w-5 text-center"></i> Dashboard Utama</a>
                <a href="booking.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 transition"><i class="fa-solid fa-calendar-check w-5 text-center"></i> Link Booking</a>
                <a href="laporan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 transition"><i class="fa-solid fa-file-lines w-5 text-center"></i> Laporan Parkir</a>
                <a href="data_kendaraan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold transition"><i class="fa-solid fa-car-side w-5 text-center"></i> Data Kendaraan</a>
            </nav>
        </div>
        <div class="pt-6 border-t border-white/10">
            <a href="logout.php" class="w-full bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-300 px-4 py-2.5 rounded-xl text-xs transition flex items-center justify-center gap-2 font-semibold">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>
    <main class="flex-1 p-4 md:p-8 space-y-6 overflow-y-auto">
        <div class="glass-panel p-6 rounded-2xl border border-white/10 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400"><i class="fa-solid fa-car-side mr-2"></i> Database Kendaraan Terdaftar</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-white/10 text-slate-400">
                            <th class="py-3 px-3">ID</th>
                            <th class="py-3 px-3">PLAT NOMOR</th>
                            <th class="py-3 px-3">WARNA</th>
                            <th class="py-3 px-3">PEMILIK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="py-3 px-3 text-slate-400">#<?= $row['id_kendaraan']; ?></td>
                                    <td class="py-3 px-3 font-mono font-bold text-amber-300"><?= htmlspecialchars($row['plat_nomor']); ?></td>
                                    <td class="py-3 px-3 text-slate-300"><?= htmlspecialchars($row['warna'] ?? '-'); ?></td>
                                    <td class="py-3 px-3 text-slate-300"><?= htmlspecialchars($row['pemilik'] ?? '-'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="py-6 text-center text-slate-400 italic">Belum ada data kendaraan tercatat.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>