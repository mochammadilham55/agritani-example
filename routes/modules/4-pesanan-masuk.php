<?php

/*
|--------------------------------------------------------------------------
| ANGGOTA 4 — Pesanan Masuk, Pembayaran & Pengiriman (sisi Petani)
|--------------------------------------------------------------------------
| Dashboard petani, pesanan masuk (konfirmasi/tolak), konfirmasi bukti
| pembayaran, dan pengisian resi pengiriman.
*/

use App\Http\Controllers\Petani;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:petani'])->prefix('petani')->name('petani.')->group(function () {
    Route::get('/dashboard', Petani\DashboardController::class)->name('dashboard');

    Route::get('/pesanan', [Petani\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{id}', [Petani\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/pesanan/{id}/konfirmasi', [Petani\OrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/pesanan/{id}/tolak', [Petani\OrderController::class, 'reject'])->name('orders.reject');

    Route::get('/pembayaran', [Petani\PaymentController::class, 'index'])->name('payments.index');
    Route::patch('/pembayaran/{id}/konfirmasi', [Petani\PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::patch('/pembayaran/{id}/tolak', [Petani\PaymentController::class, 'reject'])->name('payments.reject');

    Route::get('/laporan', [Petani\ReportController::class, 'index'])->name('reports.index');

    Route::get('/pengiriman', [Petani\ShipmentController::class, 'index'])->name('shipments.index');
    Route::put('/pengiriman/{id}', [Petani\ShipmentController::class, 'update'])->name('shipments.update');
});
