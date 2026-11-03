# UTS PRAKTIKUM PEMROGRAMAN WEB (PAKET 2 TIPE B)
## SISTEM PEMINJAMAN ALAT LABORATORIUM

Dokumentasi resmi pengerjaan Ujian Tengah Semester (UTS) Praktikum Pemrograman Web (INF11197) Semester V, Program Studi Teknik Informatika, Fakultas Teknik dan Teknologi Kemaritiman, Universitas Maritim Raja Ali Haji.

---

## 1. Identitas Proyek & Mahasiswa
- **Nama Mahasiswa**: Ridho Ramadani
- **NIM**: 2401020121
- **Kelas / Kode**: E / INF11197
- **Paket Soal**: Paket 2 — Tipe B
- **Judul Sistem**: Sistem Peminjaman Alat Laboratorium
- **Nama Basis Data**: `peminjaman_alat_2401020121`
- **Tabel Basis Data**: `alat` (master), `peminjaman_alat` (transaksi)
- **Route Utama**: `/peminjaman`

---

## 2. Deskripsi Aplikasi
Aplikasi **Sistem Peminjaman Alat** adalah aplikasi web berbasis Laravel dan PHP murni yang dikembangkan untuk mengelola transaksi peminjaman alat di laboratorium. Sistem ini dirancang berdasarkan konsep praktikum Modul 1 sampai Modul 7, mencakup:
1. **Arsitektur Client-Server & Routing RESTful**: Mendukung operasi CRUD lengkap dengan HTTP method spoofing (`PUT` dan `DELETE`).
2. **Pengolahan Input & Validasi**: Menerapkan sanitasi string (`trim()`, `strip_tags()`), validasi server-side, perlindungan token `@csrf`, dan persistensi input gagal melalui `old()`.
3. **Akses Data Berbasis PDO**: Seluruh interaksi basis data menggunakan `DB::connection()->getPdo()` dengan *Prepared Statements* (`prepare()`, `execute()`, `fetch()`, `fetchAll()`). Tidak menggunakan Eloquent ORM untuk operasi CRUD inti demi menjamin kepatuhan instruksi ujian dan pencegahan serangan SQL Injection.
4. **Relasi Basis Data Relasional**: Mengimplementasikan dua tabel saling berelasi satu-ke-banyak (*one-to-many*) antara tabel `alat` dan `peminjaman_alat` yang dilengkapi *Foreign Key* (`ON UPDATE CASCADE ON DELETE RESTRICT`), keunikan kolom (*UNIQUE*), serta batasan nilai (*CHECK constraint*).
5. **Keluaran Dinamis Blade & Fungsi Helper**: Menampilkan status volume peminjaman secara dinamis menggunakan helper function PHP `statusJumlah()` tanpa konflik deklarasi.
6. **Desain Tampilan Murni**: Sesuai instruksi teknis bersama, antarmuka dibangun secara murni menggunakan HTML semantik dan Blade tanpa pustaka CSS/JS eksternal (Bootstrap, Tailwind, jQuery, dll.) dan tanpa inline style.

---

