<?php

use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - UTS Praktikum Pemrograman Web (Paket 2 Tipe B)
| Sistem Peminjaman Alat
| Mahasiswa: Ridho Ramadani (2401020121)
|--------------------------------------------------------------------------
*/

// Redirect root ke route utama /peminjaman
Route::get('/', function () {
    return redirect()->route('peminjaman.index');
});

// Route Peminjaman Alat
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
Route::get('/peminjaman/{id}/edit', [PeminjamanController::class, 'edit'])->name('peminjaman.edit');
Route::put('/peminjaman/{id}', [PeminjamanController::class, 'update'])->name('peminjaman.update');
Route::delete('/peminjaman/{id}', [PeminjamanController::class, 'destroy'])->name('peminjaman.destroy');
