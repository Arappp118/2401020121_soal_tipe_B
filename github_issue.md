# GitHub Issue #1: Penanganan Pengecualian ID pada Validasi Keunikan Data saat Operasi UPDATE

## Informasi Masalah (Issue Information)
- **Judul Issue**: Fix: Pengecekan Duplikat Kode Peminjaman dan Nomor Identitas Menolak Pembaruan Record Sendiri (Self-Collision)
- **Label**: `bug`, `validation`, `database`, `pdo`
- **Status**: `CLOSED (RESOLVED)`
- **Penemu & Pengembang**: Ridho Ramadani (NIM: 2401020121)
- **Proyek**: Paket 2 Tipe B — Sistem Peminjaman Alat

---

## 1. Deskripsi Masalah yang Ditemukan
Saat pengguna membuka form edit pada route `GET /peminjaman/{id}/edit` dan mencoba memperbarui salah satu atribut non-identitas (misalnya hanya memperbarui `jumlah_alat` dari 2 menjadi 5 atau mengubah `nama_peminjam`) tanpa mengubah `kode_peminjaman` atau `nomor_identitas`, sistem menolak pengiriman form dan memunculkan pesan kesalahan:
`"Kode peminjaman sudah digunakan."`

Hal ini menyebabkan pengguna tidak dapat memperbarui data peminjaman yang sah jika kode peminjaman atau nomor identitasnya tetap dipertahankan.

---

## 2. Langkah Pemeriksaan (Diagnosis & Debugging)
1. Menjalankan penelusuran alur eksekusi pada method `update()` di `app/Http/Controllers/PeminjamanController.php`.
2. Memeriksa query prepared statement PDO yang digunakan untuk mengecek duplikat data:
   ```sql
   SELECT kode_peminjaman, nomor_identitas 
   FROM peminjaman_alat 
   WHERE kode_peminjaman = :kode OR nomor_identitas = :identitas 
   LIMIT 1;
   ```
3. Menguji eksekusi query secara manual melalui MariaDB CLI dengan parameter milik record ID 1 (`PM00000001` dan `MHS-2201001`).
4. Hasil query mengembalikan baris record ID 1 itu sendiri, yang kemudian memicu percabangan penolakan duplikat.

---

## 3. Penyebab Masalah (Root Cause)
Pengecekan keunikan pada operasi UPDATE tidak mengecualikan record yang sedang diedit. Query memperlakukan record aktif sebagai data duplikat, padahal suatu record diperbolehkan mempertahankan nilai unik miliknya sendiri. Penolakan seharusnya hanya berlaku jika nilai unik tersebut bentrok dengan record lain (`id <> :id`).

---

## 4. Perbaikan yang Dilakukan (Fix Implementation)
Memperbaiki query prepared statement pada method `update()` dengan menambahkan kondisi pengecualian `AND id <> :id`:

```php
// Sebelum:
$stmtCekDuplikat = $pdo->prepare(
    'SELECT kode_peminjaman, nomor_identitas 
     FROM peminjaman_alat 
     WHERE kode_peminjaman = :kode OR nomor_identitas = :identitas 
     LIMIT 1'
);
$stmtCekDuplikat->execute([
    'kode' => $cleanInput['kode_peminjaman'],
    'identitas' => $cleanInput['nomor_identitas'],
]);

// Sesudah (Perbaikan):
$stmtCekKonflik = $pdo->prepare(
    'SELECT kode_peminjaman, nomor_identitas 
     FROM peminjaman_alat 
     WHERE (kode_peminjaman = :kode OR nomor_identitas = :identitas) 
       AND id <> :id 
     LIMIT 1'
);
$stmtCekKonflik->execute([
    'kode' => $cleanInput['kode_peminjaman'],
    'identitas' => $cleanInput['nomor_identitas'],
    'id' => $id,
]);
```

Dengan perubahan ini:
- Record yang sedang diedit dapat mempertahankan kode peminjaman dan nomor identitasnya.
- Jika pengguna memasukkan kode atau nomor identitas milik record lain, query akan mengembalikan baris record lain tersebut dan sistem secara tepat menolaknya dengan pesan:
  `"Kode peminjaman sudah digunakan oleh data lain."` atau `"Nomor identitas sudah digunakan oleh peminjam lain."`.

---

## 5. Hasil Pengujian Ulang (Verification & Re-test)
Pengujian ulang dilakukan menggunakan automated test PHPUnit:
1. **Skenario 5 (test_05_update_menampilkan_data_lama_dan_berhasil_disimpan)**:
   - Mengedit record ID 1 dengan mempertahankan kode dan identitas, hanya mengubah nama dan jumlah alat.
   - **Hasil**: Lulus (PASSED). Data berhasil diperbarui di basis data MariaDB, status HTTP 302 redirect ke `/peminjaman`, feedback sukses ditampilkan.
2. **Skenario 6 (test_06_konflik_update_ditolak_jika_memakai_data_milik_record_lain)**:
   - Mengedit record ID 2 tetapi menggunakan kode milik record ID 1 (`PM00000001`).
   - **Hasil**: Lulus (PASSED). Sistem menolak pembaruan, redirect kembali ke form edit dengan pesan kesalahan aman, dan data record ID 2 tidak mengalami perubahan.