## 3. Kebutuhan Perangkat (System Requirements)
- **Sistem Operasi**: Linux / macOS / Windows
- **PHP**: Versi 8.2 atau lebih baru (Diuji pada PHP 8.4.27 NTS)
- **Ekstensi PHP**: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`
- **Composer**: Versi 2.x (Diuji pada Composer 2.10.3)
- **Database Server**: MariaDB 10.5+ atau MySQL 8.0+ (Diuji pada MariaDB 11.8.9)
- **Version Control**: Git 2.30+

---

## 4. Struktur Direktori Proyek
```
2401020121_ridho_ramadani_e/
├── app/
│   ├── Helpers/
│   │   └── helpers.php             # Fungsi statusJumlah()
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Controller.php
│   │       └── PeminjamanController.php # Controller CRUD berbasis PDO
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2026_11_03_000001_create_alat_table.php
│   │   └── 2026_11_03_000002_create_peminjaman_alat_table.php
│   ├── seeders/
│   │   └── DatabaseSeeder.php      # Data awal wajib via PDO
│   └── sql/
│       ├── struktur_peminjaman.sql # DDL skema database
│       └── data_awal_peminjaman.sql# DML data awal
├── resources/
│   └── views/
│       └── peminjaman/
│           ├── index.blade.php     # Halaman utama (Read, Tambah, Search, Filter)
│           └── edit.blade.php      # Halaman form edit
├── routes/
│   └── web.php                     # Definisi route GET, POST, PUT, DELETE
├── screenshots/                    # Bukti tangkapan layar pengujian asli
├── tests/
│   └── Feature/
│       ├── ExampleTest.php
│       └── PeminjamanTest.php      # 10 Skenario pengujian otomatis PHPUnit
├── .env.example                    # Template konfigurasi environment
├── .gitignore                      # Proteksi .env, /vendor, /node_modules
├── github_issue.md                 # Dokumentasi Issue GitHub
├── Laporan_UTS_2401020121_Ridho_Ramadani.pdf # Laporan resmi PDF
├── phpunit.xml
└── README.md
```

---

## 5. Langkah Instalasi & Menjalankan Aplikasi

### Langkah 1: Kloning atau Ekstrak Proyek
Pastikan folder berada pada direktori kerja:
```bash
cd 2401020121_ridho_ramadani_e
```

### Langkah 2: Instalasi Dependensi Composer (Jika vendor belum terpasang)
```bash
composer install
```

### Langkah 3: Konfigurasi Environment (.env)
Salin berkas `.env.example` menjadi `.env`:
```bash
cp .env.example .env
php artisan key:generate
```
Buka berkas `.env` dan sesuaikan kredensial basis data Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=peminjaman_alat_2401020121
DB_USERNAME=root
DB_PASSWORD=rahasia_anda
```

### Langkah 4: Membuat Database & Menjalankan Migration
Buat basis data di MySQL / MariaDB:
```sql
CREATE DATABASE peminjaman_alat_2401020121 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Jalankan migration dan seeder Laravel:
```bash
php artisan migrate --seed
```
*Atau alternatif menggunakan berkas SQL langsung:*
```bash
mariadb -u root -p peminjaman_alat_2401020121 < database/sql/struktur_peminjaman.sql
mariadb -u root -p peminjaman_alat_2401020121 < database/sql/data_awal_peminjaman.sql
```

### Langkah 5: Menjalankan Server Lokal
```bash
php artisan serve
```
Buka peramban (browser) dan akses alamat:
`http://127.0.0.1:8000/peminjaman`

---

## 6. Daftar Route Aplikasi

| Method | URI | Nama Route | Deskripsi & Aksi |
|---|---|---|---|
| `GET` | `/` | - | Redirect otomatis ke `/peminjaman` |
| `GET` | `/peminjaman` | `peminjaman.index` | Menampilkan halaman utama (Tabel Read, Form Tambah, Form Cari & Filter) |
| `POST` | `/peminjaman` | `peminjaman.store` | Menyimpan data peminjaman baru (Create) via PDO |
| `GET` | `/peminjaman/{id}/edit` | `peminjaman.edit` | Menampilkan form ubah data dengan data lama terisi |
| `PUT` | `/peminjaman/{id}` | `peminjaman.update` | Memperbarui data peminjaman via PDO (Method Spoofing `@method('PUT')`) |
| `DELETE` | `/peminjaman/{id}` | `peminjaman.destroy` | Menghapus data peminjaman via PDO (Method Spoofing `@method('DELETE')`) |

---

## 7. Rincian Fitur Utama

### 1. Fitur CREATE (Tambah Data)
- **Input**: `kode_peminjaman`, `nama_peminjam`, `nomor_identitas`, `jumlah_alat`, `alat_id`.
- **Sanitasi**: Nilai teks dibersihkan dengan `trim()` dan `strip_tags()`.
- **Validasi Server-Side**:
  - `kode_peminjaman`: Tepat 10 karakter alfanumerik (`regex:/^[A-Za-z0-9]{10}$/`).
  - `nama_peminjam`: Teks 3 - 100 karakter.
  - `nomor_identitas`: Teks 5 - 30 karakter.
  - `jumlah_alat`: Bilangan bulat 1 - 10.
  - `alat_id`: Wajib dipilih dan diverifikasi keberadaannya pada tabel `alat`.
- **Pencegahan Duplikat**: Mengecek apakah kode peminjaman atau nomor identitas telah terdaftar.
- **Keamanan**: Disertai token `@csrf`. Jika validasi gagal, input lama dipertahankan dengan `old()`.

