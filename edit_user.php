<?php
session_start();
include 'koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['login']) || (strtolower($_SESSION['role']) != 'admin' && strtolower($_SESSION['role']) != 'administrator')) {
    header("Location: index.php");
    exit;
}

$error = '';
$message = '';

// Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_panel.php#kelola-user");
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data user berdasarkan ID
$query_get = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id_user = '$id_user'");
if (mysqli_num_rows($query_get) == 0) {
    header("Location: admin_panel.php#kelola-user");
    exit;
}
$user = mysqli_fetch_assoc($query_get);

// Proses update data ketika form disubmit
if (isset($_POST['update_user'])) {
    $nama_lengkap = trim(mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']));
    $kontak       = trim(mysqli_real_escape_string($koneksi, $_POST['kontak']));
    $username     = trim(mysqli_real_escape_string($koneksi, $_POST['username']));
    $password     = $_POST['password']; // Disimpan langsung tanpa hash
    $role         = $_POST['role'];

    // Cek apakah username sudah digunakan user lain
    $check_username = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username' AND id_user != '$id_user'");
    if (mysqli_num_rows($check_username) > 0) {
        $error = "Username sudah digunakan oleh akun lain!";
    } else {
        $query_update = "UPDATE tb_user SET 
                         nama_lengkap = '$nama_lengkap', 
                         kontak = '$kontak', 
                         username = '$username', 
                         password = '$password', 
                         role = '$role' 
                         WHERE id_user = '$id_user'";

        if (mysqli_query($koneksi, $query_update)) {
            header("Location: admin_panel.php#kelola-user");
            exit;
        } else {
            $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - Panel Administrator</title>
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
            background: rgba(15, 23, 42, 0.8);
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
<body class="min-h-screen flex items-center justify-center p-4 text-slate-100">

    <div class="glass-panel w-full max-w-lg p-8 rounded-3xl shadow-2xl border border-purple-500/30">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-white/10">
            <div>
                <h1 class="text-xl font-bold tracking-wider gold-gradient-text">EDIT DATA PENGGUNA</h1>
                <p class="text-xs text-slate-400 mt-1">Ubah informasi akun dan level akses pengguna.</p>
            </div>
            <a href="admin_panel.php#kelola-user" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded-xl text-xs transition font-semibold border border-white/10">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-rose-500/20 border border-rose-500/50 text-rose-300 px-4 py-3 rounded-xl mb-6 text-xs flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required class="w-full bg-slate-900 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-400 transition text-sm">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-2">Kontak (No. HP / Email)</label>
                <input type="text" name="kontak" value="<?= htmlspecialchars($user['kontak']); ?>" required class="w-full bg-slate-900 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-400 transition text-sm">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required class="w-full bg-slate-900 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-400 transition text-sm">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <input type="text" name="password" value="<?= htmlspecialchars($user['password']); ?>" required class="w-full bg-slate-900 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-400 transition text-sm font-mono">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-2">Role / Level</label>
                <select name="role" required class="w-full bg-slate-900 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-400 transition text-sm">
                    <?php $current_role = strtolower($user['role']); ?>
                    <option value="pengunjung" <?= ($current_role == 'pengunjung') ? 'selected' : ''; ?>>Pengunjung</option>
                    <option value="petugas" <?= ($current_role == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                    <option value="admin" <?= ($current_role == 'admin' || $current_role == 'administrator') ? 'selected' : ''; ?>>Admin</option>
                    <option value="owner" <?= ($current_role == 'owner') ? 'selected' : ''; ?>>Owner</option>
                </select>
            </div>

            <button type="submit" name="update_user" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-bold py-3.5 rounded-xl transition mt-6 flex items-center justify-center space-x-2 shadow-lg text-sm">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>

</body>
</html>