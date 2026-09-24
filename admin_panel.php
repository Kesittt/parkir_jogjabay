<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login dan benar-benar role-nya Admin
if (!isset($_SESSION['login']) || (strtolower($_SESSION['role']) != 'admin' && strtolower($_SESSION['role']) != 'administrator')) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];

// Ambil data total pendapatan per bulan untuk grafik
$query_grafik = "SELECT MONTH(waktu_keluar) as bulan, SUM(biaya_total) as total_pendapatan 
                 FROM tb_transaksi 
                 WHERE status = 'keluar' AND YEAR(waktu_keluar) = YEAR(CURDATE())
                 GROUP BY MONTH(waktu_keluar)";
$result_grafik = mysqli_query($koneksi, $query_grafik);

$data_pendapatan = array_fill(1, 12, 0);
while ($row_g = mysqli_fetch_assoc($result_grafik)) {
    $data_pendapatan[(int)$row_g['bulan']] = (float)$row_g['total_pendapatan'];
}
$json_data_pendapatan = json_encode(array_values($data_pendapatan));

// Ambil data semua transaksi untuk laporan admin
$query_semua_transaksi = "SELECT t.*, k.plat_nomor, tr.jenis_kendaraan, a.nama_area 
                          FROM tb_transaksi t 
                          LEFT JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                          LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
                          LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area 
                          ORDER BY t.waktu_masuk DESC LIMIT 20";
