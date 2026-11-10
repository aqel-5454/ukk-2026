# Dokumentasi Fungsi dan Prosedur

Aplikasi **Peminjaman Buku** dibangun dengan Laravel dan Filament. Di Filament, setiap tabel database dikelola lewat sebuah **Resource**. Struktur tiap Resource di aplikasi ini:

```
app/Filament/Resources/<Nama>/
├── <Nama>Resource.php      # Konfigurasi resource, halaman, dan hak akses
├── Pages/                  # Halaman: List, Create, Edit
├── Schemas/<Nama>Form.php  # Definisi form input
└── Tables/<Nama>sTable.php # Definisi tabel dan aksi
```

Resource yang ada: `Books` (data buku), `Loans` (transaksi peminjaman), dan `Users` (anggota).

---

## 1. Prosedur Inti Aplikasi

### 1.1 Prosedur Peminjaman Buku (`pinjam`)

| | |
|---|---|
| **Lokasi** | `app/Filament/Resources/Books/Tables/BooksTable.php` (dalam `recordActions`) |
| **Jenis** | Action Filament (tombol pada setiap baris buku) |
| **Parameter** | `Book $record`: buku pada baris yang diklik |
| **Pengguna** | Siswa (tombol disembunyikan untuk admin) |
| **Kegunaan** | Mencatat peminjaman buku oleh siswa yang sedang login dan mengurangi stok buku |

**Kondisi tampil:**
- `visible(fn () => ! auth()->user()->isAdmin())`: tombol hanya terlihat oleh non-admin.
- `disabled(fn (Book $record) => $record->stock < 1)`: tombol nonaktif jika stok habis.
- `requiresConfirmation()`: muncul dialog konfirmasi sebelum dijalankan.

**Alur:**
1. Siswa menekan tombol **Pinjam** pada buku, lalu mengonfirmasi.
2. Sistem membuat data baru di tabel `loans` dengan `user_id` = ID siswa yang login, `book_id` = ID buku, `tgl_pinjam` = hari ini, `tgl_batas` = hari ini + 7 hari. Kolom `status` tidak diisi secara eksplisit, sehingga memakai nilai default database, yaitu `dipinjam`.
3. Stok buku dikurangi satu (`decrement('stock')`).
4. Muncul notifikasi sukses "Buku dipinjam olehmu!".

**Kode inti:**

```php
Action::make('pinjam')
    ->requiresConfirmation()
    ->visible(fn () => ! auth()->user()->isAdmin())
    ->disabled(fn (Book $record) => $record->stock < 1)
    ->action(function (Book $record) {
        Loan::create([
            'user_id'    => auth()->id(),
            'book_id'    => $record->id,
            'tgl_pinjam' => now(),
            'tgl_batas'  => now()->addDays(7),
        ]);

        $record->decrement('stock');
        Notification::make()->title('Buku dipinjam olehmu!')->success()->send();
    })
```

### 1.2 Prosedur Pengembalian Buku (`kembalikan`)

| | |
|---|---|
| **Lokasi** | `app/Filament/Resources/Loans/Tables/LoansTable.php` (dalam `recordActions`) |
| **Jenis** | Action Filament (tombol pada setiap baris transaksi) |
| **Parameter** | `Loan $record`: transaksi pada baris yang diklik |
| **Pengguna** | Siswa pada transaksinya sendiri, dan admin pada semua transaksi |
| **Kegunaan** | Menandai transaksi sebagai selesai dan mengembalikan stok buku |

**Kondisi tampil:**
- `visible(fn (Loan $record) => $record->status === 'dipinjam')`: tombol hanya muncul pada transaksi yang masih berstatus `dipinjam`, sehingga buku yang sudah dikembalikan tidak bisa dikembalikan dua kali.
- `requiresConfirmation()`: muncul dialog konfirmasi.

**Alur:**
1. Pengguna menekan tombol **Kembalikan** dan mengonfirmasi.
2. Sistem menghitung selisih hari antara `tgl_batas` dan hari ini (`diffInDays`) dan menyimpannya di variabel `$telat`.
3. Transaksi diperbarui: `tgl_kembali` = sekarang, `status` = `dikembalikan`.
4. Stok buku terkait ditambah satu (`$record->book->increment('stock')`).
5. Muncul notifikasi sukses "Buku sudah dikembalikan".

**Kode inti:**

```php
Action::make('kembalikan')
    ->requiresConfirmation()
    ->visible(fn (Loan $record) => $record->status === 'dipinjam')
    ->action(function (Loan $record) {
        $telat = (int) $record->tgl_batas->diffInDays(now()->startOfDay(), false);
        $record->update([
            'tgl_kembali' => now(),
            'status'      => 'dikembalikan',
        ]);
        $record->book->increment('stock');
        Notification::make()->title('Buku sudah dikembalikan')->success()->send();
    })
```

> Catatan: variabel `$telat` sudah dihitung tetapi belum dipakai (belum ada fitur denda keterlambatan).

---

## 2. Fungsi Pembatasan Akses

Dua method dipakai untuk mengatur siapa boleh melakukan apa.

### 2.1 `getAuthorizationResponse(UnitEnum|string $action, ?Model $record = null): Response`

| | |
|---|---|
| **Lokasi** | `BookResource`, `LoanResource`, `UserResource` |
| **Parameter** | `$action`: nama aksi yang diminta (`viewAny`, `create`, `update`, `delete`, dan seterusnya); `$record`: data yang diakses (opsional) |
| **Nilai kembalian** | `Response::allow()` bila diizinkan, `Response::deny()` bila ditolak |
| **Kegunaan** | Menentukan hak akses per resource berdasarkan role pengguna |

