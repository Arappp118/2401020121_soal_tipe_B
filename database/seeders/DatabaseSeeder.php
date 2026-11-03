<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $pdo = DB::connection()->getPdo();

        // 1. Data Awal Master Alat
        $alatList = [
            ['id' => 1, 'nama_alat' => 'Kamera'],
            ['id' => 2, 'nama_alat' => 'Proyektor'],
        ];

        $stmtAlat = $pdo->prepare('INSERT IGNORE INTO alat (id, nama_alat) VALUES (:id, :nama_alat)');
        foreach ($alatList as $item) {
            $stmtAlat->execute($item);
        }

        // 2. Data Awal Transaksi Peminjaman Alat
        $peminjamanList = [
            [
                'id' => 1,
                'kode_peminjaman' => 'PM00000001',
                'nama_peminjam' => 'Rina Kurnia',
                'nomor_identitas' => 'MHS-2201001',
                'jumlah_alat' => 2,
                'alat_id' => 1,
            ],
            [
                'id' => 2,
                'kode_peminjaman' => 'PM00000002',
                'nama_peminjam' => 'Dedi Pranata',
                'nomor_identitas' => 'MHS-2201002',
                'jumlah_alat' => 1,
                'alat_id' => 2,
            ],
            [
                'id' => 3,
                'kode_peminjaman' => 'PM00000003',
                'nama_peminjam' => 'Mira Ananda',
                'nomor_identitas' => 'MHS-2201003',
                'jumlah_alat' => 3,
                'alat_id' => 1,
            ],
        ];

        $stmtPeminjaman = $pdo->prepare(
            'INSERT IGNORE INTO peminjaman_alat 
             (id, kode_peminjaman, nama_peminjam, nomor_identitas, jumlah_alat, alat_id, created_at, updated_at) 
             VALUES (:id, :kode_peminjaman, :nama_peminjam, :nomor_identitas, :jumlah_alat, :alat_id, NOW(), NOW())'
        );

        foreach ($peminjamanList as $peminjaman) {
            $stmtPeminjaman->execute($peminjaman);
        }
    }
}
