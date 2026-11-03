-- ========================================================
-- UTS PRAKTIKUM PEMROGRAMAN WEB (PAKET 2 TIPE B)
-- DATA AWAL SISTEM PEMINJAMAN ALAT
-- NIM  : 2401020121
-- Nama : Ridho Ramadani
-- ========================================================

USE peminjaman_alat_2401020121;

-- 1. Data Awal Master Alat
INSERT INTO alat (id, nama_alat) VALUES
(1, 'Kamera'),
(2, 'Proyektor');

-- 2. Data Awal Transaksi Peminjaman Alat
INSERT INTO peminjaman_alat (id, kode_peminjaman, nama_peminjam, nomor_identitas, jumlah_alat, alat_id, created_at, updated_at) VALUES
(1, 'PM00000001', 'Rina Kurnia', 'MHS-2201001', 2, 1, NOW(), NOW()),
(2, 'PM00000002', 'Dedi Pranata', 'MHS-2201002', 1, 2, NOW(), NOW()),
(3, 'PM00000003', 'Mira Ananda', 'MHS-2201003', 3, 1, NOW(), NOW());
