<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Peminjaman Alat</title>
</head>
<body>
    <h1>Edit Data Peminjaman Alat</h1>
    <p><a href="{{ route('peminjaman.index') }}">&laquo; Kembali ke Daftar Peminjaman</a></p>
    <hr>

    {{-- Feedback Notifikasi --}}
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

    <form action="{{ route('peminjaman.update', $peminjaman['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label for="kode_peminjaman">Kode Peminjaman (10 Karakter Alfanumerik):</label><br>
            <input type="text" id="kode_peminjaman" name="kode_peminjaman" maxlength="10" 
                   value="{{ old('kode_peminjaman', $peminjaman['kode_peminjaman']) }}" required>
        </p>
        <p>
            <label for="nama_peminjam">Nama Peminjam (3 - 100 Karakter):</label><br>
            <input type="text" id="nama_peminjam" name="nama_peminjam" maxlength="100" 
                   value="{{ old('nama_peminjam', $peminjaman['nama_peminjam']) }}" required>
        </p>
        <p>
            <label for="nomor_identitas">Nomor Identitas (5 - 30 Karakter):</label><br>
            <input type="text" id="nomor_identitas" name="nomor_identitas" maxlength="30" 
                   value="{{ old('nomor_identitas', $peminjaman['nomor_identitas']) }}" required>
        </p>
        <p>
            <label for="jumlah_alat">Jumlah Alat (1 - 10):</label><br>
            <input type="number" id="jumlah_alat" name="jumlah_alat" min="1" max="10" 
                   value="{{ old('jumlah_alat', $peminjaman['jumlah_alat']) }}" required>
        </p>
        <p>
            <label for="alat_id">Pilih Alat:</label><br>
            <select id="alat_id" name="alat_id" required>
                <option value="">-- Pilih Alat --</option>
                @foreach ($daftarAlat as $alat)
                    <option value="{{ $alat['id'] }}" 
                        @selected((string) old('alat_id', $peminjaman['alat_id']) === (string) $alat['id'])>
                        {{ $alat['nama_alat'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <p>
            <button type="submit">Perbarui Peminjaman</button>
            <a href="{{ route('peminjaman.index') }}">Batal</a>
        </p>
    </form>
</body>
</html>
