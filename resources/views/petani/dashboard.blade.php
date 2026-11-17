@extends('layouts.app')

@section('title', 'Dashboard Petani')
@section('heading', 'Dashboard')

@section('content')
    <div class="row g-3">
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Produk" :value="$productCount" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Terjual" :value="number_format($soldQty, 0, ',', '.')" hint="Dari pesanan selesai" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Pendapatan" :value="'Rp '.number_format($revenue, 0, ',', '.')" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Pesanan Baru" :value="$newOrders" hint="Menunggu konfirmasi" /></div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <h2 class="h6 fw-bold text-brand-dark mb-3">Pesanan Terbaru</h2>
            <x-orders-table :orders="$recentOrders" />
        </div>
        <div class="col-lg-4">
            <h2 class="h6 fw-bold text-brand-dark mb-3">Stok Menipis</h2>
            <div class="card">
                <ul class="list-group list-group-flush small">
                    @forelse ($lowStock as $p)
                        <li class="list-group-item d-flex align-items-center justify-content-between px-3 py-2">
                            <span class="fw-medium">{{ $p->name }}</span>
                            <span class="badge rounded-pill" style="background: #FCEFD6; color: #7A4B00;">{{ $p->stock }} {{ $p->unit }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-secondary py-4">Semua stok aman.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
