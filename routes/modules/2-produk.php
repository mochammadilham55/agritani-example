<?php

/*
|--------------------------------------------------------------------------
| ANGGOTA 2 — Produk & Katalog
|--------------------------------------------------------------------------
| Katalog publik, produk milik petani (tambah/ubah/aktif-nonaktif),
| dan kategori produk (admin).
*/

use App\Http\Controllers\Admin;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Petani;
use Illuminate\Support\Facades\Route;

// Publik
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/produk/{product:slug}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    // Petani: kelola produk sendiri.
    Route::middleware('role:petani')->prefix('petani')->name('petani.')->group(function () {
        Route::resource('produk', Petani\ProductController::class)
            ->parameters(['produk' => 'product'])->except(['show', 'destroy'])->names('products');
        Route::patch('/produk/{product}/toggle', [Petani\ProductController::class, 'toggle'])->name('products.toggle');
    });

    // Admin: kelola kategori.
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('kategori', Admin\CategoryController::class)
            ->parameters(['kategori' => 'category'])->except(['show', 'create'])->names('categories');
    });
});
