<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use PDO;

class PeminjamanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Pastikan data awal selalu siap sebelum pengujian
        $pdo = DB::connection()->getPdo();
        
        // Bersihkan tabel peminjaman_alat dan reset data awal wajib
        $pdo->exec('DELETE FROM peminjaman_alat WHERE id > 3');
        
        // Pastikan master alat tersedia
        $pdo->exec("INSERT IGNORE INTO alat (id, nama_alat) VALUES (1, 'Kamera'), (2, 'Proyektor')");
        
        // Reset 3 data awal
        $pdo->exec("INSERT INTO peminjaman_alat (id, kode_peminjaman, nama_peminjam, nomor_identitas, jumlah_alat, alat_id, created_at, updated_at) 
                    VALUES 
                    (1, 'PM00000001', 'Rina Kurnia', 'MHS-2201001', 2, 1, NOW(), NOW()),
                    (2, 'PM00000002', 'Dedi Pranata', 'MHS-2201002', 1, 2, NOW(), NOW()),
                    (3, 'PM00000003', 'Mira Ananda', 'MHS-2201003', 3, 1, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        kode_peminjaman = VALUES(kode_peminjaman),
                        nama_peminjam = VALUES(nama_peminjam),
                        nomor_identitas = VALUES(nomor_identitas),
                        jumlah_alat = VALUES(jumlah_alat),
                        alat_id = VALUES(alat_id)");
    }

    /**
     * Skenario 1: READ
     * Tiga data awal dan nama tabel master tampil pada indeks.
     */
    public function test_01_read_menampilkan_tiga_data_awal_dan_relasi_master_alat(): void
    {
        $response = $this->get('/peminjaman');

        $response->assertStatus(200);
        $response->assertSee('Sistem Peminjaman Alat');
        $response->assertSee('PM00000001');
        $response->assertSee('Rina Kurnia');
        $response->assertSee('MHS-2201001');
        $response->assertSee('Kamera');
        $response->assertSee('Sedikit'); // statusJumlah(2)

        $response->assertSee('PM00000002');
        $response->assertSee('Dedi Pranata');
        $response->assertSee('MHS-2201002');
        $response->assertSee('Proyektor');

        $response->assertSee('PM00000003');
        $response->assertSee('Mira Ananda');
        $response->assertSee('MHS-2201003');
        $response->assertSee('Banyak'); // statusJumlah(3)
    }

    /**
     * Skenario 2: CREATE valid
     * Data baru tersimpan dan feedback keberhasilan tampil.
     */
    public function test_02_create_valid_tersimpan_dan_menampilkan_feedback(): void
    {
        $payload = [
            'kode_peminjaman' => 'PM00000004',
            'nama_peminjam' => 'Fajar Nugraha',
            'nomor_identitas' => 'MHS-2201004',
            'jumlah_alat' => '4',
            'alat_id' => '2',
        ];

        $response = $this->post('/peminjaman', $payload);

        $response->assertRedirect('/peminjaman');
        $response->assertSessionHas('success', 'Data peminjaman alat berhasil ditambahkan.');

        // Verifikasi langsung di basis data via PDO
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('SELECT * FROM peminjaman_alat WHERE kode_peminjaman = :kode');
        $stmt->execute(['kode' => 'PM00000004']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($row);
        $this->assertEquals('Fajar Nugraha', $row['nama_peminjam']);
        $this->assertEquals(4, $row['jumlah_alat']);
    }

    /**
     * Skenario 3: CREATE tidak valid
     * Pesan kesalahan tampil dan input lama dipertahankan.
     */
    public function test_03_create_invalid_menolak_input_dan_mempertahankan_old_input(): void
    {
        // Kode kurang dari 10 karakter, nama terlalu pendek, jumlah di luar 1-10
        $payload = [
            'kode_peminjaman' => 'PM001',
            'nama_peminjam' => 'AB',
            'nomor_identitas' => '12',
            'jumlah_alat' => '25',
            'alat_id' => '',
        ];

        $response = $this->post('/peminjaman', $payload);

        $response->assertRedirect('/peminjaman');
        $response->assertSessionHasErrors([
            'kode_peminjaman',
            'nama_peminjam',
            'nomor_identitas',
            'jumlah_alat',
            'alat_id',
        ]);
        $this->assertEquals('PM001', session()->getOldInput('kode_peminjaman'));
        $this->assertEquals('AB', session()->getOldInput('nama_peminjam'));
    }

    /**
     * Skenario 4: Duplikat
     * Kode peminjaman atau nomor identitas yang sama ditolak tanpa merusak data.
     */
    public function test_04_create_duplikat_kode_atau_identitas_ditolak(): void
    {
        // Mencoba menggunakan kode yang sudah ada (PM00000001)
        $payloadDuplikatKode = [
            'kode_peminjaman' => 'PM00000001',
            'nama_peminjam' => 'Mahasiswa Baru',
            'nomor_identitas' => 'MHS-9999999',
            'jumlah_alat' => '2',
            'alat_id' => '1',
        ];

        $responseKode = $this->post('/peminjaman', $payloadDuplikatKode);
        $responseKode->assertRedirect('/peminjaman');
        $responseKode->assertSessionHas('error', 'Kode peminjaman sudah digunakan.');

        // Mencoba menggunakan nomor identitas yang sudah ada (MHS-2201001)
        $payloadDuplikatIdentitas = [
            'kode_peminjaman' => 'PM99999999',
            'nama_peminjam' => 'Mahasiswa Lain',
            'nomor_identitas' => 'MHS-2201001',
            'jumlah_alat' => '1',
            'alat_id' => '1',
        ];

        $responseIdentitas = $this->post('/peminjaman', $payloadDuplikatIdentitas);
        $responseIdentitas->assertRedirect('/peminjaman');
        $responseIdentitas->assertSessionHas('error', 'Nomor identitas sudah digunakan.');
    }

    /**
     * Skenario 5: UPDATE
     * Data lama tampil pada form edit dan perubahan berhasil disimpan.
     */
    public function test_05_update_menampilkan_data_lama_dan_berhasil_disimpan(): void
    {
        // 1. Cek tampilan form edit
        $responseEdit = $this->get('/peminjaman/1/edit');
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('PM00000001');
        $responseEdit->assertSee('Rina Kurnia');
        $responseEdit->assertSee('MHS-2201001');

        // 2. Kirim update dengan HTTP PUT
        $updatePayload = [
            'kode_peminjaman' => 'PM00000001',
            'nama_peminjam' => 'Rina Kurnia Pratama',
            'nomor_identitas' => 'MHS-2201001',
            'jumlah_alat' => '5',
            'alat_id' => '1',
        ];

        $responseUpdate = $this->put('/peminjaman/1', $updatePayload);
        $responseUpdate->assertRedirect('/peminjaman');
        $responseUpdate->assertSessionHas('success', 'Data peminjaman alat berhasil diperbarui.');

        // Verifikasi ke basis data via PDO
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('SELECT nama_peminjam, jumlah_alat FROM peminjaman_alat WHERE id = 1');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Rina Kurnia Pratama', $row['nama_peminjam']);
        $this->assertEquals(5, $row['jumlah_alat']);
    }

    /**
     * Skenario 6: Konflik UPDATE
     * Kode atau nomor identitas milik data lain ditolak.
     */
    public function test_06_konflik_update_ditolak_jika_memakai_data_milik_record_lain(): void
    {
        // Edit record ID 2, tetapi memakai kode atau nomor identitas milik record ID 1
        $payloadKonflik = [
            'kode_peminjaman' => 'PM00000001', // Milik ID 1
            'nama_peminjam' => 'Dedi Pranata',
            'nomor_identitas' => 'MHS-2201002',
            'jumlah_alat' => '1',
            'alat_id' => '2',
        ];

        $response = $this->put('/peminjaman/2', $payloadKonflik);
        $response->assertRedirect('/peminjaman/2/edit');
        $response->assertSessionHas('error', 'Kode peminjaman sudah digunakan oleh data lain.');

        // Pastikan data record 2 tidak berubah
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('SELECT kode_peminjaman FROM peminjaman_alat WHERE id = 2');
        $stmt->execute();
        $kode = $stmt->fetchColumn();
        $this->assertEquals('PM00000002', $kode);
    }

    /**
     * Skenario 7: DELETE
     * Data terpilih berhasil dihapus melalui form DELETE dengan CSRF.
     */
    public function test_07_delete_berhasil_menghapus_data(): void
    {
        // Buat data sementara untuk dihapus
        $pdo = DB::connection()->getPdo();
        $pdo->exec("INSERT INTO peminjaman_alat (id, kode_peminjaman, nama_peminjam, nomor_identitas, jumlah_alat, alat_id, created_at, updated_at) 
                    VALUES (99, 'PM99999999', 'Hapus Saya', 'MHS-9999999', 1, 1, NOW(), NOW())");

        $response = $this->delete('/peminjaman/99');
        $response->assertRedirect('/peminjaman');
        $response->assertSessionHas('success', 'Data peminjaman alat berhasil dihapus.');

        // Verifikasi data terhapus dari basis data
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM peminjaman_alat WHERE id = 99');
        $stmt->execute();
        $this->assertEquals(0, (int) $stmt->fetchColumn());
    }

    /**
     * Skenario 8: Pencarian
     * Kata kunci dapat mencari kode, nama peminjam, dan nomor identitas.
     */
    public function test_08_pencarian_berdasarkan_kode_nama_dan_nomor_identitas(): void
    {
        // 1. Cari berdasarkan nama
        $resNama = $this->get('/peminjaman?q=Dedi');
        $resNama->assertStatus(200);
        $resNama->assertSee('Dedi Pranata');
        $resNama->assertDontSee('Mira Ananda');

        // 2. Cari berdasarkan kode
        $resKode = $this->get('/peminjaman?q=PM00000003');
        $resKode->assertStatus(200);
        $resKode->assertSee('Mira Ananda');
        $resKode->assertDontSee('Dedi Pranata');

        // 3. Cari berdasarkan nomor identitas
        $resIdentitas = $this->get('/peminjaman?q=MHS-2201001');
        $resIdentitas->assertStatus(200);
        $resIdentitas->assertSee('Rina Kurnia');
        $resIdentitas->assertDontSee('Mira Ananda');
    }

    /**
     * Skenario 9: Filter
     * Data dapat difilter berdasarkan alat.
     */
    public function test_09_filter_berdasarkan_alat(): void
    {
        // Filter alat Proyektor (id = 2)
        $response = $this->get('/peminjaman?alat_id=2');
        $response->assertStatus(200);
        $response->assertSee('Dedi Pranata');
        $response->assertSee('Proyektor');
        $response->assertDontSee('Rina Kurnia'); // Rina meminjam Kamera
        $response->assertDontSee('Mira Ananda'); // Mira meminjam Kamera
    }

    /**
     * Skenario 10: Error handling
     * Kesalahan koneksi atau query dicatat melalui report(), sedangkan pengguna menerima respons aman.
     */
    public function test_10_error_handling_memberikan_respons_aman(): void
    {
        // Buka edit dengan ID yang tidak ada
        $response = $this->get('/peminjaman/99999/edit');
        $response->assertRedirect('/peminjaman');
        $response->assertSessionHas('error', 'Data peminjaman tidak ditemukan.');

        // Delete ID yang tidak ada
        $responseDelete = $this->delete('/peminjaman/99999');
        $responseDelete->assertRedirect('/peminjaman');
        $responseDelete->assertSessionHas('error', 'Data peminjaman tidak ditemukan atau telah dihapus.');
    }
}