### 2. Fitur READ (Tampil Data & JOIN)
- Menampilkan seluruh transaksi peminjaman menggunakan query `JOIN` antara `peminjaman_alat` dan `alat`.
- Menampilkan kolom: ID, Kode Peminjaman, Nama Peminjam, Nomor Identitas, Nama Alat (dari master), Jumlah Alat, Status Jumlah (dari fungsi `statusJumlah()`), dan Aksi (Edit & Hapus).
- Menampilkan teks ramah jika data kosong.

### 3. Fitur UPDATE (Ubah Data)
- Menampilkan form edit dengan nilai awal dari record yang bersangkutan.
- Seluruh kolom dapat diperbarui.
- Aturan keunikan `kode_peminjaman` dan `nomor_identitas` mengecualikan record aktif (`AND id <> :id`), sehingga pembaruan record itu sendiri tidak dianggap sebagai duplikat, tetapi bentrokan dengan record lain tetap ditolak secara ketat.
- Menggunakan method spoofing `@method('PUT')` dan prepared statement.

### 4. Fitur DELETE (Hapus Data)
- Penghapusan data berdasarkan ID menggunakan method spoofing `@method('DELETE')` dan token CSRF.
- Menampilkan konfirmasi sebelum penghapusan (`onclick="return confirm(...)"`).
- Menolak penghapusan master alat jika masih ada relasi transaksi (`ON DELETE RESTRICT`).

### 5. Fitur PENCARIAN (Search 3 Kolom)
- Mendukung pencarian teks sebagian (*partial search*) menggunakan operator `LIKE %...%`.
- Memeriksa secara simultan 3 kolom: `kode_peminjaman`, `nama_peminjam`, dan `nomor_identitas`.
- Parameter input dikirim melalui query string `q` dan dibinding ke prepared statement PDO.

### 6. Fitur FILTER (Filter Berdasarkan Alat)
- Menyaring data transaksi berdasarkan `alat_id` yang dipilih dari tabel master.
- Dapat digabungkan secara bersamaan dengan fitur pencarian.

### 7. Helper Function PHP `statusJumlah()`
- Logika aturan:
  - Jika `jumlah_alat >= 3`: mengembalikan string `"Banyak"`.
  - Jika `jumlah_alat < 3`: mengembalikan string `"Sedikit"`.
- Dideklarasikan dalam pembungkus `if (!function_exists('statusJumlah'))` untuk mencegah konflik deklarasi ganda, serta di-autoload melalui Composer dan Service Provider.

---

## 8. Penerapan PDO & Keamanan Basis Data
Aplikasi ini secara disiplin menerapkan prinsip keamanan sesuai Modul 5:
1. **Prepared Statement & Parameter Binding**:
   Semua query `SELECT`, `INSERT`, `UPDATE`, dan `DELETE` yang menerima data pengguna menggunakan penanda parameter bernama (`:param`). Dilarang keras menggabungkan input mentah ke dalam string SQL (*string concatenation*).
2. **Penanganan Error try-catch & report()**:
   Semua operasi database dibungkus dalam blok `try { ... } catch (\Throwable $e) { report($e); ... }`. Jika terjadi kegagalan sistem atau koneksi:
   - Kesalahan teknis dan jejak *stack trace* dicatat ke dalam log sistem melalui `report($e)`.
   - Pengguna hanya menerima pesan kesalahan yang singkat, aman, dan informatif tanpa mengekspos kredensial, username, sandi, atau struktur internal database.
3. **Perlindungan File Konfigurasi**:
   File `.env` dilindungi oleh `.gitignore` dan tidak pernah dimasukkan ke dalam repositori Git maupun berkas pengumpulan akhir. Kebutuhan variabel lingkungan disediakan melalui `.env.example`.

---

## 9. Hasil 10 Skenario Pengujian (Automated & Manual)

Pengujian dilakukan secara menyeluruh menggunakan test suite PHPUnit (`tests/Feature/PeminjamanTest.php`):

