# Deskripsi Program

## Gambaran Umum

**Peminjaman Buku** adalah aplikasi web untuk mencatat dan mengelola peminjaman buku perpustakaan sekolah. Aplikasi dibangun dengan Laravel dan Filament, dengan database MySQL. Terdapat dua jenis pengguna (role): **admin** dan **siswa**.

## Hak Akses

| Role | Kemampuan |
|---|---|
| Admin | Kelola data buku, kelola transaksi, kelola anggota |
| Siswa | Mendaftar sebagai anggota, login, meminjam buku, mengembalikan buku |

## Alur Kerja (sesuai flowchart)

1. Pengguna memulai aplikasi dan memilih login sebagai **admin** atau **siswa**.
2. **Alur admin**
   1. Admin memasukkan username/email dan password.
   2. Sistem memvalidasi login. Jika salah, kembali ke form login.
   3. Jika benar, admin masuk ke **dashboard admin** dan memilih menu:
      - **Kelola data buku**: tambah, lihat, ubah, hapus buku.
      - **Transaksi**: tambah, lihat, ubah, hapus data peminjaman.
      - **Kelola anggota**: tambah, lihat, ubah, hapus data anggota.
   3. Setelah selesai, admin kembali ke dashboard atau mengakhiri sesi.
3. **Alur siswa**
   1. Sistem memeriksa apakah siswa sudah terdaftar sebagai anggota. Jika belum, siswa **mendaftar** terlebih dahulu.
   2. Siswa memasukkan email dan password, lalu sistem memvalidasi login.
   3. Jika benar, siswa masuk ke **dashboard siswa** dan memilih menu:
      - **Peminjaman buku**: memilih buku dan melakukan peminjaman.
      - **Pengembalian buku**: mengembalikan buku yang sedang dipinjam.
4. Proses selesai (End).

## Daftar Halaman / Menu

| Halaman | Pengguna | Fungsi |
|---|---|---|
| Login | Admin, siswa | Autentikasi pengguna |
| Dashboard admin | Admin | Ringkasan dan akses menu |
| Data Buku | Admin | CRUD data buku |
| Transaksi (Peminjaman) | Admin | CRUD transaksi peminjaman |
| Anggota | Admin | CRUD data anggota/siswa |
| Dashboard siswa | Siswa | Akses menu peminjaman dan pengembalian |
| Peminjaman Buku | Siswa | Meminjam buku |
| Pengembalian Buku | Siswa | Mengembalikan buku |

> Sesuaikan nama halaman di atas dengan menu yang benar-benar ada di aplikasi Anda.

## Aturan Data

- Status peminjaman: `dipinjam` (awal) dan `dikembalikan` (setelah buku kembali).
- `tgl_batas` terisi saat peminjaman (di data contoh, 7 hari setelah `tgl_pinjam`).
- `tgl_kembali` terisi saat buku dikembalikan.
- Stok buku (`books.stock`) berkurang saat dipinjam dan bertambah saat dikembalikan.
