<?php
session_start();
include 'koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['login']) || (strtolower($_SESSION['role']) != 'admin' && strtolower($_SESSION['role']) != 'administrator')) {
    header("Location: index.php");
    exit;
}

$pesan_error = "";

if (isset($_POST['simpan_user'])) {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $kontak       = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_raw = $_POST['password'];
    $role         = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Hash password menggunakan password_hash agar aman (sesuai format database Anda)
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

    // Cek struktur kolom tabel tb_user Anda (menyesuaikan nama kolom di database)
    // Umumnya kolomnya: nama_lengkap, kontak, username, password, role
    $query = "INSERT INTO tb_user (nama_lengkap, kontak, username, password, role) 
              VALUES ('$nama_lengkap', '$kontak', '$username', '$password_hash', '$role')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: admin_panel.php#kelola-user");
        exit;
    } else {
        $pesan_error = "Gagal menambah user: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User Baru - Jogja Bay Luxury Parking</title>
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
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .gold-button {
            background: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%);
            color: #0b1329;
            font-weight: 700;
        }
        .gold-button:hover {
            background: linear-gradient(135deg, #FFD54F 100%, #FFE082 100%);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg glass-panel p-6 md:p-8 rounded-3xl shadow-2xl border border-white/10 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-amber-500/20 text-amber-400 rounded-2xl border border-amber-500/30">
                    <i class="fa-solid fa-user-plus text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-white">Tambah User Baru</h1>
                    <p class="text-xs text-slate-400">Daftarkan akun petugas atau admin baru</p>
                </div>
            </div>
            <a href="admin_panel.php#kelola-user" class="text-slate-400 hover:text-white text-sm transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </a>
        </div>

        <?php if (!empty($pesan_error)) : ?>
            <div class="p-3 bg-rose-500/20 border border-rose-500/40 text-rose-300 rounded-xl text-xs">
                <?= $pesan_error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Contoh: Budi Santoso" required class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Kontak / No. HP</label>
                <input type="text" name="kontak" placeholder="Contoh: 08820088..." required class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Username</label>
                    <input type="text" name="username" placeholder="Username login" required class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Password</label>
                    <input type="password" name="password" placeholder="Password akun" required class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Role / Hak Akses</label>
                <select name="role" class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400">
                    <option value="petugas">PETUGAS</option>
                    <option value="admin">ADMIN</option>
                </select>
            </div>

            <div class="pt-4 flex items-center gap-3">
                <a href="admin_panel.php#kelola-user" class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold py-3 rounded-xl text-xs text-center transition">
                    Batal
                </a>
                <button type="submit" name="simpan_user" class="w-2/3 gold-button py-3 rounded-xl text-xs transition shadow-lg text-center">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan User
                </button>
            </div>
        </form>
    </div>

</body>
</html>