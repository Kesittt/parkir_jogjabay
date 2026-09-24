<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi database aktif

// Proses jika form dikirim (Menggunakan PRG pattern agar aman dari double submit)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kirim_komentar'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);$komentar = mysqli_real_escape_string($koneksi,$_POST['komentar']);
    $rating = intval($_POST['rating']);

    if (!empty($nama) && !empty($komentar)) {
        // Pastikan tabel tb_ulasan ada di database
        mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS tb_ulasan (
            id_ulasan INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            rating INT NOT NULL,
            komentar TEXT NOT NULL,
            tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Insert ke tabel tb_ulasan sesuai struktur phpMyAdmin Anda
        $query_insert = "INSERT INTO tb_ulasan (nama, rating, komentar) VALUES ('$nama', $rating, '$komentar')";
        if (mysqli_query($koneksi,$query_insert)) {
            header("Location: landing.php?status=sukses");
            exit();
        } else {
            header("Location: landing.php?status=gagal");
            exit();
        }
    } else {
        header("Location: landing.php?status=kosong");
        exit();
    }
}

// Ambil data jumlah kendaraan terisi berdasarkan jenis kendaraannya secara dinamis
$q_motor = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi t JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif WHERE t.status = 'masuk' AND (tr.jenis_kendaraan LIKE '%motor%' OR t.id_tarif = 1)");
$d_motor = mysqli_fetch_assoc($q_motor);
$total_motor =$d_motor['total'] ?? 0;

$q_mobil = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi t JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif WHERE t.status = 'masuk' AND (tr.jenis_kendaraan LIKE '%mobil%' OR t.id_tarif = 2)");
$d_mobil = mysqli_fetch_assoc($q_mobil);
$total_mobil =$d_mobil['total'] ?? 0;

$q_bus = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_transaksi t JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif WHERE t.status = 'masuk' AND (tr.jenis_kendaraan LIKE '%bus%' OR tr.jenis_kendaraan LIKE '%lain%' OR t.id_tarif = 3)");
$d_bus = mysqli_fetch_assoc($q_bus);
$total_bus =$d_bus['total'] ?? 0;

