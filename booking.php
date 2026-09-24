<?php
include 'koneksi.php';

$success_code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Slot Parkir - Jogja Bay Waterpark</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(rgba(10, 25, 47, 0.75), rgba(10, 25, 47, 0.85)), 
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
        .gold-button {
            background: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%);
            color: #0b1329;
            font-weight: 700;
        }
        .gold-button:hover {
            background: linear-gradient(135deg, #FFD54F 0%, #FFE082 100%);
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.4);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col justify-between items-center p-4 md:p-8">

    <!-- Header / Brand -->
    <div class="text-center my-6">
        <div class="flex items-center justify-center space-x-3 mb-2">
            <i class="fa-solid fa-water-ladder text-amber-400 text-3xl"></i>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-wider gold-gradient-text">JOGJA BAY WATERPARK</h1>
        </div>
        <p class="text-xs md:text-sm text-cyan-300 tracking-widest uppercase font-semibold">E-PARKING RESERVATION SYSTEM</p>
    </div>

    <!-- Container Utama -->
    <div class="w-full max-w-xl">

        <?php if (!empty($success_code)): ?>
            <!-- CARD TAMPILAN SUKSES -->
            <div class="glass-panel p-8 md:p-10 rounded-3xl shadow-2xl mb-8 border border-amber-500/30 text-center space-y-6">
                
                <div class="w-16 h-16 bg-[#134e4a]/80 text-[#34d399] rounded-full flex items-center justify-center mx-auto text-2xl border border-[#059669]/40">
                    <i class="fa-solid fa-check"></i>
                </div>

                <div class="space-y-2">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-white">Booking Berhasil!</h2>
                    <p class="text-xs md:text-sm text-slate-300">Pembayaran DP dan data kendaraan Anda telah kami terima.</p>
                </div>

                <div class="bg-[#0f172a]/90 border border-slate-800/80 rounded-2xl p-6">
                    <span class="text-xs text-amber-400 font-bold uppercase tracking-wider block mb-2">KODE BOOKING ANDA</span>
                    <div class="text-amber-400 font-mono text-3xl md:text-4xl font-extrabold tracking-widest">
                        <?= $success_code; ?>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="booking.php" class="block w-full py-4 text-white font-bold rounded-2xl transition hover:bg-white/5 text-base">
                        Buat Booking Baru
                    </a>
                </div>

            </div>

        <?php else: ?>

            <!-- FORM RESERVASI -->
            <div class="glass-panel p-6 md:p-8 rounded-3xl shadow-2xl mb-8 border border-white/10">
                
                <div class="text-center mb-6">
                    <h2 class="text-xl font-bold text-white flex items-center justify-center gap-2">
                        <i class="fa-solid fa-calendar-check text-amber-400"></i> Reservasi Slot Parkir & DP QRIS
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Lakukan pembayaran DP via QRIS untuk mengamankan slot parkir Anda.</p>
                </div>

                <form action="proses_booking.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Pemilik / Pengunjung</label>
                        <input type="text" name="nama_pemilik" required placeholder="Masukkan nama lengkap" 
                               class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pilih Zone Parkir</label>
                        <select name="id_area" required class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-400 transition text-sm">
                            <option value="1">VIP Waterpark Zone</option>
                            <option value="2">Area Utama A</option>
                            <option value="3">Area Bus & Travel</option>
                        </select>
                    </div>

                    <!-- Input Tanggal Kunjungan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Rencana Kunjungan</label>
                        <input type="date" name="tanggal_booking" min="<?= date('Y-m-d'); ?>" required 
                               class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-400 transition text-sm">
                    </div>

                    <!-- Input Jam Rencana Kunjungan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Jam Rencana Kunjungan</label>
                        <input type="time" name="jam_booking" required 
                               class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-400 transition text-sm">
                        <p class="text-[10px] text-amber-300/80 mt-1">⚠️ Toleransi keterlambatan adalah 1 jam dari jam yang dipilih sebelum status booking otomatis hangus.</p>
                    </div>

                    <div class="space-y-4 pt-2">
                        <div class="flex justify-between items-center border-b border-white/10 pb-2">
                            <label class="text-xs font-semibold text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-car"></i> Daftar Kendaraan
                            </label>
                            <button type="button" id="btn-tambah-kendaraan" class="text-xs bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                <i class="fa-solid fa-plus"></i> Tambah Kendaraan
                            </button>
                        </div>

                        <div id="wrapper-kendaraan" class="space-y-4">
                            <div class="item-kendaraan bg-slate-900/50 p-4 rounded-2xl border border-white/10 relative space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-slate-400 label-nomor">Kendaraan #1</span>
                                    <button type="button" class="btn-hapus hidden text-xs text-rose-400 hover:text-rose-300 transition">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Nomor Plat Kendaraan</label>
                                    <input type="text" name="plat_nomor[]" required placeholder="Contoh: AB 1234 CD" 
                                           class="w-full bg-slate-950/80 border border-white/15 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition font-mono uppercase text-sm">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-300 mb-1">Jenis Kendaraan</label>
                                        <select name="id_tarif[]" required class="w-full bg-slate-950/80 border border-white/15 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-amber-400 transition text-sm">
                                            <option value="1">Motor (DP: Rp 5.000)</option>
                                            <option value="2">Mobil (DP: Rp 10.000)</option>
                                            <option value="3">Bus / Lainnya (DP: Rp 20.000)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-300 mb-1">Warna Kendaraan</label>
                                        <input type="text" name="warna[]" placeholder="Hitam/Putih" 
                                               class="w-full bg-slate-950/80 border border-white/15 rounded-xl px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN PEMBAYARAN QRIS -->
                    <div class="bg-slate-950/60 p-5 rounded-2xl border border-amber-500/20 text-center space-y-3">
                        <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider">Scan QRIS untuk Pembayaran DP</h3>
                        <p class="text-[11px] text-slate-400">Silakan scan QR di bawah menggunakan BCA, GoPay, OVO, Dana, atau QRIS Banking lainnya.</p>
                        
                        <!-- Gambar QRIS (Pastikan file qris_toko.png ada di folder yang sama atau sesuaikan path-nya) -->
                        <div class="bg-white p-3 inline-block rounded-xl shadow-md">
                            <img src="qris_toko.png" alt="QRIS Jogja Bay" class="w-36 h-36 mx-auto object-contain">
                        </div>

                        <div class="text-left pt-2">
                            <label class="block text-xs font-medium text-slate-300 mb-1">Upload Bukti Transfer / Pembayaran QRIS</label>
                            <input type="file" name="bukti_pembayaran" accept="image/*" required 
                                   class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-500/20 file:text-amber-300 hover:file:bg-amber-500/30 cursor-pointer">
                        </div>
                    </div>

                    <button type="submit" name="submit_booking" class="w-full gold-button py-4 rounded-2xl transition mt-4 flex items-center justify-center space-x-2 text-base">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>Konfirmasi & Dapatkan Kode Booking</span>
                    </button>
                </form>
            </div>

        <?php endif; ?>

    </div>

    <footer class="text-center text-xs text-slate-400 pb-4">
        <p>&copy; 2026 Jogja Bay Waterpark Luxury Services</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('wrapper-kendaraan');
            const btnTambah = document.getElementById('btn-tambah-kendaraan');

            if (wrapper && btnTambah) {
                function updateNomorDanTombol() {
                    const items = wrapper.querySelectorAll('.item-kendaraan');
                    items.forEach((item, index) => {
                        item.querySelector('.label-nomor').textContent = `Kendaraan #${index + 1}`;
                        const btnHapus = item.querySelector('.btn-hapus');
                        if (items.length > 1) {
                            btnHapus.classList.remove('hidden');
                        } else {
                            btnHapus.classList.add('hidden');
                        }
                    });
                }

                btnTambah.addEventListener('click', function () {
                    const firstItem = wrapper.querySelector('.item-kendaraan');
                    const newItem = firstItem.cloneNode(true);
                    
                    newItem.querySelectorAll('input').forEach(input => input.value = '');
                    newItem.querySelector('select').selectedIndex = 0;

                    wrapper.appendChild(newItem);
                    updateNomorDanTombol();
                });

                wrapper.addEventListener('click', function (e) {
                    if (e.target.closest('.btn-hapus')) {
                        const items = wrapper.querySelectorAll('.item-kendaraan');
                        if (items.length > 1) {
                            e.target.closest('.item-kendaraan').remove();
                            updateNomorDanTombol();
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>