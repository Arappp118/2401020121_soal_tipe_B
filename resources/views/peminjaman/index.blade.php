<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Peminjaman Alat</title>
</head>
<body>
    <h1>Sistem Peminjaman Alat</h1>
    <p>Laboratorium - Praktikum Pemrograman Web (Paket 2 Tipe B)</p>
    <hr>

    {{-- Feedback Notifikasi Operasi --}}
    @if (session('success'))
        <p><strong>Berhasil:</strong> {{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p><strong>Gagal:</strong> {{ session('error') }}</p>
    @endif

    {{-- Pesan Kesalahan Validasi Server-Side --}}
    @if ($errors->any())
        <div>
            <p><strong>Terjadi kesalahan input:</strong></p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. Form Tambah Peminjaman --}}
    <h2>Tambah Peminjaman Alat</h2>
    <form action="{{ route('peminjaman.store') }}" method="POST">
        @csrf
        <p>
            <label for="kode_peminjaman">Kode Peminjaman (10 Karakter Alfanumerik):</label><br>
            <input type="text" id="kode_peminjaman" name="kode_peminjaman" maxlength="10" value="{{ old('kode_peminjaman') }}" required>
        </p>
        <p>
            <label for="nama_peminjam">Nama Peminjam (3 - 100 Karakter):</label><br>
            <input type="text" id="nama_peminjam" name="nama_peminjam" maxlength="100" value="{{ old('nama_peminjam') }}" required>
        </p>
        <p>
            <label for="nomor_identitas">Nomor Identitas (5 - 30 Karakter):</label><br>
            <input type="text" id="nomor_identitas" name="nomor_identitas" maxlength="30" value="{{ old('nomor_identitas') }}" required>
        </p>
        <p>
            <label for="jumlah_alat">Jumlah Alat (1 - 10):</label><br>
            <input type="number" id="jumlah_alat" name="jumlah_alat" min="1" max="10" value="{{ old('jumlah_alat') }}" required>
        </p>
        <p>
            <label for="alat_id">Pilih Alat:</label><br>
            <select id="alat_id" name="alat_id" required>
                <option value="">-- Pilih Alat --</option>
                @foreach ($daftarAlat as $alat)
                    <option value="{{ $alat['id'] }}" @selected((string) old('alat_id') === (string) $alat['id'])>
                        {{ $alat['nama_alat'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <p>
            <button type="submit">Simpan Peminjaman</button>
        </p>
    </form>
    <hr>

    {{-- 2. Form Pencarian dan Filter --}}
    <h2>Pencarian dan Filter</h2>
    <form action="{{ route('peminjaman.index') }}" method="GET">
        <p>
            <label for="q">Pencarian (Kode / Nama Peminjam / No. Identitas):</label><br>
            <input type="text" id="q" name="q" value="{{ $kataKunci }}" placeholder="Ketik kata kunci...">
        </p>
        <p>
            <label for="filter_alat_id">Filter Berdasarkan Alat:</label><br>
            <select id="filter_alat_id" name="alat_id">
                <option value="">-- Semua Alat --</option>
                @foreach ($daftarAlat as $alat)
                    <option value="{{ $alat['id'] }}" @selected((string) $alatId === (string) $alat['id'])>
                        {{ $alat['nama_alat'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <p>
            <button type="submit">Cari & Filter</button>
            <a href="{{ route('peminjaman.index') }}">Reset</a>
        </p>
    </form>
    <hr>

    {{-- 3. Tabel Data Peminjaman --}}
    <h2>Daftar Peminjaman Alat</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode Peminjaman</th>
                <th>Nama Peminjam</th>
                <th>Nomor Identitas</th>
                <th>Nama Alat</th>
                <th>Jumlah Alat</th>
                <th>Status Jumlah</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarPeminjaman as $peminjaman)
                <tr>
                    <td>{{ $peminjaman['id'] }}</td>
                    <td>{{ $peminjaman['kode_peminjaman'] }}</td>
                    <td>{{ $peminjaman['nama_peminjam'] }}</td>
                    <td>{{ $peminjaman['nomor_identitas'] }}</td>
                    <td>{{ $peminjaman['nama_alat'] }}</td>
                    <td>{{ $peminjaman['jumlah_alat'] }}</td>
                    <td>{{ statusJumlah($peminjaman['jumlah_alat']) }}</td>
                    <td>
                        <a href="{{ route('peminjaman.edit', $peminjaman['id']) }}">Edit</a>
                        <form action="{{ route('peminjaman.destroy', $peminjaman['id']) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Data peminjaman tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
