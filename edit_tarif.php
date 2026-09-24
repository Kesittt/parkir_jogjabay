<?php
session_start();
include 'koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['login']) || (strtolower($_SESSION['role']) != 'admin' && strtolower($_SESSION['role']) != 'administrator')) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update_tarif'])) {
    $id_tarif = mysqli_real_escape_string($koneksi, $_POST['id_tarif']);
    $jenis_kendaraan = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $tarif_per_jam = mysqli_real_escape_string($koneksi, $_POST['tarif_per_jam']);
    // Jika di database Anda ada kolom tarif flat/pertama, bisa disesuaikan

    $query = "UPDATE tb_tarif SET jenis_kendaraan = '$jenis_kendaraan', tarif_per_jam = '$tarif_per_jam' WHERE id_tarif = '$id_tarif'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: admin_panel.php#kelola-tarif");
        exit;
    } else {
        echo "Gagal mengupdate tarif: " . mysqli_error($koneksi);
    }
}

// Ambil data tarif berdasarkan ID
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $result = mysqli_query($koneksi, "SELECT * FROM tb_tarif WHERE id_tarif = '$id'");
    $tarif = mysqli_fetch_assoc($result);
    if (!$tarif) {
        header("Location: admin_panel.php");
        exit;
    }
} else {
    header("Location: admin_panel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tarif Parkir - Jogja Bay Luxury Parking</title>
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

    <div class="w-full max-w-md glass-panel p-6 md:p-8 rounded-3xl shadow-2xl border border-white/10 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-amber-500/20 text-amber-400 rounded-2xl border border-amber-500/30">
                    <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-white">Edit Tarif Parkir</h1>
                    <p class="text-xs text-slate-400">Sesuaikan nominal harga tarif</p>
                </div>
            </div>
            <a href="admin_panel.php#kelola-tarif" class="text-slate-400 hover:text-white text-sm transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </a>
        </div>

        <form action="" method="POST" class="space-y-4">
            <input type="hidden" name="id_tarif" value="<?= $tarif['id_tarif']; ?>">

            <div>
                <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Jenis Kendaraan</label>
                <input type="text" name="jenis_kendaraan" value="<?= htmlspecialchars($tarif['jenis_kendaraan']); ?>" required class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-300 uppercase mb-1">Tarif Per Jam (Rp)</label>
                <input type="number" name="tarif_per_jam" value="<?= htmlspecialchars($tarif['tarif_per_jam']); ?>" required class="w-full bg-slate-900/80 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400">
            </div>

            <div class="pt-4 flex items-center gap-3">
                <a href="admin_panel.php#kelola-tarif" class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold py-3 rounded-xl text-xs text-center transition">
                    Batal
                </a>
                <button type="submit" name="update_tarif" class="w-2/3 gold-button py-3 rounded-xl text-xs transition shadow-lg text-center">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</body>
</html>