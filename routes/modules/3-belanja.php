<?php

/*
|--------------------------------------------------------------------------
| ANGGOTA 3 — Belanja (Keranjang, Checkout, Pesanan Pembeli)
|--------------------------------------------------------------------------
| Semua fitur pembeli: dashboard, keranjang, checkout, daftar pesanan,
| upload bukti transfer, batalkan pesanan, dan konfirmasi pesanan diterima.
*/

use App\Http\Controllers\Pembeli;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:pembeli'])->prefix('pembeli')->name('pembeli.')->group(function () {
    Route::get('/dashboard', Pembeli\DashboardController::class)->name('dashboard');

    Route::get('/keranjang', [Pembeli\CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang/{product:slug}', [Pembeli\CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{cart}', [Pembeli\CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{cart}', [Pembeli\CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [Pembeli\CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [Pembeli\CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [Pembeli\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [Pembeli\OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/bukti', [Pembeli\OrderController::class, 'uploadProof'])->name('orders.proof');
    Route::patch('/pesanan/{order}/batal', [Pembeli\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('/pesanan/{order}/selesai', [Pembeli\OrderController::class, 'complete'])->name('orders.complete');
});
