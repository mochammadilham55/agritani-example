<?php

/*
|--------------------------------------------------------------------------
| ANGGOTA 3 — Pengiriman & Pembayaran
|--------------------------------------------------------------------------
| Konfirmasi/penolakan bukti transfer dan pengisian resi oleh petani.
| (Route upload bukti oleh pembeli ada di 2-pesanan.php.)
*/

use App\Http\Controllers\Petani;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:petani'])->prefix('petani')->name('petani.')->group(function () {
    Route::get('/pembayaran', [Petani\PaymentController::class, 'index'])->name('payments.index');
    Route::patch('/pembayaran/{id}/konfirmasi', [Petani\PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::patch('/pembayaran/{id}/tolak', [Petani\PaymentController::class, 'reject'])->name('payments.reject');

    Route::get('/pengiriman', [Petani\ShipmentController::class, 'index'])->name('shipments.index');
    Route::put('/pengiriman/{id}', [Petani\ShipmentController::class, 'update'])->name('shipments.update');
    Route::patch('/pengiriman/{id}/tiba', [Petani\ShipmentController::class, 'arrived'])->name('shipments.arrived');
});
