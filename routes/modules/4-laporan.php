<?php

/*
|--------------------------------------------------------------------------
| ANGGOTA 4 — Laporan & Dashboard
|--------------------------------------------------------------------------
| Dashboard petani/admin dan laporan penjualan (filter tanggal, export).
| (Dashboard pembeli ada di 2-pesanan.php.)
*/

use App\Http\Controllers\Admin;
use App\Http\Controllers\Petani;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::middleware('role:petani')->prefix('petani')->name('petani.')->group(function () {
        Route::get('/dashboard', Petani\DashboardController::class)->name('dashboard');

        Route::get('/laporan', [Petani\ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/export', [Petani\ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

        Route::get('/laporan', [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/export', [Admin\ReportController::class, 'export'])->name('reports.export');
    });
});
