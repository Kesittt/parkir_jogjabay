# Jogja Bay Luxury Parking System 🅿️💧

Sistem Manajemen Parkir berbasis web modern yang dirancang khusus untuk **Jogja Bay Waterpark**. Aplikasi ini menyediakan pemantauan slot parkir secara *real-time*, manajemen transaksi masuk/keluar kendaraan, sistem reservasi/booking mandiri bagi pengunjung, serta panel laporan keuangan dan aktivitas harian.

---

## 🚀 Fitur Utama

1. **Dashboard Multi-Role (Admin, Petugas, & Pengunjung)**:
   * **Pengunjung**: Dapat melihat ketersediaan slot parkir secara *real-time*, melakukan *booking* slot parkir secara online, serta memantau riwayat status pemesanan.
   * **Petugas / Admin**: Dapat memproses kendaraan masuk dan keluar, melakukan *check-in* kode *booking*, melihat pendapatan harian, serta memantau kendaraan yang sedang aktif terparkir.
2. **Manajemen Slot Parkir Dinamis**: Perhitungan kapasitas total (270 slot), kendaraan terisi, dan sisa slot yang otomatis diperbarui secara *live*.
3. **Sistem Booking & Check-In**: Pengunjung dapat memesan slot terlebih dahulu dan mendapatkan kode *booking* unik untuk divalidasi oleh petugas di gerbang parkir.
4. **Laporan & Cetak Data**: Rekapitulasi seluruh data transaksi parkir lengkap dengan fitur cetak laporan.

---

## 🛠️ Teknologi yang Digunakan

* **Frontend**: HTML5, Tailwind CSS (CDN), FontAwesome 6, Google Fonts (*Plus Jakarta Sans*)
* **Backend**: PHP (Native Sessions & Database Connection)
* **Database**: MySQL
* **Hosting**: InfinityFree

---

## 📂 Struktur File Proyek

```text
├── landing.php           # Halaman utama sambutan (Landing Page)
├── index.php             # Dashboard utama aplikasi (menyesuaikan role user)
├── booking.php           # Halaman reservasi/booking slot parkir untuk pengunjung
├── laporan.php           # Halaman rekapitulasi laporan transaksi parkir
├── data_kendaraan.php    # Database daftar kendaraan terdaftar
├── admin_panel.php       # Panel kontrol khusus administrator
├── koneksi.php           # Konfigurasi koneksi database MySQL
├── proses_masuk.php      # Skrip backend input kendaraan masuk
├── proses_keluar.php     # Skrip backend proses kendaraan keluar & hitung biaya
├── proses_checkin_booking.php # Skrip validasi kode booking pengunjung
└── logout.php            # Skrip penghancur sesi (destroy session)
