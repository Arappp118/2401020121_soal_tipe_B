<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDO;

class PeminjamanController extends Controller
{
    /**
     * Menampilkan daftar peminjaman, form tambah, form pencarian, dan filter.
     */
    public function index(Request $request)
    {
        $kataKunci = trim(strip_tags((string) $request->query('q', '')));
        $alatId = trim(strip_tags((string) $request->query('alat_id', '')));

        try {
            $pdo = DB::connection()->getPdo();

            // Ambil daftar master alat untuk pilihan form dan filter
            $stmtMasterAlat = $pdo->prepare('SELECT id, nama_alat FROM alat ORDER BY id ASC');
            $stmtMasterAlat->execute();
            $daftarAlat = $stmtMasterAlat->fetchAll(PDO::FETCH_ASSOC);

            // Query data peminjaman dengan JOIN ke tabel alat
            $sql = 'SELECT p.id, p.kode_peminjaman, p.nama_peminjam, p.nomor_identitas, 
                           p.jumlah_alat, p.alat_id, p.created_at, p.updated_at,
                           a.nama_alat
                    FROM peminjaman_alat AS p
                    JOIN alat AS a ON a.id = p.alat_id
                    WHERE 1 = 1';
            $params = [];

            // Pencarian 3 kolom: kode_peminjaman, nama_peminjam, nomor_identitas
            if ($kataKunci !== '') {
                $sql .= ' AND (p.kode_peminjaman LIKE :kunci_kode 
                            OR p.nama_peminjam LIKE :kunci_nama 
                            OR p.nomor_identitas LIKE :kunci_identitas)';
                $nilaiPencarian = '%' . $kataKunci . '%';
                $params['kunci_kode'] = $nilaiPencarian;
                $params['kunci_nama'] = $nilaiPencarian;
                $params['kunci_identitas'] = $nilaiPencarian;
            }

            // Filter berdasarkan master alat
            if ($alatId !== '') {
                $sql .= ' AND p.alat_id = :filter_alat_id';
                $params['filter_alat_id'] = (int) $alatId;
            }

            $sql .= ' ORDER BY p.id ASC';

            $stmtPeminjaman = $pdo->prepare($sql);
            $stmtPeminjaman->execute($params);
            $daftarPeminjaman = $stmtPeminjaman->fetchAll(PDO::FETCH_ASSOC);

            return view('peminjaman.index', compact(
                'daftarPeminjaman',
                'daftarAlat',
                'kataKunci',
                'alatId'
            ));
        } catch (\Throwable $e) {
            report($e);
            return response(
                'Terjadi kesalahan saat memuat data peminjaman. Silakan coba beberapa saat lagi.',
                500
            );
        }
    }

    /**
     * Menyimpan data peminjaman baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Sanitasi input menggunakan trim() dan strip_tags() sebelum validasi
        $rawInput = $request->all();
        $cleanInput = [];
        foreach ($rawInput as $key => $value) {
            if (is_string($value)) {
                $cleanInput[$key] = trim(strip_tags($value));
            } else {
                $cleanInput[$key] = $value;
            }
        }

        // 2. Validasi server-side
        $validator = Validator::make($cleanInput, [
            'kode_peminjaman' => [
                'required',
                'string',
                'size:10',
                'regex:/^[A-Za-z0-9]{10}$/',
            ],
            'nama_peminjam' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'nomor_identitas' => [
                'required',
                'string',
                'min:5',
                'max:30',
            ],
            'jumlah_alat' => [
                'required',
                'integer',
                'min:1',
                'max:10',
            ],
            'alat_id' => [
                'required',
                'integer',
            ],
        ], [
            'kode_peminjaman.required' => 'Kode peminjaman wajib diisi.',
            'kode_peminjaman.size' => 'Kode peminjaman harus tepat 10 karakter.',
            'kode_peminjaman.regex' => 'Kode peminjaman harus tepat 10 karakter alfanumerik tanpa spasi.',
            'nama_peminjam.required' => 'Nama peminjam wajib diisi.',
            'nama_peminjam.min' => 'Nama peminjam minimal 3 karakter.',
            'nama_peminjam.max' => 'Nama peminjam maksimal 100 karakter.',
            'nomor_identitas.required' => 'Nomor identitas wajib diisi.',
            'nomor_identitas.min' => 'Nomor identitas minimal 5 karakter.',
            'nomor_identitas.max' => 'Nomor identitas maksimal 30 karakter.',
            'jumlah_alat.required' => 'Jumlah alat wajib diisi.',
            'jumlah_alat.integer' => 'Jumlah alat harus berupa bilangan bulat.',
            'jumlah_alat.min' => 'Jumlah alat minimal 1.',
            'jumlah_alat.max' => 'Jumlah alat maksimal 10.',
            'alat_id.required' => 'Alat wajib dipilih.',
            'alat_id.integer' => 'Pilihan alat tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('peminjaman.index')
                ->withErrors($validator)
                ->withInput($cleanInput);
        }

        try {
            $pdo = DB::connection()->getPdo();

            // Verifikasi bahwa alat_id tersedia pada tabel master alat
            $stmtCekAlat = $pdo->prepare('SELECT COUNT(*) FROM alat WHERE id = :alat_id');
            $stmtCekAlat->execute(['alat_id' => (int) $cleanInput['alat_id']]);
            if ((int) $stmtCekAlat->fetchColumn() === 0) {
                return redirect()->route('peminjaman.index')
                    ->withInput($cleanInput)
                    ->with('error', 'Alat yang dipilih tidak tersedia pada data master.');
            }

            // Pengecekan duplikat kode_peminjaman atau nomor_identitas
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
            $existing = $stmtCekDuplikat->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['kode_peminjaman'] === $cleanInput['kode_peminjaman']) {
                    return redirect()->route('peminjaman.index')
                        ->withInput($cleanInput)
                        ->with('error', 'Kode peminjaman sudah digunakan.');
                }
                if ($existing['nomor_identitas'] === $cleanInput['nomor_identitas']) {
                    return redirect()->route('peminjaman.index')
                        ->withInput($cleanInput)
                        ->with('error', 'Nomor identitas sudah digunakan.');
                }
            }

            // Simpan data menggunakan prepared statement
            $stmtInsert = $pdo->prepare(
                'INSERT INTO peminjaman_alat 
                 (kode_peminjaman, nama_peminjam, nomor_identitas, jumlah_alat, alat_id, created_at, updated_at) 
                 VALUES (:kode_peminjaman, :nama_peminjam, :nomor_identitas, :jumlah_alat, :alat_id, NOW(), NOW())'
            );
            $stmtInsert->execute([
                'kode_peminjaman' => $cleanInput['kode_peminjaman'],
                'nama_peminjam' => $cleanInput['nama_peminjam'],
                'nomor_identitas' => $cleanInput['nomor_identitas'],
                'jumlah_alat' => (int) $cleanInput['jumlah_alat'],
                'alat_id' => (int) $cleanInput['alat_id'],
            ]);

            return redirect()->route('peminjaman.index')
                ->with('success', 'Data peminjaman alat berhasil ditambahkan.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('peminjaman.index')
                ->withInput($cleanInput)
                ->with('error', 'Terjadi kesalahan sistem saat menyimpan data peminjaman.');
        }
    }

    /**
     * Menampilkan form edit peminjaman alat.
     */
    public function edit(int $id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            // Ambil data peminjaman berdasarkan ID
            $stmtPeminjaman = $pdo->prepare(
                'SELECT id, kode_peminjaman, nama_peminjam, nomor_identitas, jumlah_alat, alat_id 
                 FROM peminjaman_alat 
                 WHERE id = :id'
            );
            $stmtPeminjaman->execute(['id' => $id]);
            $peminjaman = $stmtPeminjaman->fetch(PDO::FETCH_ASSOC);

            if (!$peminjaman) {
                return redirect()->route('peminjaman.index')
                    ->with('error', 'Data peminjaman tidak ditemukan.');
            }

            // Ambil master alat
            $stmtAlat = $pdo->prepare('SELECT id, nama_alat FROM alat ORDER BY id ASC');
            $stmtAlat->execute();
            $daftarAlat = $stmtAlat->fetchAll(PDO::FETCH_ASSOC);

            return view('peminjaman.edit', compact('peminjaman', 'daftarAlat'));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('peminjaman.index')
                ->with('error', 'Terjadi kesalahan sistem saat mengambil data peminjaman.');
        }
    }

    /**
     * Memperbarui data peminjaman alat.
     */
    public function update(Request $request, int $id)
    {
        // 1. Sanitasi input menggunakan trim() dan strip_tags()
        $rawInput = $request->all();
        $cleanInput = [];
        foreach ($rawInput as $key => $value) {
            if (is_string($value)) {
                $cleanInput[$key] = trim(strip_tags($value));
            } else {
                $cleanInput[$key] = $value;
            }
        }

        // 2. Validasi server-side
        $validator = Validator::make($cleanInput, [
            'kode_peminjaman' => [
                'required',
                'string',
                'size:10',
                'regex:/^[A-Za-z0-9]{10}$/',
            ],
            'nama_peminjam' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'nomor_identitas' => [
                'required',
                'string',
                'min:5',
                'max:30',
            ],
            'jumlah_alat' => [
                'required',
                'integer',
                'min:1',
                'max:10',
            ],
            'alat_id' => [
                'required',
                'integer',
            ],
        ], [
            'kode_peminjaman.required' => 'Kode peminjaman wajib diisi.',
            'kode_peminjaman.size' => 'Kode peminjaman harus tepat 10 karakter.',
            'kode_peminjaman.regex' => 'Kode peminjaman harus tepat 10 karakter alfanumerik tanpa spasi.',
            'nama_peminjam.required' => 'Nama peminjam wajib diisi.',
            'nama_peminjam.min' => 'Nama peminjam minimal 3 karakter.',
            'nama_peminjam.max' => 'Nama peminjam maksimal 100 karakter.',
            'nomor_identitas.required' => 'Nomor identitas wajib diisi.',
            'nomor_identitas.min' => 'Nomor identitas minimal 5 karakter.',
            'nomor_identitas.max' => 'Nomor identitas maksimal 30 karakter.',
            'jumlah_alat.required' => 'Jumlah alat wajib diisi.',
            'jumlah_alat.integer' => 'Jumlah alat harus berupa bilangan bulat.',
            'jumlah_alat.min' => 'Jumlah alat minimal 1.',
            'jumlah_alat.max' => 'Jumlah alat maksimal 10.',
            'alat_id.required' => 'Alat wajib dipilih.',
            'alat_id.integer' => 'Pilihan alat tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('peminjaman.edit', $id)
                ->withErrors($validator)
                ->withInput($cleanInput);
        }

        try {
            $pdo = DB::connection()->getPdo();

            // Pastikan data record yang diedit ada
            $stmtCekAda = $pdo->prepare('SELECT id FROM peminjaman_alat WHERE id = :id');
            $stmtCekAda->execute(['id' => $id]);
            if (!$stmtCekAda->fetch()) {
                return redirect()->route('peminjaman.index')
                    ->with('error', 'Data peminjaman tidak ditemukan.');
            }

            // Pastikan alat_id ada di tabel master
            $stmtCekAlat = $pdo->prepare('SELECT COUNT(*) FROM alat WHERE id = :alat_id');
            $stmtCekAlat->execute(['alat_id' => (int) $cleanInput['alat_id']]);
            if ((int) $stmtCekAlat->fetchColumn() === 0) {
                return redirect()->route('peminjaman.edit', $id)
                    ->withInput($cleanInput)
                    ->with('error', 'Alat yang dipilih tidak tersedia pada data master.');
            }

            // Pengecekan konflik keunikan kode_peminjaman atau nomor_identitas dengan record LAIN (id <> :id)
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
            $konflik = $stmtCekKonflik->fetch(PDO::FETCH_ASSOC);

            if ($konflik) {
                if ($konflik['kode_peminjaman'] === $cleanInput['kode_peminjaman']) {
                    return redirect()->route('peminjaman.edit', $id)
                        ->withInput($cleanInput)
                        ->with('error', 'Kode peminjaman sudah digunakan oleh data lain.');
                }
                if ($konflik['nomor_identitas'] === $cleanInput['nomor_identitas']) {
                    return redirect()->route('peminjaman.edit', $id)
                        ->withInput($cleanInput)
                        ->with('error', 'Nomor identitas sudah digunakan oleh peminjam lain.');
                }
            }

            // Update menggunakan prepared statement
            $stmtUpdate = $pdo->prepare(
                'UPDATE peminjaman_alat 
                 SET kode_peminjaman = :kode_peminjaman,
                     nama_peminjam = :nama_peminjam,
                     nomor_identitas = :nomor_identitas,
                     jumlah_alat = :jumlah_alat,
                     alat_id = :alat_id,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmtUpdate->execute([
                'kode_peminjaman' => $cleanInput['kode_peminjaman'],
                'nama_peminjam' => $cleanInput['nama_peminjam'],
                'nomor_identitas' => $cleanInput['nomor_identitas'],
                'jumlah_alat' => (int) $cleanInput['jumlah_alat'],
                'alat_id' => (int) $cleanInput['alat_id'],
                'id' => $id,
            ]);

            return redirect()->route('peminjaman.index')
                ->with('success', 'Data peminjaman alat berhasil diperbarui.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('peminjaman.edit', $id)
                ->withInput($cleanInput)
                ->with('error', 'Terjadi kesalahan sistem saat memperbarui data peminjaman.');
        }
    }

    /**
     * Menghapus data peminjaman alat.
     */
    public function destroy(int $id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            // Eksekusi penghapusan dengan prepared statement
            $stmtDelete = $pdo->prepare('DELETE FROM peminjaman_alat WHERE id = :id');
            $stmtDelete->execute(['id' => $id]);

            if ($stmtDelete->rowCount() === 0) {
                return redirect()->route('peminjaman.index')
                    ->with('error', 'Data peminjaman tidak ditemukan atau telah dihapus.');
            }

            return redirect()->route('peminjaman.index')
                ->with('success', 'Data peminjaman alat berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('peminjaman.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus data peminjaman.');
        }
    }
}
