<?php
session_start();
include 'koneksi.php';

$error = '';
$sukses_login = false;
$role_tujuan = 'index.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Mencari berdasarkan kolom username saja
    $query = "SELECT * FROM tb_user WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi password (mendukung password_verify atau plain text)
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'] ?? 'pengunjung';

            // Tentukan tujuan halaman berdasarkan role
            $role_lower = strtolower($_SESSION['role']);
            if ($role_lower == 'admin' || $role_lower == 'administrator') {
                $role_tujuan = "admin_panel.php";
            } else {
                $role_tujuan = "index.php";
            }

            // Tandai sukses agar animasi notifikasi & suara berhasil diputar di browser
            $sukses_login = true;
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username atau Akun tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jogja Bay Luxury Parking</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col justify-between">

    <!-- Navbar Header Sederhana -->
    <header class="w-full px-6 py-4 flex justify-between items-center glass-panel border-b border-white/10">
        <div class="flex items-center space-x-3">
            <a href="landing.php" class="flex items-center space-x-2">
                <div class="p-2 bg-amber-500/20 rounded-xl border border-amber-500/30">
                    <i class="fa-solid fa-water-ladder text-amber-400 text-lg"></i>
                </div>
                <span class="font-extrabold tracking-wider text-base md:text-lg gold-gradient-text">JOGJA BAY PARKING</span>
            </a>
        </div>
        <div>
            <a href="landing.php" class="px-4 py-2 text-xs text-slate-300 hover:text-white transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </header>

    <!-- Form Login Utama -->
    <main class="container mx-auto px-6 py-12 flex flex-col items-center justify-center my-auto">
        <div class="glass-panel w-full max-w-md p-8 rounded-3xl border border-white/10 shadow-2xl relative">
            
            <div class="text-center mb-8">
                <div class="inline-flex p-3 bg-amber-500/20 rounded-2xl border border-amber-500/30 text-amber-400 text-2xl mb-3 shadow-inner">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </div>
                <h2 class="text-2xl font-extrabold tracking-tight text-white">Masuk ke Sistem</h2>
                <p class="text-xs text-slate-400 mt-1">Silakan masukkan akun terdaftar Anda</p>
            </div>

            <!-- Notifikasi Error + Efek Suara Gagal -->
            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-base"></i>
                    <span><?= $error; ?></span>
                </div>
                <script>
                    window.addEventListener('DOMContentLoaded', function() {
                        try {
                            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            const oscillator = audioCtx.createOscillator();
                            const gainNode = audioCtx.createGain();
                            
                            oscillator.type = 'sawtooth';
                            oscillator.frequency.setValueAtTime(140, audioCtx.currentTime);
                            
                            gainNode.gain.setValueAtTime(0.15, audioCtx.currentTime);
                            
                            oscillator.connect(gainNode);
                            gainNode.connect(audioCtx.destination);
                            
                            oscillator.start();
                            oscillator.stop(audioCtx.currentTime + 0.35);
                        } catch (e) {
                            console.log("Audio diblokir atau tidak didukung browser.");
                        }
                    });
                </script>
            <?php endif; ?>

            <!-- Notifikasi Sukses Login + Efek Suara Chime Naik + Redirect -->
            <?php if ($sukses_login): ?>
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-3 shadow-lg">
                    <i class="fa-solid fa-circle-check text-base"></i>
                    <span>Password Benar! Login Berhasil, mengalihkan...</span>
                </div>
                <script>
                    window.addEventListener('DOMContentLoaded', function() {
                        try {
                            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            
                            // Nada 1 (C5)
                            const osc1 = audioCtx.createOscillator();
                            const gain1 = audioCtx.createGain();
                            osc1.type = 'sine';
                            osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime);
                            gain1.gain.setValueAtTime(0.1, audioCtx.currentTime);
                            osc1.connect(gain1);
                            gain1.connect(audioCtx.destination);
                            osc1.start();
                            osc1.stop(audioCtx.currentTime + 0.12);

                            // Nada 2 (E5)
                            setTimeout(() => {
                                const osc2 = audioCtx.createOscillator();
                                const gain2 = audioCtx.createGain();
                                osc2.type = 'sine';
                                osc2.frequency.setValueAtTime(659.25, audioCtx.currentTime);
                                gain2.gain.setValueAtTime(0.1, audioCtx.currentTime);
                                osc2.connect(gain2);
                                gain2.connect(audioCtx.destination);
                                osc2.start();
                                osc2.stop(audioCtx.currentTime + 0.18);
                            }, 100);

                            // Nada 3 (G5)
                            setTimeout(() => {
                                const osc3 = audioCtx.createOscillator();
                                const gain3 = audioCtx.createGain();
                                osc3.type = 'sine';
                                osc3.frequency.setValueAtTime(783.99, audioCtx.currentTime);
                                gain3.gain.setValueAtTime(0.1, audioCtx.currentTime);
                                osc3.connect(gain3);
                                gain3.connect(audioCtx.destination);
                                osc3.start();
                                osc3.stop(audioCtx.currentTime + 0.25);
                            }, 200);
                        } catch (e) {
                            console.log("Audio dicegah oleh browser.");
                        }

                        // Jeda 1 detik agar suara & animasi notifikasi hijau sempat terekam dengan baik
                        setTimeout(function() {
                            window.location.href = "<?= $role_tujuan; ?>";
                        }, 1000);
                    });
                </script>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 text-xs">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" name="username" required placeholder="Masukkan username..." 
                            class="w-full pl-10 pr-4 py-3 bg-slate-900/60 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" required placeholder="••••••••" 
                            class="w-full pl-10 pr-4 py-3 bg-slate-900/60 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
                    </div>
                </div>

                <button type="submit" name="login" class="w-full gold-button py-3.5 rounded-xl text-sm transition shadow-lg flex items-center justify-center gap-2 mt-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
                </button>
            </form>

            <div class="text-center mt-6 text-xs text-slate-400">
                Belum punya akun? <a href="register.php" class="text-amber-400 font-bold hover:underline">Daftar di sini</a>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full py-4 text-center text-xs text-slate-400 border-t border-white/10 glass-panel">
        <p>© 2026 Jogja Bay Waterpark. All Rights Reserved.</p>
    </footer>

</body>
</html>