// Ambil daftar ulasan dari tabel tb_ulasan
$daftar_komentar = [];
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tb_ulasan'");
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    $q_ulasan = mysqli_query($koneksi, "SELECT * FROM tb_ulasan ORDER BY id_ulasan DESC LIMIT 6");
    if ($q_ulasan) {
        while ($row = mysqli_fetch_assoc($q_ulasan)) {
            $daftar_komentar[] =$row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogja Bay Luxury Parking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(rgba(10, 25, 47, 0.75), rgba(10, 25, 47, 0.9)), 
                        url('https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?q=80&w=2070&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .gold-gradient-text {
            background: linear-gradient(135deg, #FFE082 0%, #FFB300 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gold-button {
            background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
            color: #0b1329;
            font-weight: 700;
        }
        .gold-button:hover {
            background: linear-gradient(135deg, #FFD700 0%, #FFF099 100%);
            box-shadow: 0 0 25px rgba(212, 175, 55, 0.5);
        }
        .outline-gold-button {
            border: 2px solid #FFD700;
            color: #FFD700;
            font-weight: 700;
        }
        .outline-gold-button:hover {
            background: rgba(255, 215, 0, 0.1);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col justify-between">

    <!-- Navbar Header -->
    <header class="w-full px-6 py-4 flex justify-between items-center glass-panel border-b border-white/10">
        <div class="flex items-center space-x-3">
            <img src="logo_sanden.png" alt="Logo SMKN 1 Sanden" class="w-8 h-8 object-cover rounded-full border border-amber-400/40 shadow-sm">
            <div class="flex items-center space-x-2">
                <div class="p-2 bg-amber-500/20 rounded-xl border border-amber-500/30 hidden sm:block">
                    <i class="fa-solid fa-water-ladder text-amber-400 text-lg"></i>
                </div>
                <span class="font-extrabold tracking-wider text-base md:text-lg gold-gradient-text">JOGJA BAY PARKING</span>
            </div>
            <img src="logo_rpl.png" alt="Logo RPL" class="w-8 h-8 object-cover rounded-full border border-amber-400/40 shadow-sm">
        </div>
        <div class="flex items-center gap-2">
            <button onclick="toggleHelpModal()" class="px-3.5 py-2 text-xs outline-gold-button rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-circle-question"></i> <span class="hidden sm:inline">Bantuan</span>
            </button>
            <?php if (isset($_SESSION['login'])): ?>
                <a href="index.php" class="px-4 py-2 text-xs gold-button rounded-xl shadow-lg transition">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="px-4 py-2 text-xs gold-button rounded-xl shadow-lg transition">Login</a>
                <a href="register.php" class="px-4 py-2 text-xs outline-gold-button rounded-xl transition hidden sm:inline-block">Daftar</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Hero Section -->
    <main class="container mx-auto px-6 py-12 flex flex-col items-center text-center my-auto">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-semibold mb-6">
            <i class="fa-solid fa-shield-halved"></i> Sistem Parkir Terintegrasi & Aman
        </div>
        
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-4 max-w-3xl leading-tight">
            Solusi Parkir Modern di <span class="gold-gradient-text">Jogja Bay Waterpark</span>
        </h1>
        
        <p class="text-slate-300 text-sm md:text-base max-w-xl mb-8 leading-relaxed">
            Nikmati kemudahan pencatatan, keamanan kendaraan terjamin, serta efisiensi layanan parkir kelas dunia dalam satu genggaman sistem digital.
        </p>

        <!-- Call to Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 w-full max-w-md justify-center mb-12">
            <a href="register.php" class="gold-button px-8 py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2 shadow-xl">
                <i class="fa-solid fa-user-plus"></i> Buat Akun Pengunjung
            </a>
            <a href="login.php" class="outline-gold-button px-8 py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sistem
            </a>
        </div>

        <!-- Video Preview Area Parkir -->
        <div class="w-full max-w-3xl mx-auto mb-10 glass-panel p-4 md:p-6 rounded-3xl border border-white/10 shadow-2xl">
            <div class="flex items-center justify-between mb-4 px-2">
                <h3 class="text-sm md:text-base font-extrabold text-white flex items-center gap-2 text-left">
                    <i class="fa-solid fa-circle-play text-amber-400"></i> Video Preview Area Parkir
                </h3>
                <span class="px-3 py-1 bg-amber-500/20 border border-amber-500/30 text-amber-300 rounded-xl text-[10px] font-semibold">
                    Local Video
                </span>
            </div>
            <div class="relative w-full overflow-hidden rounded-2xl shadow-inner border border-white/10 bg-slate-900/80" style="padding-top: 56.25%;">
                <video controls autoplay muted loop class="absolute top-0 left-0 w-full h-full object-cover rounded-2xl">
                    <source src="vid_parkir.mp4" type="video/mp4">
                    Browser Anda tidak mendukung tag video HTML5.
                </video>
            </div>
        </div>

        <!-- Grafik Statistik Kendaraan Parkir Aktif -->
        <div class="w-full max-w-3xl mx-auto mb-16 glass-panel p-6 rounded-3xl border border-white/10 shadow-2xl text-left">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
                <div>
                    <h3 class="text-sm md:text-base font-extrabold text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-amber-400"></i> Statistik Kendaraan Parkir Aktif
                    </h3>
                    <p class="text-xs text-slate-400">Grafik visual real-time jenis kendaraan yang sedang terisi di area.</p>
                </div>
                <span class="px-3 py-1 bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 rounded-xl text-[10px] font-semibold">
                    Live Update
                </span>
            </div>
            <div class="relative w-full h-64 md:h-72 flex justify-center items-center">
                <canvas id="grafikKendaraan"></canvas>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- BAGIAN FITUR ULASAN & KOMENTAR -->
        <!-- ========================================== -->
        <div class="w-full max-w-4xl mx-auto mb-16 text-left">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg md:text-xl font-extrabold text-white flex items-center gap-2">
                        <i class="fa-solid fa-comments text-amber-400"></i> Ulasan & Komentar Pengunjung
                    </h3>
                    <p class="text-xs text-slate-300">Bagikan kesan, pesan, atau masukan Anda mengenai sistem parkir kami.</p>
                </div>
                <button onclick="toggleKomentarModal()" class="gold-button px-4 py-2 rounded-xl text-xs flex items-center gap-2 shadow-lg">
                    <i class="fa-solid fa-pen-to-square"></i> Tulis Komentar
                </button>
            </div>

            <!-- Notifikasi Status dari URL -->
            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] == 'sukses'): ?>
                    <div class="mb-6 p-4 bg-emerald-500/20 border border-emerald-500/40 rounded-2xl text-emerald-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i> Ulasan/komentar berhasil dikirim dan masuk ke database!
                    </div>
                <?php elseif ($_GET['status'] == 'gagal'): ?>
                    <div class="mb-6 p-4 bg-rose-500/20 border border-rose-500/40 rounded-2xl text-rose-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Gagal menyimpan ke database `tb_ulasan`, periksa koneksi atau kolom tabel Anda.
                    </div>
                <?php elseif ($_GET['status'] == 'kosong'): ?>
                    <div class="mb-6 p-4 bg-amber-500/20 border border-amber-500/40 rounded-2xl text-amber-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Nama dan komentar wajib diisi!
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- List Card Komentar dari Database (`tb_ulasan`) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if (count($daftar_komentar) > 0): ?>
                    <?php foreach ($daftar_komentar as$kom): ?>
                        <div class="glass-panel p-5 rounded-2xl border border-white/10 flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex text-amber-400 text-xs gap-1 mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= intval($kom['rating'])): ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-star text-slate-500"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-xs text-slate-200 italic leading-relaxed">"<?= htmlspecialchars($kom['komentar']); ?>"</p>
                            </div>
                            <div class="flex items-center justify-between border-t border-white/10 pt-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400 font-bold text-[10px]">
                                        <?= strtoupper(substr($kom['nama'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-white"><?= htmlspecialchars($kom['nama']); ?></h4>
                                        <p class="text-[9px] text-slate-400"><?= date('d M Y, H:i', strtotime($kom['tanggal'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full glass-panel p-8 rounded-2xl text-center text-slate-400 text-xs">
                        <i class="fa-regular fa-comment-dots text-3xl mb-2 text-amber-400/60 block"></i>
                        Belum ada data ulasan di tabel `tb_ulasan`. Jadilah yang pertama memberikan ulasan!
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- ========================================== -->
    <!-- MODAL FORM INPUT KOMENTAR -->
    <!-- ========================================== -->
    <div id="komentarModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md hidden justify-center items-center z-50 p-4">
        <div class="glass-panel w-full max-w-md rounded-3xl border border-amber-500/40 p-6 md:p-8 space-y-5 text-left shadow-2xl">
            <div class="flex justify-between items-center border-b border-white/10 pb-4">
                <div class="flex items-center space-x-3 text-amber-300">
                    <div class="p-2 bg-amber-500/20 rounded-xl border border-amber-500/30">
                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm md:text-base font-extrabold uppercase tracking-wider text-white">Tulis Ulasan</h3>
                        <p class="text-[10px] text-amber-300">Jogja Bay Luxury Parking</p>
                    </div>
                </div>
                <button onclick="toggleKomentarModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition flex items-center justify-center border border-white/10">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Lengkap / Panggilan</label>
                    <input type="text" name="nama" required placeholder="Contoh: Budi Santoso" class="w-full px-4 py-2.5 bg-slate-900/80 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:border-amber-400">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Rating / Penilaian</label>
                    <select name="rating" class="w-full px-4 py-2.5 bg-slate-900/80 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:border-amber-400">
                        <option value="5">⭐⭐⭐⭐⭐ (5/5 - Sangat Memuaskan)</option>
                        <option value="4">⭐⭐⭐⭐ (4/5 - Bagus & Rapi)</option>
                        <option value="3">⭐⭐⭐ (3/5 - Cukup)</option>
                        <option value="2">⭐⭐ (2/5 - Kurang)</option>
                        <option value="1">⭐ (1/5 - Buruk)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Komentar / Pesan</label>
                    <textarea name="komentar" rows="3" required placeholder="Tuliskan pengalaman atau masukan Anda..." class="w-full px-4 py-2.5 bg-slate-900/80 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:border-amber-400"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="toggleKomentarModal()" class="px-4 py-2.5 text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition">Batal</button>
                    <button type="submit" name="kirim_komentar" class="px-5 py-2.5 text-xs gold-button rounded-xl shadow-lg transition">Kirim Ulasan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL POPUP BANTUAN / FAQ -->
    <!-- ========================================== -->
    <div id="helpModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md hidden justify-center items-center z-50 p-4">
        <div class="glass-panel w-full max-w-2xl rounded-3xl border border-amber-500/40 p-6 md:p-8 space-y-6 max-h-[90vh] overflow-y-auto text-left shadow-2xl">
            <div class="flex justify-between items-center border-b border-white/10 pb-4">
                <div class="flex items-center space-x-3 text-amber-300">
                    <div class="p-2 bg-amber-500/20 rounded-xl border border-amber-500/30">
                        <i class="fa-solid fa-circle-question text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm md:text-base font-extrabold uppercase tracking-wider text-white">Pusat Bantuan & FAQ</h3>
                        <p class="text-[10px] text-amber-300">Jogja Bay Luxury Parking System</p>
                    </div>
                </div>
                <button onclick="toggleHelpModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition flex items-center justify-center border border-white/10">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="space-y-4 text-xs md:text-sm text-slate-300">
                <div class="p-4 bg-slate-900/60 rounded-2xl border border-white/5 space-y-1.5">
                    <p class="font-bold text-amber-300 flex items-center gap-2">
                        <i class="fa-solid fa-circle-dot text-[8px]"></i> Bagaimana cara membuat akun pengunjung baru?
                    </p>
                    <p class="text-slate-400 pl-4 leading-relaxed">Klik tombol <strong class="text-slate-200">"Buat Akun Pengunjung"</strong> di halaman utama, lalu isi formulir pendaftaran dengan informasi lengkap.</p>
                </div>
                <div class="p-4 bg-slate-900/60 rounded-2xl border border-white/5 space-y-1.5">
                    <p class="font-bold text-amber-300 flex items-center gap-2">
                        <i class="fa-solid fa-circle-dot text-[8px]"></i> Bagaimana cara menulis ulasan atau komentar?
                    </p>
                    <p class="text-slate-400 pl-4 leading-relaxed">Klik tombol <strong class="text-slate-200">"Tulis Komentar"</strong> pada bagian ulasan di halaman utama, isi nama, rating, dan pesan Anda lalu klik kirim.</p>
                </div>
            </div>
            <div class="pt-2 text-center">
                <button onclick="toggleHelpModal()" class="px-6 py-2.5 text-xs gold-button rounded-xl shadow-lg transition font-bold">
                    Tutup Bantuan
                </button>
            </div>
        </div>
    </div>

    <!-- Script Inisialisasi Chart.js & Modal Toggle -->
    <script>
        function toggleHelpModal() {
            const modal = document.getElementById('helpModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }

        function toggleKomentarModal() {
            const modal = document.getElementById('komentarModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }

        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('grafikKendaraan').getContext('2d');
            const dataMotor = <?= $total_motor; ?>;
            const dataMobil = <?= $total_mobil; ?>;
            const dataBus = <?= $total_bus; ?>;

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sepeda Motor', 'Mobil Pribadi', 'Bus / Kendaraan Lain'],
                    datasets: [{
                        label: 'Jumlah Unit',
                        data: [dataMotor, dataMobil, dataBus],
                        backgroundColor: [
                            'rgba(255, 193, 7, 0.85)',
                            'rgba(6, 182, 212, 0.85)',
                            'rgba(168, 85, 247, 0.85)'
                        ],
                        borderColor: [
                            'rgba(255, 193, 7, 1)',
                            'rgba(6, 182, 212, 1)',
                            'rgba(168, 85, 247, 1)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#cbd5e1',
                                font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                                padding: 20
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        });
    </script>

    <!-- Footer -->
    <footer class="w-full py-4 text-center text-xs text-slate-400 border-t border-white/10 glass-panel">
        <p>© 2026 Jogja Bay Waterpark. All Rights Reserved.</p>
    </footer>

</body>
</html>