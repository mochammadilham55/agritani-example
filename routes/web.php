<?php

/*
|--------------------------------------------------------------------------
| Routes inti (login, register, logout, pengalihan dashboard)
|--------------------------------------------------------------------------
| Route fitur dipecah per anggota di routes/modules/. Ubah file inti ini
| hanya setelah disepakati bersama (lihat PEMBAGIAN_TUGAS.md).
*/

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Arahkan ke dashboard sesuai role.
Route::get('/dashboard', fn () => redirect()->route(auth()->user()->role.'.dashboard'))
    ->middleware('auth')->name('dashboard');

// Route per modul/anggota.
require __DIR__.'/modules/1-petani-produk.php';
require __DIR__.'/modules/2-pesanan.php';
require __DIR__.'/modules/3-pembayaran-pengiriman.php';
require __DIR__.'/modules/4-laporan.php';
require __DIR__.'/modules/4-ulasan.php';
