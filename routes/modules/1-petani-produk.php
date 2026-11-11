<?php

/*
|--------------------------------------------------------------------------
| ANGGOTA 1 — Manajemen Petani & Produk
|--------------------------------------------------------------------------
| Profil pengguna, produk milik petani, serta sisi admin untuk
| daftar petani, daftar produk, dan kategori.
*/

use App\Http\Controllers\Admin;
use App\Http\Controllers\Petani;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Profil: tersedia untuk semua role.
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'password'])->name('profile.password');

    // Petani: kelola produk sendiri.
    Route::middleware('role:petani')->prefix('petani')->name('petani.')->group(function () {
        Route::resource('produk', Petani\ProductController::class)
            ->parameters(['produk' => 'product'])->except(['show', 'destroy'])->names('products');
        Route::patch('/produk/{product}/toggle', [Petani\ProductController::class, 'toggle'])->name('products.toggle');
    });

    // Admin: verifikasi petani, moderasi produk, kategori.
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/petani', [Admin\FarmerController::class, 'index'])->name('farmers.index');
        Route::patch('/petani/{user}/toggle', [Admin\FarmerController::class, 'toggle'])->name('farmers.toggle');

        Route::get('/produk', [Admin\ProductController::class, 'index'])->name('products.index');
        Route::patch('/produk/{product:id}/toggle', [Admin\ProductController::class, 'toggle'])->name('products.toggle');

        Route::resource('kategori', Admin\CategoryController::class)
            ->parameters(['kategori' => 'category'])->except(['show', 'create'])->names('categories');
    });
});
