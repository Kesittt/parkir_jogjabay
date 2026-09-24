<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: landing.php");
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$role     = isset($_SESSION['role']) ? $_SESSION['role'] : 'Pengunjung';
$role_lower = strtolower($role);

$is_admin = ($role_lower == 'admin' || $role_lower == 'administrator');
$is_petugas = ($role_lower == 'petugas');
$is_petugas_or_admin = ($is_admin || $is_petugas);
$is_pengunjung = ($role_lower == 'pengunjung');

// 1. Ambil data kendaraan yang sedang aktif parkir (status 'masuk')
$query_aktif = "SELECT t.*, k.*, tr.*, 
                COALESCE(tr.jenis_kendaraan, 'Kendaraan') as jenis_kendaraan, 
                COALESCE(a.nama_area, 'Area Utama') as nama_area_parkir 
                FROM tb_transaksi t 
                LEFT JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
                LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area 
                WHERE t.status = 'masuk'
                ORDER BY t.waktu_masuk DESC";
$result_aktif = mysqli_query($koneksi, $query_aktif);

// 2. Hitung jumlah kendaraan terisi secara dinamis
$q_terisi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi WHERE status = 'masuk'");
$d_terisi = mysqli_fetch_assoc($q_terisi);
$total_terisi = $d_terisi['total'] ?? 0;

// 3. Hitung total kapasitas secara dinamis dari tabel tb_area_parkir
$q_kapasitas = mysqli_query($koneksi, "SELECT SUM(kapasitas) as total FROM tb_area_parkir");
$d_kapasitas = mysqli_fetch_assoc($q_kapasitas);
$total_kapasitas = $d_kapasitas['total'] ?? 0;

$slot_tersedia = $total_kapasitas - $total_terisi;

