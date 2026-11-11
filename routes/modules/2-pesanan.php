<?php

/*
|--------------------------------------------------------------------------
| ANGGOTA 2 — Manajemen Pesanan
|--------------------------------------------------------------------------
| Katalog publik, keranjang, checkout, pesanan pembeli, pesanan masuk
| petani, dan daftar pesanan admin.
|
| Catatan: route 'pembeli.orders.proof' (upload bukti bayar) memakai
| Pembeli\OrderController milik modul ini, tetapi logikanya milik Anggota 3.
*/

use App\Http\Controllers\Admin;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Pembeli;
use App\Http\Controllers\Petani;
use Illuminate\Support\Facades\Route;

// Publik
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/produk/{product:slug}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    // Pembeli
    Route::middleware('role:pembeli')->prefix('pembeli')->name('pembeli.')->group(function () {
        Route::get('/dashboard', Pembeli\DashboardController::class)->name('dashboard');

        Route::get('/checkout', [Pembeli\CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [Pembeli\CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('/pesanan', [Pembeli\OrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{order}', [Pembeli\OrderController::class, 'show'])->name('orders.show');
        Route::post('/pesanan/{order}/bukti', [Pembeli\OrderController::class, 'uploadProof'])->name('orders.proof');
        Route::patch('/pesanan/{order}/batal', [Pembeli\OrderController::class, 'cancel'])->name('orders.cancel');
        Route::patch('/pesanan/{order}/selesai', [Pembeli\OrderController::class, 'complete'])->name('orders.complete');

        Route::post('/keranjang/{product:slug}', [Pembeli\CartController::class, 'store'])->name('cart.store');
        Route::get('/keranjang', [Pembeli\CartController::class, 'index'])->name('cart.index');
        Route::patch('/keranjang/{cart}', [Pembeli\CartController::class, 'update'])->name('cart.update');
        Route::delete('/keranjang/{cart}', [Pembeli\CartController::class, 'destroy'])->name('cart.destroy');
    });

    // Petani: pesanan masuk
    Route::middleware('role:petani')->prefix('petani')->name('petani.')->group(function () {
        Route::get('/pesanan', [Petani\OrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{id}', [Petani\OrderController::class, 'show'])->name('orders.show');
        Route::patch('/pesanan/{id}/konfirmasi', [Petani\OrderController::class, 'confirm'])->name('orders.confirm');
        Route::patch('/pesanan/{id}/tolak', [Petani\OrderController::class, 'reject'])->name('orders.reject');
    });

    // Admin: semua pesanan
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/pesanan', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    });
});