| No | Skenario Pengujian | Input / Aksi | Hasil yang Diharapkan | Hasil Aktual | Status |
|:--:|---|---|---|---|:--:|
| 1 | **READ** | Akses `GET /peminjaman` | Tiga data awal dan nama alat relasi tampil dengan status jumlah | Tampil: PM00000001 (Rina Kurnia, Kamera, Sedikit), PM00000002 (Dedi Pranata, Proyektor, Sedikit), PM00000003 (Mira Ananda, Kamera, Banyak) | **LULUS** |
| 2 | **CREATE Valid** | `POST /peminjaman` (Kode: PM00000004, Nama: Fajar Nugraha, No ID: MHS-2201004, Jumlah: 4, Alat: 2) | Data tersimpan di MariaDB, redirect status 302, flash session 'success' tampil | Tersimpan di DB, redirect ke indeks, alert "Data peminjaman alat berhasil ditambahkan." tampil, baris baru muncul di tabel | **LULUS** |
| 3 | **CREATE Tidak Valid** | `POST /peminjaman` (Kode: PM01 [4 char], Nama: Al [2 char], Jumlah: 15, Alat: kosong) | Input ditolak validator, pesan error spesifik tampil, nilai lama dipertahankan via `old()` | Muncul pesan error untuk masing-masing field tidak valid, nilai input lama tetap terisi pada form | **LULUS** |
| 4 | **Duplikat** | `POST /peminjaman` (Kode duplikat: PM00000001 ATAU No ID: MHS-2201001) | Sistem menolak input, tidak merusak data yang ada, pesan error duplikat tampil | Input ditolak dengan pesan "Kode peminjaman sudah digunakan." atau "Nomor identitas sudah digunakan." | **LULUS** |
| 5 | **UPDATE Valid** | Akses `GET /peminjaman/1/edit` lalu `PUT /peminjaman/1` (Nama diubah: Rina Kurnia Pratama, Jumlah: 5) | Form edit memuat data lama, pembaruan tersimpan, status jumlah berubah dinamis | Form edit terisi data ID 1, setelah submit data terupdate di DB, status jumlah berubah menjadi 'Banyak', alert sukses tampil | **LULUS** |
| 6 | **Konflik UPDATE** | `PUT /peminjaman/2` mencoba memakai kode milik ID 1 (`PM00000001`) | Pembaruan ditolak, data ID 2 tetap utuh | Muncul pesan kesalahan "Kode peminjaman sudah digunakan oleh data lain.", data ID 2 tidak berubah | **LULUS** |
| 7 | **DELETE** | Form `DELETE /peminjaman/{id}` dengan method spoofing dan `@csrf` | Record terhapus dari basis data, feedback sukses tampil | Record terhapus permanen dari MariaDB, alert "Data peminjaman alat berhasil dihapus." tampil | **LULUS** |
| 8 | **Pencarian** | Akses `GET /peminjaman?q=Dedi` atau `q=PM00000003` atau `q=MHS-2201001` | Hanya data yang cocok pada salah satu dari 3 kolom yang ditampilkan | Kata kunci "Dedi" hanya menampilkan Dedi Pranata, kata kunci "PM00000003" hanya menampilkan Mira Ananda | **LULUS** |
| 9 | **Filter** | Akses `GET /peminjaman?alat_id=2` | Hanya transaksi dengan alat Proyektor yang ditampilkan | Hanya record Dedi Pranata (Proyektor) yang muncul, record Kamera tersembunyi | **LULUS** |
| 10 | **Error Handling** | Akses ID non-eksisten (`/peminjaman/99999/edit` atau delete) | Exception ditangani try-catch, dicatat via `report()`, pesan aman bagi pengguna | Menampilkan pesan aman "Data peminjaman tidak ditemukan.", tidak ada kebocoran SQL / stack trace | **LULUS** |

---

## 10. Catatan Riwayat Git Commit
Repositori dikelola dengan commit terstruktur sebagai berikut:
1. `commit 1`: `feat: inisialisasi proyek laravel dan konfigurasi dasar`
2. `commit 2`: `feat: membuat database, migration, dan data awal sql`
3. `commit 3`: `feat: implementasi helper fungsi statusJumlah, routing web, dan konfigurasi pdo`
4. `commit 4`: `feat: implementasi pdo controller dan fitur create read`
5. `commit 5`: `feat: implementasi fitur update delete pencarian dan filter`
6. `commit 6`: `test: menambahkan 10 skenario pengujian phpunit dan dokumentasi`
