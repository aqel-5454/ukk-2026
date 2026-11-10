# Debugging

Dokumen ini berisi kendala yang ditemui selama pembuatan aplikasi **Peminjaman Buku** (Laravel + Filament), serta kendala umum yang perlu diantisipasi beserta cara mengatasinya.

## A. Masalah yang Dialami Selama Pengembangan

### 1. Error saat menjalankan `php artisan migrate`

| | |
|---|---|
| **Gejala** | Perintah `php artisan migrate` gagal dengan pesan `SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it`. |
| **Penyebab** | Service MySQL belum dinyalakan, sehingga Laravel tidak bisa terhubung ke database. |
| **Solusi** | Menyalakan MySQL (Start pada XAMPP/Laragon), memastikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env` sudah benar, lalu menjalankan ulang `php artisan migrate`. |
| **Hasil** | Seluruh tabel berhasil dibuat. |

> Tambahkan masalah lain yang benar-benar Anda alami di bagian A ini dengan format yang sama.

## B. Kendala Umum pada Laravel + Filament dan Cara Mengatasinya

Bagian ini adalah daftar kendala yang lazim terjadi pada pengembangan aplikasi Laravel + Filament, sebagai bahan antisipasi dan referensi penanganan.

| No | Gejala / Pesan Error | Penyebab | Cara Mengatasi |
|---|---|---|---|
| 1 | `SQLSTATE[HY000] [1049] Unknown database` | Database belum dibuat, atau nama di `DB_DATABASE` tidak sesuai. | Buat database terlebih dahulu (phpMyAdmin), samakan nama dengan `.env`, lalu `php artisan config:clear`. |
| 2 | `MassAssignmentException: Add [judul] to fillable property` | Kolom belum didaftarkan pada `$fillable` di model saat data disimpan lewat form Filament. | Tambahkan kolom ke `protected $fillable` pada model (`Book`, `Loan`, `User`). |
| 3 | Halaman `/admin` menampilkan **403 Forbidden** | Model `User` belum mengizinkan akses panel (umumnya muncul di luar environment lokal). | Implementasikan `FilamentUser` pada model `User` dan buat method `canAccessPanel()` yang hanya mengizinkan `role === 'admin'`. |
| 4 | Resource atau menu baru tidak muncul, atau `Class ... not found` | Cache autoload/konfigurasi belum diperbarui, atau namespace salah. | Jalankan `composer dump-autoload` dan `php artisan optimize:clear`, lalu periksa namespace file Resource. |
| 5 | Kolom relasi di tabel Filament menampilkan ID, atau kosong | Relasi belum didefinisikan di model, atau nama kolom tidak memakai notasi relasi. | Definisikan `belongsTo` pada model `Loan`, lalu pakai `TextColumn::make('book.judul')` dan `TextColumn::make('user.name')`. |
| 6 | Stok buku menjadi negatif atau tidak sinkron saat dipinjam/dikembalikan | Stok tidak divalidasi sebelum peminjaman, atau update stok dan pembuatan transaksi tidak atomik. | Cek `stock > 0` sebelum menyimpan peminjaman, lalu bungkus pembuatan transaksi dan perubahan stok dalam `DB::transaction()`. |
| 7 | `Vite manifest not found` | Aset frontend belum di-build. | Jalankan `npm install` lalu `npm run build` (atau `npm run dev` saat pengembangan). |
| 8 | `419 Page Expired` saat login atau submit form | Token CSRF/session kedaluwarsa atau session tidak tersimpan. | Jalankan `php artisan optimize:clear`, bersihkan cookie browser, dan pastikan tabel `sessions` sudah ada serta `SESSION_DRIVER` di `.env` sesuai. |
| 9 | `Unable to prepare route ... for serialization` atau `Target class [...] does not exist` | Nama route/controller duplikat atau namespace salah. | Periksa `routes/web.php`, pastikan nama unik, lalu `php artisan route:clear`. |
| 10 | Perubahan `.env` tidak berpengaruh | Konfigurasi masih ter-cache. | `php artisan config:clear` lalu restart `php artisan serve`. |

## C. Pengujian Aplikasi (Manual per Fitur)

Isi kolom **Hasil** setelah Anda benar-benar menguji tiap skenario.

| No | Fitur | Skenario Uji | Hasil yang Diharapkan | Hasil |
|---|---|---|---|---|
| 1 | Login admin | Email dan password benar | Masuk ke dashboard admin | (isi) |
| 2 | Login admin | Password salah | Muncul pesan gagal login | (isi) |
| 3 | Data buku | Tambah, ubah, hapus buku | Data tersimpan dan berubah di daftar | (isi) |
| 4 | Anggota | Tambah, ubah, hapus anggota | Data tersimpan dan berubah di daftar | (isi) |
| 5 | Transaksi | Tambah, ubah, hapus transaksi | Data tersimpan dan berubah di daftar | (isi) |
| 6 | Peminjaman (siswa) | Pinjam buku yang stoknya ada | Transaksi `dipinjam` tercatat, stok berkurang | (isi) |
| 7 | Peminjaman (siswa) | Pinjam buku yang stoknya 0 | Ditolak | (isi) |
| 8 | Pengembalian (siswa) | Kembalikan buku | Status `dikembalikan`, `tgl_kembali` terisi, stok bertambah | (isi) |
