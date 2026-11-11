<?php

/*
|--------------------------------------------------------------------------
| ANGGOTA 4 — Ulasan (seluruh fitur ulasan)
|--------------------------------------------------------------------------
| Pembeli menulis/mengedit ulasan, admin memoderasi (setujui/tolak).
| Ulasan baru selalu menunggu moderasi; ulasan yang disetujui tampil di
| halaman produk (tampilannya ada di catalog/show.blade.php, milik Anggota 2).
*/

use App\Http\Controllers\Admin;
use App\Http\Controllers\Pembeli;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::middleware('role:pembeli')->prefix('pembeli')->name('pembeli.')->group(function () {
        Route::get('/ulasan', [Pembeli\ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/pesanan/{order}/ulasan/{product}', [Pembeli\ReviewController::class, 'create'])->name('reviews.create');
        Route::post('/pesanan/{order}/ulasan/{product}', [Pembeli\ReviewController::class, 'store'])->name('reviews.store');
        Route::get('/ulasan/{review}/edit', [Pembeli\ReviewController::class, 'edit'])->name('reviews.edit');
        Route::put('/ulasan/{review}', [Pembeli\ReviewController::class, 'update'])->name('reviews.update');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/ulasan', [Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/ulasan/{review}/setujui', [Admin\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::delete('/ulasan/{review}', [Admin\ReviewController::class, 'reject'])->name('reviews.reject');
    });
});
