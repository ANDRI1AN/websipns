# Web Sipns

Aplikasi web sederhana untuk pengelolaan data Pegawai Negeri Sipil (PNS) yang dibangun menggunakan PHP tanpa framework. Aplikasi ini memungkinkan admin untuk mengelola data PNS, instansi, jabatan, golongan, serta riwayat pendidikan dan jabatan.

## Fitur

- **Manajemen Pengguna**: Admin dapat login dan mengelola data pengguna.
- **Manajemen Instansi**: Mengelola data instansi tempat PNS bekerja.
- **Manajemen Jabatan**: Mengelola data jabatan struktural dan fungsional.
- **Manajemen Golongan**: Mengelola data golongan dan pangkat PNS.
- **Manajemen Data PNS**: Mengelola data PNS termasuk foto, riwayat pendidikan, dan riwayat jabatan.
- **Kenaikan Pangkat**: Mengelola proses kenaikan pangkat PNS.

## Struktur Database

Struktur database dapat dilihat pada file `database.sql` yang terlampir. Database terdiri dari beberapa tabel utama seperti `pengguna`, `instansi`, `jabatan`, `golongan`, `pns`, `kenaikan_pangkat`, `riwayat_pendidikan`, dan `riwayat_jabatan`.

## Instalasi

1. Clone repository ini ke direktori lokal Anda:
   ```bash
   git clone https://github.com/ANDRI1AN/websipns.git
   ```

2. Import database menggunakan file `database.sql` yang tersedia.

3. Sesuaikan konfigurasi koneksi database di file `config.php` (buat file ini jika belum ada).

4. Jalankan aplikasi di browser Anda.

## Kontribusi

Jika Anda ingin berkontribusi pada proyek ini, silakan fork repository ini dan buat pull request dengan perubahan yang Anda usulkan.

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

Dibuat dengan ❤️ oleh [ANDRI1AN](https://github.com/ANDRI1AN).
