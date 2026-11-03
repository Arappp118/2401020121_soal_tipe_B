-- ========================================================
-- UTS PRAKTIKUM PEMROGRAMAN WEB (PAKET 2 TIPE B)
-- SISTEM PEMINJAMAN ALAT
-- NIM  : 2401020121
-- Nama : Ridho Ramadani
-- ========================================================

-- Pastikan database yang digunakan benar
CREATE DATABASE IF NOT EXISTS peminjaman_alat_2401020121 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE peminjaman_alat_2401020121;

-- Hapus tabel jika sudah ada (urutkan sesuai dependensi relasi)
DROP TABLE IF EXISTS peminjaman_alat;
DROP TABLE IF EXISTS alat;

-- 1. Tabel Master: alat
CREATE TABLE alat (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_alat VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Transaksi: peminjaman_alat
CREATE TABLE peminjaman_alat (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_peminjaman CHAR(10) NOT NULL UNIQUE,
    nama_peminjam VARCHAR(100) NOT NULL,
    nomor_identitas VARCHAR(30) NOT NULL UNIQUE,
    jumlah_alat TINYINT UNSIGNED NOT NULL,
    alat_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_jumlah_alat CHECK (jumlah_alat BETWEEN 1 AND 10),
    CONSTRAINT fk_peminjaman_alat_alat_id FOREIGN KEY (alat_id)
        REFERENCES alat(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