$result_transaksi = mysqli_query($koneksi, $query_semua_transaksi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrator - Jogja Bay Luxury Parking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(rgba(10, 25, 47, 0.85), rgba(10, 25, 47, 0.95)), 
                        url('https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?q=80&w=2070&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            overflow-x: hidden;
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
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col md:flex-row">

    <!-- SIDEBAR KIRI ADMIN -->
    <aside class="w-full md:w-64 glass-panel border-b md:border-b-0 md:border-r border-white/10 p-4 md:p-6 flex flex-col justify-between shrink-0">
        <div class="space-y-6">
            <!-- Brand / Logo -->
            <div class="flex items-center space-x-3 pb-6 border-b border-white/10">
                <div class="p-2.5 bg-purple-500/20 rounded-xl border border-purple-500/30">
                    <i class="fa-solid fa-shield-halved text-purple-400 text-xl"></i>
                </div>
                <div>
                    <h1 class="font-extrabold tracking-wider text-xs gold-gradient-text">ADMIN PARKIR</h1>
                    <p class="text-[9px] text-purple-300 uppercase tracking-widest font-semibold">Panel Kontrol</p>
                </div>
            </div>

            <!-- Menu Navigasi Sidebar -->
            <nav class="flex md:flex-col gap-2 text-xs overflow-x-auto pb-2 md:pb-0">
                <a href="admin_panel.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-purple-500/20 text-purple-300 border border-purple-500/30 font-bold transition whitespace-nowrap">
                    <i class="fa-solid fa-gauge w-5 text-center"></i> Dashboard
                </a>
                <a href="#kelola-area" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                    <i class="fa-solid fa-square-parking w-5 text-center"></i> Kelola Kapasitas
                </a>
                <a href="#kelola-tarif" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                    <i class="fa-solid fa-tags w-5 text-center"></i> Kelola Tarif
                </a>
                <a href="#kelola-user" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                    <i class="fa-solid fa-users w-5 text-center"></i> Kelola User
                </a>
                <a href="laporan_keuangan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i> Laporan Keuangan
                </a>
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                    <i class="fa-solid fa-arrow-left w-5 text-center"></i> Lihat Beranda
                </a>
            </nav>
        </div>

        <!-- Profil & Tombol Keluar -->
        <div class="pt-6 border-t border-white/10 space-y-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-purple-300 font-bold text-xs shrink-0">
                    <?= strtoupper(substr($username, 0, 2)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($username); ?></p>
                    <p class="text-[10px] text-purple-400 uppercase">Administrator</p>
                </div>
            </div>
            <a href="logout.php" class="w-full bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-300 px-4 py-2.5 rounded-xl text-xs transition flex items-center justify-center gap-2 font-semibold">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA SEBELAH KANAN -->
    <main class="flex-1 p-4 md:p-8 space-y-6 overflow-y-auto flex flex-col justify-between">
        <div class="space-y-6">
            
            <!-- Header Atas Judul -->
            <div class="glass-panel p-5 rounded-2xl flex justify-between items-center border border-purple-500/30">
                <div>
                    <h2 class="text-base font-extrabold text-white">Panel Kontrol Administrator</h2>
                    <p class="text-xs text-slate-400">Kelola sistem, laporan keuangan, dan data pengguna secara terpusat.</p>
                </div>
                <span class="bg-purple-500/20 text-purple-300 border border-purple-500/30 px-3 py-1 rounded-xl text-xs font-bold">
                    <i class="fa-solid fa-shield-halved mr-1"></i> Admin Mode
                </span>
            </div>

            <!-- GRAFIK KEUANGAN -->
            <div class="glass-panel p-6 rounded-2xl space-y-4 border border-purple-500/30">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2 text-purple-300">
                        <i class="fa-solid fa-chart-line text-base"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider">Grafik Laporan Keuangan Bulanan (<?= date('Y'); ?>)</h3>
                    </div>
                    <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-1 rounded-lg font-semibold">Realtime Analytics</span>
                </div>
                <div class="relative w-full h-72">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>

            <!-- TABEL KELOLA AREA & KAPASITAS PARKIR -->
            <div id="kelola-area" class="glass-panel p-6 rounded-2xl space-y-4 border border-purple-500/30">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2 text-purple-300">
                        <i class="fa-solid fa-square-parking text-base"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider">Kelola Area & Kapasitas Parkir</h3>
                    </div>
                    <span class="text-[10px] bg-purple-500/20 text-purple-300 border border-purple-500/30 px-2.5 py-1 rounded-lg font-semibold">Total Kapasitas Otomatis</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-white/10 text-slate-400">
                                <th class="py-2 px-3 font-semibold">ID AREA</th>
                                <th class="py-2 px-3 font-semibold">NAMA AREA PARKIR</th>
                                <th class="py-2 px-3 font-semibold">KAPASITAS (SLOT)</th>
                                <th class="py-2 px-3 font-semibold">TERISI</th>
                                <th class="py-2 px-3 font-semibold text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php
                            $query_area = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir ORDER BY id_area ASC");
                            if ($query_area && mysqli_num_rows($query_area) > 0) {
                                while ($ar = mysqli_fetch_assoc($query_area)) {
                            ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <form action="update_kapasitas.php" method="POST">
                                            <input type="hidden" name="id_area" value="<?= $ar['id_area']; ?>">
                                            <td class="py-3 px-3 text-slate-400">#<?= $ar['id_area']; ?></td>
                                            <td class="py-3 px-3 font-semibold text-white"><?= htmlspecialchars($ar['nama_area']); ?></td>
                                            <td class="py-3 px-3">
                                                <input type="number" name="kapasitas" value="<?= $ar['kapasitas']; ?>" class="bg-slate-900 border border-white/20 rounded px-2.5 py-1 text-white w-28 font-mono text-xs focus:outline-none focus:border-purple-400" required>
                                            </td>
                                            <td class="py-3 px-3 text-amber-300 font-semibold"><?= $ar['terisi']; ?> Unit</td>
                                            <td class="py-3 px-3 text-center">
                                                <button type="submit" name="update_kapasitas" class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-1.5 rounded-lg text-xs transition font-semibold shadow">
                                                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                            <?php 
                                }
                            } else { 
                            ?>
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400 italic">Belum ada data area parkir.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABEL KELOLA TARIF PARKIR -->
            <div id="kelola-tarif" class="glass-panel p-6 rounded-2xl space-y-4 border border-purple-500/30">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2 text-purple-300">
                        <i class="fa-solid fa-tags text-base"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider">Kelola Tarif Parkir</h3>
                    </div>
                    <span class="text-[10px] bg-purple-500/20 text-purple-300 border border-purple-500/30 px-2.5 py-1 rounded-lg font-semibold">Pengaturan Harga Jam</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-white/10 text-slate-400">
                                <th class="py-2 px-3 font-semibold">ID TARIF</th>
                                <th class="py-2 px-3 font-semibold">JENIS KENDARAAN</th>
                                <th class="py-2 px-3 font-semibold">TARIF PER JAM</th>
                                <th class="py-2 px-3 font-semibold text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php
                            $query_tarif = mysqli_query($koneksi, "SELECT * FROM tb_tarif ORDER BY id_tarif ASC");
                            if ($query_tarif && mysqli_num_rows($query_tarif) > 0) {
                                while ($t = mysqli_fetch_assoc($query_tarif)) {
                            ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-3 text-slate-400">#<?= $t['id_tarif']; ?></td>
                                        <td class="py-3 px-3 font-semibold text-white uppercase"><?= htmlspecialchars($t['jenis_kendaraan']); ?></td>
                                        <td class="py-3 px-3 text-amber-300 font-mono font-bold">Rp <?= number_format($t['tarif_per_jam'], 0, ',', '.'); ?></td>
                                        <td class="py-3 px-3 text-center">
                                            <a href="edit_tarif.php?id=<?= $t['id_tarif']; ?>" class="bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 px-3 py-1.5 rounded-lg text-xs transition inline-flex items-center gap-1 font-semibold">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit Tarif
                                            </a>
                                        </td>
                                    </tr>
                            <?php 
                                }
                            } else { 
                            ?>
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400 italic">Belum ada data tarif di database.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABEL KELOLA DATA PENGGUNA (USER) -->
            <div id="kelola-user" class="glass-panel p-6 rounded-2xl space-y-4 border border-purple-500/30">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2 text-purple-300">
                        <i class="fa-solid fa-users text-base"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider">Kelola Data Pengguna (User)</h3>
                    </div>
                    <a href="tambah_user.php" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 font-semibold shadow-md">
                        <i class="fa-solid fa-user-plus"></i> Tambah User Baru
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-white/10 text-slate-400">
                                <th class="py-2 px-3 font-semibold">ID</th>
                                <th class="py-2 px-3 font-semibold">NAMA LENGKAP</th>
                                <th class="py-2 px-3 font-semibold">KONTAK</th>
                                <th class="py-2 px-3 font-semibold">USERNAME</th>
                                <th class="py-2 px-3 font-semibold">PASSWORD</th>
                                <th class="py-2 px-3 font-semibold">ROLE</th>
                                <th class="py-2 px-3 font-semibold text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php
                            $query_user = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user ASC");
                            if ($query_user && mysqli_num_rows($query_user) > 0) {
                                while ($u = mysqli_fetch_assoc($query_user)) {
                                    $role = ucfirst(strtolower($u['role'] ?? 'Pengunjung'));
                                    if (empty($u['role'])) $role = 'Pengunjung';
                                    
                                    $badge_color = "bg-slate-500/20 text-slate-300 border-slate-500/30";
                                    if ($role == 'Admin') $badge_color = "bg-red-500/20 text-red-300 border-red-500/30 font-bold";
                                    elseif ($role == 'Owner') $badge_color = "bg-amber-500/20 text-amber-300 border-amber-500/30 font-bold";
                                    elseif ($role == 'Petugas') $badge_color = "bg-cyan-500/20 text-cyan-300 border-cyan-500/30 font-bold";
                                    elseif ($role == 'Member' || $role == 'Pengunjung') $badge_color = "bg-emerald-500/20 text-emerald-300 border-emerald-500/30 font-bold";
                            ?>
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-3 text-slate-400">#<?= $u['id_user']; ?></td>
                                        <td class="py-3 px-3 font-semibold text-white"><?= htmlspecialchars($u['nama_lengkap']); ?></td>
                                        <td class="py-3 px-3 text-slate-300"><?= htmlspecialchars($u['kontak'] ?? '-'); ?></td>
                                        <td class="py-3 px-3 text-amber-300 font-mono"><?= htmlspecialchars($u['username']); ?></td>
                                        <td class="py-3 px-3 text-slate-300 font-mono"><?= htmlspecialchars($u['password']); ?></td>
                                        <td class="py-3 px-3">
                                            <span class="border px-2.5 py-0.5 rounded text-[10px] uppercase tracking-wider <?= $badge_color; ?>">
                                                <?= $role; ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center space-x-1">
                                            <a href="edit_user.php?id=<?= $u['id_user']; ?>" class="bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 p-1.5 rounded-lg transition inline-block" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="hapus_user.php?id=<?= $u['id_user']; ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 p-1.5 rounded-lg transition inline-block" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                            <?php 
                                }
                            } else { 
                            ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400 italic">Belum ada data pengguna terdaftar.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABEL LOG TRANSAKSI -->
            <div class="glass-panel p-6 rounded-2xl space-y-4 border border-purple-500/30">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2 text-purple-300">
                        <i class="fa-solid fa-database text-base"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider">Log Seluruh Transaksi Parkir</h3>
                    </div>
                    <span class="text-[10px] bg-purple-500/20 text-purple-300 border border-purple-500/30 px-2.5 py-1 rounded-lg font-semibold">Full Access</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-white/10 text-slate-400">
                                <th class="py-2 px-3 font-semibold">ID</th>
                                <th class="py-2 px-3 font-semibold">PLAT NOMOR</th>
                                <th class="py-2 px-3 font-semibold">JENIS</th>
                                <th class="py-2 px-3 font-semibold">AREA</th>
                                <th class="py-2 px-3 font-semibold">MASUK</th>
                                <th class="py-2 px-3 font-semibold">KELUAR</th>
                                <th class="py-2 px-3 font-semibold">BIAYA TOTAL</th>
                                <th class="py-2 px-3 font-semibold text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if ($result_transaksi && mysqli_num_rows($result_transaksi) > 0) : ?>
                                <?php while ($row = mysqli_fetch_assoc($result_transaksi)) : ?>
                                    <tr>
                                        <td class="py-3 px-3 text-slate-400">#<?= $row['id_parkir']; ?></td>
                                        <td class="py-3 px-3 font-mono font-bold text-amber-300"><?= htmlspecialchars($row['plat_nomor'] ?? '-'); ?></td>
                                        <td class="py-3 px-3 text-slate-300 uppercase"><?= htmlspecialchars($row['jenis_kendaraan'] ?? 'Kendaraan'); ?></td>
                                        <td class="py-3 px-3 text-slate-300"><?= htmlspecialchars($row['nama_area'] ?? 'Area Utama'); ?></td>
                                        <td class="py-3 px-3 text-slate-300"><?= $row['waktu_masuk']; ?></td>
                                        <td class="py-3 px-3 text-slate-300"><?= $row['waktu_keluar'] ? $row['waktu_keluar'] : '-'; ?></td>
                                        <td class="py-3 px-3 text-emerald-400 font-semibold"><?= $row['biaya_total'] ? 'Rp ' . number_format($row['biaya_total'], 0, ',', '.') : '-'; ?></td>
                                        <td class="py-3 px-3 text-center">
                                            <?php if ($row['status'] == 'masuk') : ?>
                                                <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2.5 py-0.5 rounded text-[10px]">Aktif</span>
                                            <?php else : ?>
                                                <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-0.5 rounded text-[10px]">Keluar</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-slate-400 italic">Belum ada data transaksi tercatat.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <footer class="w-full text-center text-[11px] text-slate-500 py-4 mt-8 border-t border-white/10">
            © 2026 Jogja Bay Waterpark. All Rights Reserved.
        </footer>
    </main>

    <!-- Script Chart.js -->
    <script>
        const ctx = document.getElementById('financialChart').getContext('2d');
        const pendapatanData = <?= $json_data_pendapatan; ?>;

        const financialChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Total Pendapatan (Rp)',
                    data: pendapatanData,
                    backgroundColor: 'rgba(168, 85, 247, 0.6)',
                    borderColor: 'rgb(168, 85, 247)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { 
                            color: '#94a3b8',
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    legend: { labels: { color: '#cbd5e1' } }
                }
            }
        });
    </script>
</body>
</html>