// 4. Hitung pendapatan hari ini (khusus petugas/admin)
$pendapatan_hari_ini = 0;
if ($is_petugas_or_admin) {
    $today = date('Y-m-d');
    $q_pendapatan = mysqli_query($koneksi, "SELECT SUM(biaya_total) as total FROM tb_transaksi WHERE DATE(waktu_keluar) = '$today' AND status = 'keluar'");
    $d_pendapatan = mysqli_fetch_assoc($q_pendapatan);
    $pendapatan_hari_ini = $d_pendapatan['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Jogja Bay Luxury Parking</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
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
        .gold-button {
            background: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%);
            color: #0b1329;
            font-weight: 700;
        }
        .gold-button:hover {
            background: linear-gradient(135deg, #FFD54F 100%, #FFE082 100%);
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.4);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col md:flex-row">

    <!-- SIDEBAR KIRI -->
    <aside class="w-full md:w-64 glass-panel border-b md:border-b-0 md:border-r border-white/10 p-4 md:p-6 flex flex-col justify-between shrink-0">
        <div class="space-y-4 md:space-y-6">
            <!-- Brand / Logo -->
            <div class="flex items-center space-x-3 pb-4 md:pb-6 border-b border-white/10">
                <div class="p-2.5 bg-amber-500/20 rounded-xl border border-amber-500/30">
                    <i class="fa-solid fa-water-ladder text-amber-400 text-xl"></i>
                </div>
                <div>
                    <h1 class="font-extrabold tracking-wider text-xs gold-gradient-text">JOGJA BAY</h1>
                    <p class="text-[9px] text-cyan-300 uppercase tracking-widest font-semibold">Luxury Parking</p>
                </div>
            </div>

            <!-- Menu Navigasi Sidebar -->
            <nav class="flex md:flex-col gap-2 text-xs overflow-x-auto pb-2 md:pb-0">
                <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold transition whitespace-nowrap">
                    <i class="fa-solid fa-gauge w-5 text-center"></i> Dashboard Utama
                </a>

                <a href="booking.php" class="flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                    <i class="fa-solid fa-calendar-check w-5 text-center"></i> Link Booking
                </a>

                <?php if ($is_petugas_or_admin) : ?>
                    <a href="laporan.php" class="flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                        <i class="fa-solid fa-file-lines w-5 text-center"></i> Laporan Parkir
                    </a>
                    <a href="data_kendaraan.php" class="flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                        <i class="fa-solid fa-car-side w-5 text-center"></i> Data Kendaraan
                    </a>
                <?php endif; ?>

                <?php if ($is_admin) : ?>
                    <a href="admin_panel.php" class="flex items-center gap-3 px-4 py-2.5 md:py-3 rounded-xl text-slate-300 hover:bg-slate-800/60 hover:text-white transition whitespace-nowrap">
                        <i class="fa-solid fa-gauge-high w-5 text-center"></i> Panel Admin
                    </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Profil User & Tombol Keluar (Desktop) -->
        <div class="hidden md:block pt-6 border-t border-white/10 space-y-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0">
                    <?= strtoupper(substr($username, 0, 2)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($username); ?></p>
                    <p class="text-[10px] text-amber-400 uppercase">Sistem <?= htmlspecialchars($role); ?></p>
                </div>
            </div>
            <a href="logout.php" class="w-full bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-300 px-4 py-2.5 rounded-xl text-xs transition flex items-center justify-center gap-2 font-semibold">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </div>
        
        <!-- Tombol Keluar Versi Mobile -->
        <div class="flex md:hidden items-center justify-between pt-3 mt-3 border-t border-white/10 text-xs">
            <span class="text-amber-400 font-bold"><?= htmlspecialchars($username); ?></span>
            <a href="logout.php" class="bg-rose-500/20 text-rose-300 px-3 py-1.5 rounded-lg border border-rose-500/40 font-semibold">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA SEBELAH KANAN -->
    <main class="flex-1 p-4 md:p-8 space-y-6 overflow-y-auto flex flex-col justify-between">
        <div class="space-y-6">
            
            <!-- Check-In Kode Booking Bar (Khusus Petugas/Admin) -->
            <?php if ($is_petugas_or_admin) : ?>
            <div class="glass-panel p-5 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4 border border-cyan-500/30">
                <div class="flex items-center space-x-3 w-full md:w-auto">
                    <div class="p-3 bg-cyan-500/20 text-cyan-400 rounded-xl border border-cyan-500/30 shrink-0">
                        <i class="fa-solid fa-key text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white">Check-In Kode Booking</h2>
                        <p class="text-xs text-slate-400">Masukkan kode booking dan plat nomor kendaraan yang ingin check-in.</p>
                    </div>
                </div>
                <form action="proses_checkin_booking.php" method="POST" class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto">
                    <input type="text" name="kode_booking" placeholder="KODE: JB-X89A2" required class="bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 uppercase font-mono w-full sm:w-36">
                    <input type="text" name="plat_nomor" placeholder="PLAT: AB 1234 CD" required class="bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 uppercase font-mono w-full sm:w-36">
                    <button type="submit" name="checkin" class="bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold px-5 py-2 rounded-xl text-xs transition flex items-center gap-1.5 w-full sm:w-auto justify-center">
                        <i class="fa-solid fa-right-to-bracket"></i> Check-In
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Statistik Kartu Ringkasan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 <?= $is_petugas_or_admin ? 'lg:grid-cols-4' : 'lg:grid-cols-3'; ?> gap-4">
                <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border border-white/10">
                    <div>
                        <p class="text-[11px] text-slate-400 uppercase font-semibold">Total Kapasitas</p>
                        <h3 class="text-2xl font-extrabold text-white mt-1"><?= $total_kapasitas; ?> <span class="text-xs font-normal text-slate-400">Slot</span></h3>
                    </div>
                    <div class="p-3 bg-cyan-500/20 rounded-xl text-cyan-400 border border-cyan-500/30"><i class="fa-solid fa-square-parking text-lg"></i></div>
                </div>
                <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border border-white/10">
                    <div>
                        <p class="text-[11px] text-slate-400 uppercase font-semibold">Kendaraan Terisi</p>
                        <h3 class="text-2xl font-extrabold text-amber-400 mt-1"><?= $total_terisi; ?> <span class="text-xs font-normal text-slate-400">Unit</span></h3>
                    </div>
                    <div class="p-3 bg-amber-500/20 rounded-xl text-amber-400 border border-amber-500/30"><i class="fa-solid fa-car text-lg"></i></div>
                </div>
                <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border border-white/10">
                    <div>
                        <p class="text-[11px] text-slate-400 uppercase font-semibold">Slot Tersedia</p>
                        <h3 class="text-2xl font-extrabold text-emerald-400 mt-1"><?= $slot_tersedia; ?> <span class="text-xs font-normal text-slate-400">Slot</span></h3>
                    </div>
                    <div class="p-3 bg-emerald-500/20 rounded-xl text-emerald-400 border border-emerald-500/30"><i class="fa-solid fa-circle-check text-lg"></i></div>
                </div>
                
                <?php if ($is_petugas_or_admin) : ?>
                <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border border-white/10">
                    <div>
                        <p class="text-[11px] text-slate-400 uppercase font-semibold">Pendapatan Hari Ini</p>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-amber-300 mt-1">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="p-3 bg-purple-500/20 rounded-xl text-purple-400 border border-purple-500/30"><i class="fa-solid fa-wallet text-lg"></i></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Konten Khusus Petugas / Admin -->
            <?php if ($is_petugas_or_admin) : ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Input Masuk Kendaraan -->
                <div class="glass-panel p-5 md:p-6 rounded-2xl space-y-4 border border-white/10">
                    <div class="flex items-center space-x-2 text-amber-400">
                        <i class="fa-solid fa-square-plus text-base"></i>
                        <h2 class="text-xs font-bold uppercase tracking-wider">Input Masuk Kendaraan</h2>
                    </div>
                    <form action="proses_masuk.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Nomor Plat Kendaraan</label>
                            <input type="text" name="plat_nomor" placeholder="CONTOH: AB 1234 CD" required class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 uppercase font-mono focus:outline-none focus:border-amber-400">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Jenis</label>
                                <select name="id_tarif" class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400">
                                    <option value="1">Motor</option>
                                    <option value="2">Mobil</option>
                                    <option value="3">Bus / Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Warna</label>
                                <input type="text" name="warna" placeholder="Hitam/Putih" class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Area Parkir Tujuan</label>
                            <select name="id_area" class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400">
                                <option value="1">VIP Waterpark Zone</option>
                                <option value="2">Area Utama A</option>
                                <option value="3">Area Bus & Travel</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Nama Pemilik (Opsional)</label>
                            <input type="text" name="pemilik" placeholder="Nama pengunjung" class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">
                        </div>
                        <button type="submit" name="submit" class="w-full gold-button py-3 rounded-xl text-xs transition shadow-lg">
                            <i class="fa-solid fa-arrow-right-to-bracket mr-1"></i> Simpan & Masukkan Kendaraan
                        </button>
                    </form>
                </div>

                <!-- Tabel Kendaraan Aktif di Area -->
                <div class="lg:col-span-2 glass-panel p-5 md:p-6 rounded-2xl space-y-4 border border-white/10 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center space-x-2 text-cyan-300">
                                <i class="fa-solid fa-list-ul text-base"></i>
                                <h2 class="text-xs font-bold uppercase tracking-wider">Kendaraan Aktif di Area</h2>
                            </div>
                            <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-1 rounded-lg font-semibold">Live Monitor</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs whitespace-nowrap">
                                <thead>
                                    <tr class="border-b border-white/10 text-slate-400">
                                        <th class="py-2 px-3 font-semibold">PLAT NOMOR</th>
                                        <th class="py-2 px-3 font-semibold">JENIS</th>
                                        <th class="py-2 px-3 font-semibold">AREA</th>
                                        <th class="py-2 px-3 font-semibold">WAKTU MASUK</th>
                                        <th class="py-2 px-3 font-semibold">KODE BOOKING</th>
                                        <th class="py-2 px-3 font-semibold text-center">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php if (mysqli_num_rows($result_aktif) > 0) : ?>
                                        <?php while ($row = mysqli_fetch_assoc($result_aktif)) : ?>
                                            <tr>
                                                <td class="py-3 px-3 font-mono font-bold text-amber-300"><?= htmlspecialchars($row['plat_nomor']); ?></td>
                                                <td class="py-3 px-3 text-slate-300 uppercase"><?= htmlspecialchars($row['jenis_kendaraan']); ?></td>
                                                <td class="py-3 px-3 text-slate-300"><?= htmlspecialchars($row['nama_area_parkir']); ?></td>
                                                <td class="py-3 px-3 text-slate-300"><?= date('H:i', strtotime($row['waktu_masuk'])); ?> WIB</td>
                                                <td class="py-3 px-3 font-mono text-cyan-400"><?= !empty($row['kode_booking']) ? htmlspecialchars($row['kode_booking']) : '-'; ?></td>
                                                <td class="py-3 px-3 text-center">
                                                    <a href="proses_keluar.php?id=<?= $row['id_parkir']; ?>" class="bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 px-3 py-1.5 rounded-lg text-[11px] transition inline-block font-semibold">
                                                        Proses Keluar
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="6" class="py-6 text-center text-slate-400 italic">Belum ada kendaraan yang aktif di area parkir.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-400 text-right pt-2 border-t border-white/5">
                        Menampilkan data kendaraan yang sedang terparkir saat ini.
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Konten Khusus Pengunjung -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="glass-panel p-5 rounded-2xl border border-cyan-500/20 space-y-2">
                    <div class="text-cyan-400 text-lg mb-1"><i class="fa-solid fa-circle-info"></i></div>
                    <h3 class="text-xs font-bold text-white uppercase">Informasi Selamat Datang</h3>
                    <p class="text-[11px] text-slate-300 leading-relaxed">
                        Halo <b><?= htmlspecialchars($username); ?></b>! Anda login sebagai Pengunjung. Pantau ketersediaan slot parkir secara real-time dengan mudah.
                    </p>
                </div>
                <div class="glass-panel p-5 rounded-2xl border border-amber-500/20 space-y-2">
                    <div class="text-amber-400 text-lg mb-1"><i class="fa-solid fa-ticket"></i></div>
                    <h3 class="text-xs font-bold text-white uppercase">Cara Menggunakan Booking</h3>
                    <p class="text-[11px] text-slate-300 leading-relaxed">
                        Ingin memesan slot terlebih dahulu? Gunakan menu <b>Link Booking</b> di sidebar untuk membuat reservasi parkir Jogja Bay.
                    </p>
                </div>
                <div class="glass-panel p-5 rounded-2xl border border-emerald-500/20 space-y-2">
                    <div class="text-emerald-400 text-lg mb-1"><i class="fa-solid fa-shield-halved"></i></div>
                    <h3 class="text-xs font-bold text-white uppercase">Keamanan Kendaraan</h3>
                    <p class="text-[11px] text-slate-300 leading-relaxed">
                        Area parkir Jogja Bay diawasi ketat selama 24 jam dengan sistem manajemen modern demi keamanan dan kenyamanan kendaraan Anda.
                    </p>
                </div>
            </div>

            <!-- Tabel Riwayat Booking Pengunjung -->
            <?php
            $q_booking = "SELECT * FROM tb_booking WHERE nama = '$username' ORDER BY id DESC LIMIT 5";
            $res_booking = mysqli_query($koneksi, $q_booking);
            ?>
            <div class="glass-panel p-5 md:p-6 rounded-2xl space-y-4 border border-cyan-500/20">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2 text-cyan-300">
                        <i class="fa-solid fa-clock-rotate-left text-base"></i>
                        <h2 class="text-xs font-bold uppercase tracking-wider">Riwayat & Status Booking Saya</h2>
                    </div>
                    <a href="booking.php" class="text-[11px] text-amber-400 hover:underline font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> Buat Booking Baru
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-white/10 text-slate-400">
                                <th class="py-2 px-3 font-semibold">KODE BOOKING</th>
                                <th class="py-2 px-3 font-semibold">PLAT NOMOR</th>
                                <th class="py-2 px-3 font-semibold">TANGGAL / WAKTU</th>
                                <th class="py-2 px-3 font-semibold">AREA TUJUAN</th>
                                <th class="py-2 px-3 font-semibold text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if ($res_booking && mysqli_num_rows($res_booking) > 0) : ?>
                                <?php while ($b = mysqli_fetch_assoc($res_booking)) : ?>
                                    <tr>
                                        <td class="py-3 px-3 font-mono font-bold text-amber-300"><?= htmlspecialchars($b['kode_booking']); ?></td>
                                        <td class="py-3 px-3 font-mono text-white"><?= htmlspecialchars($b['plat']); ?></td>
                                        <td class="py-3 px-3 text-slate-300"><?= htmlspecialchars($b['tanggal']); ?></td>
                                        <td class="py-3 px-3 text-slate-300"><?= htmlspecialchars($b['zona']); ?></td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 px-2.5 py-1 rounded-lg text-[10px] font-semibold">
                                                Aktif
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400 italic">Belum ada riwayat booking. Silakan buat reservasi melalui menu Link Booking.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Footer -->
        <footer class="w-full text-center text-[11px] text-slate-500 py-4 mt-8 border-t border-white/10">
            © 2026 Jogja Bay Waterpark. All Rights Reserved.
        </footer>
    </main>

</body>
</html>