Aturan pada masing-masing resource:

| Resource | Admin | Siswa |
|---|---|---|
| `BookResource` | Semua aksi | Hanya melihat daftar (`viewAny`) |
| `LoanResource` | Hanya melihat daftar (`viewAny`) | Hanya melihat daftar (`viewAny`) |
| `UserResource` | Semua aksi | Ditolak seluruhnya (menu tidak tampil) |

Pada `LoanResource`, aksi `kembalikan` tetap bisa dipakai karena merupakan custom action pada tabel, bukan aksi bawaan seperti `create` atau `delete`.

### 2.2 `LoanResource::getEloquentQuery(): Builder`

| | |
|---|---|
| **Lokasi** | `app/Filament/Resources/Loans/LoanResource.php` |
| **Nilai kembalian** | Query `Builder` yang sudah difilter |
| **Kegunaan** | Membatasi data transaksi yang tampil |

Jika pengguna bukan admin, query ditambah `where('user_id', auth()->id())`, sehingga **siswa hanya melihat transaksi miliknya sendiri**. Admin melihat semua transaksi.

---

## 3. Fungsi Konfigurasi Resource

Method berikut ada pada setiap Resource (`BookResource`, `LoanResource`, `UserResource`).

| Method | Parameter | Nilai kembalian | Kegunaan |
|---|---|---|---|
| `form(Schema $schema)` | `Schema` | `Schema` | Memanggil `<Nama>Form::configure()` untuk membentuk form input |
| `table(Table $table)` | `Table` | `Table` | Memanggil `<Nama>sTable::configure()` untuk membentuk tabel daftar data |
| `getRelations()` | tidak ada | `array` | Daftar relation manager (kosong pada aplikasi ini) |
| `getPages()` | tidak ada | `array` | Mendaftarkan rute halaman: `index` (daftar), `create` (tambah), `edit` (ubah) |

### 3.1 Form (`app/Filament/Resources/*/Schemas/*Form.php`)

Method `configure(Schema $schema): Schema` mendefinisikan field form.

| Form | Field | Keterangan |
|---|---|---|
| `BookForm` | `judul`, `penerbit`, `penulis`, `tahun_rilis`, `stock` | Semua wajib diisi. `stock` numerik, default 0. |
| `LoanForm` | `user_id` (pilih pengguna), `book_id` (pilih buku), `tgl_pinjam`, `tgl_batas`, `tgl_kembali`, `status` | `user_id` dan `book_id` berupa Select yang mengambil data dari relasi. `status` default `dipinjam`. `tgl_kembali` boleh kosong. |
| `UserForm` | `name`, `email`, `role`, `kelas`, `password` | `role` berupa pilihan `admin` / `siswa` (default `siswa`). `password` wajib saat membuat data baru, sedangkan saat mengubah data hanya disimpan jika diisi (`dehydrated(fn ($state) => filled($state))`). |

### 3.2 Tabel (`app/Filament/Resources/*/Tables/*Table.php`)

Method `configure(Table $table): Table` mendefinisikan kolom, aksi baris, dan aksi massal.

| Tabel | Kolom | Aksi |
|---|---|---|
| `BooksTable` | judul, penerbit, penulis (dapat dicari), tahun_rilis, stock (dapat diurutkan), created_at, updated_at (disembunyikan default) | `pinjam`, `EditAction`, hapus massal |
| `LoansTable` | user.name, book.id, tgl_pinjam, tgl_batas, tgl_kembali, status (badge: kuning = `dipinjam`, hijau = `dikembalikan`) | `kembalikan`, hapus massal |
| `UsersTable` | name, email, role, kelas (dapat dicari), email_verified_at, created_at, updated_at | `EditAction`, hapus massal |

---

## 4. Halaman (Pages)

Setiap resource punya tiga halaman dengan pola yang sama.

| Kelas | Induk | Kegunaan |
|---|---|---|
| `ListBooks`, `ListLoans`, `ListUsers` | `ListRecords` | Menampilkan daftar data. `getHeaderActions()` mengembalikan tombol `CreateAction` (tambah data). |
| `CreateBook`, `CreateLoan`, `CreateUser` | `CreateRecord` | Menampilkan form tambah data. Tidak ada logika tambahan. |
| `EditBook`, `EditLoan`, `EditUser` | `EditRecord` | Menampilkan form ubah data. `getHeaderActions()` mengembalikan `DeleteAction` (hapus data). |

Semua halaman menghubungkan diri ke resource-nya lewat `protected static string $resource`.

---

## 5. Method pada Model

> Bagian ini ditulis berdasarkan pemakaiannya di kode di atas. Lengkapi setelah file model (`app/Models/`) diperiksa.

| Method | Model | Kegunaan |
|---|---|---|
| `isAdmin(): bool` | `User` | Mengembalikan `true` bila `role` pengguna adalah `admin`. Dipakai pada pembatasan akses dan tombol `pinjam`. |
| `user()` | `Loan` | Relasi `belongsTo` ke `User` (peminjam). Dipakai di kolom `user.name`. |
| `book()` | `Loan` | Relasi `belongsTo` ke `Book`. Dipakai di kolom `book.id` dan `$record->book->increment('stock')`. |
| casts `tgl_batas` | `Loan` | Diubah menjadi objek tanggal agar bisa memakai `diffInDays()`. |
