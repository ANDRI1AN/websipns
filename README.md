# Aplikasi Web Pengelolaan Pegawai PNS

## Deskripsi
Aplikasi web ini dirancang untuk mengelola data Pegawai Negeri Sipil (PNS), termasuk pengelolaan data pegawai, kenaikan pangkat, dan laporan kepegawaian. Dibangun menggunakan PHP dan MySQL, aplikasi ini memungkinkan admin untuk mengelola data dengan mudah melalui antarmuka yang sederhana.

## Fitur
- **Manajemen Pegawai**: Tambah, edit, hapus, dan lihat daftar pegawai.
- **Kenaikan Pangkat**: Kelola proses kenaikan pangkat pegawai.
- **Laporan Kepegawaian**: Tampilkan laporan terkait data pegawai.
- **Dashboard Admin**: Ringkasan data pegawai secara visual.

## Teknologi yang Digunakan
- **Bahasa Pemrograman**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript, Tailwind CSS

## Instalasi
1. Clone repositori ini:
   ```bash
   git clone https://github.com/ANDRI1AN/websipns.git
   ```
2. Pindahkan ke folder proyek:
   ```bash
   cd websipns
   ```
3. Impor database `si_pns` menggunakan file `database.sql` yang tersedia.
4. Konfigurasi koneksi database di file `config.php`:
   ```php
   <?php
   $host = 'localhost';
   $username = 'root';
   $password = '';
   $database = 'si_pns';

   $conn = new mysqli($host, $username, $password, $database);
   if ($conn->connect_error) {
       die("Koneksi database gagal: " . $conn->connect_error);
   }
   $conn->set_charset("utf8mb4");
   ?>
   ```
5. Jalankan aplikasi dengan mengakses `http://localhost/websipns` melalui server lokal seperti XAMPP atau MAMP.

## Penggunaan
1. Buka browser dan akses `http://localhost/websipns`.
2. Login sebagai admin untuk mengakses fitur manajemen.
3. Gunakan menu navigasi untuk mengelola data pegawai, instansi, jabatan, dan golongan.

## Kontribusi
Jika ingin berkontribusi, silakan fork repositori ini dan buat pull request dengan perubahan yang diusulkan.

## Lisensi
Aplikasi ini dikembangkan untuk penggunaan internal dan bebas digunakan dengan modifikasi sesuai kebutuhan.

---

### Catatan Database
- Database yang digunakan adalah `si_pns` dengan tabel-tabel utama seperti `pns`, `instansi`, `jabatan`, dan `golongan`.
- Pastikan semua tabel yang diperlukan sudah diimpor sebelum menjalankan aplikasi.

---

Dibuat dengan ❤️ oleh [ANDRI1AN](https://github.com/ANDRI1AN).
