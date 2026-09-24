<?php
session_start();
include 'koneksi.php';

$message = '';
$error = '';

if (isset($_POST['register'])) {
    $username     = trim(mysqli_real_escape_string($koneksi, $_POST['username']));
    $nama_lengkap = trim(mysqli_real_escape_string($koneksi, $_POST['nama_user']));
    $kontak       = trim(mysqli_real_escape_string($koneksi, $_POST['kontak'])); // No. HP / Email
    $password     = $_POST['password']; // Disimpan langsung tanpa di-hash
    
    // Role diset otomatis menjadi 'pengunjung'
    $role         = 'pengunjung';

    // Cek apakah username sudah dipakai
    $check_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username'");
    if (mysqli_num_rows($check_user) > 0) {
        $error = "Username sudah digunakan, pilih username lain!";
    } else {
        // Query langsung memasukkan variabel $password mentah-mentah ke database
        $query = "INSERT INTO tb_user (nama_lengkap, kontak, username, password, role) 
                  VALUES ('$nama_lengkap', '$kontak', '$username', '$password', '$role')";

        if (mysqli_query($koneksi, $query)) {
            $message = "Akun berhasil dibuat! Silakan <a href='login.php' class='underline text-amber-300 font-semibold'>Login di sini</a>";
        } else {
            $error = "Gagal mendaftarkan akun: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun - Jogja Bay Parking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(rgba(10, 25, 47, 0.85), rgba(10, 25, 47, 0.95)), 
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
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="glass-panel w-full max-w-md p-8 rounded-3xl shadow-2xl border border-amber-500/20">
        <div class="text-center mb-6">
            <div class="inline-flex p-3 bg-amber-500/20 rounded-2xl border border-amber-500/30 mb-3">
                <i class="fa-solid fa-user-plus text-amber-400 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-wider gold-gradient-text">REGISTRASI AKUN</h1>
            <p class="text-xs text-cyan-300 tracking-widest uppercase mt-1">Sistem Parkir Jogja Bay</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-rose-500/20 border border-rose-500/50 text-rose-300 px-4 py-3 rounded-xl mb-6 text-xs flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-xl mb-6 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= $message; ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input type="text" name="nama_user" required placeholder="Contoh: Budi Santoso" class="w-full bg-slate-900/80 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">No. HP / Email</label>
                <input type="text" name="kontak" required placeholder="Contoh: 08123456789 atau email@domain.com" class="w-full bg-slate-900/80 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" required placeholder="Masukkan username" class="w-full bg-slate-900/80 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <input type="password" name="password" required placeholder="Masukkan password" class="w-full bg-slate-900/80 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition text-sm">
            </div>

            <button type="submit" name="register" class="w-full gold-button py-3.5 rounded-xl transition mt-4 flex items-center justify-center space-x-2">
                <i class="fa-solid fa-user-check"></i>
                <span>Daftar Akun</span>
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400 border-t border-white/10 pt-4">
            <p>Sudah punya akun? <a href="login.php" class="text-amber-400 font-semibold hover:underline">Login di sini</a></p>
        </div>
    </div>

</body>
</html>