@extends('layouts.app')

@section('title', 'Dashboard Petani')
@section('heading', 'Dashboard')

@section('content')
    {{-- Keputusan utama petani di halaman ini: pesanan mana yang harus diproses sekarang. --}}
    @if ($newOrders > 0)
        <div class="card border-warning mb-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <p class="fs-5 fw-bold text-brand-dark mb-0">{{ $newOrders }} pesanan menunggu konfirmasi Anda</p>
                    <p class="small text-secondary mb-0">Konfirmasi atau tolak agar pembeli tahu pesanannya diproses.</p>
                </div>
                <a href="{{ route('petani.orders.index', ['status' => 'pending']) }}" class="btn btn-primary fw-semibold">Proses pesanan</a>
            </div>
        </div>
    @else
        <p class="text-secondary mb-4">Tidak ada pesanan baru yang menunggu konfirmasi.</p>
    @endif

    <dl class="d-flex flex-wrap gap-4 small mb-4">
        <div><dt class="fw-normal text-secondary">Produk</dt><dd class="fs-5 fw-bold text-brand-dark mb-0">{{ $productCount }}</dd></div>
        <div><dt class="fw-normal text-secondary">Terjual (pesanan selesai)</dt><dd class="fs-5 fw-bold text-brand-dark mb-0">{{ number_format($soldQty, 0, ',', '.') }}</dd></div>
        <div><dt class="fw-normal text-secondary">Pendapatan (pesanan selesai)</dt><dd class="fs-5 fw-bold text-brand-dark mb-0">Rp {{ number_format($revenue, 0, ',', '.') }}</dd></div>
    </dl>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <h2 class="h6 fw-bold text-brand-dark mb-3">Pesanan Terbaru</h2>
            <x-orders-table :orders="$recentOrders" />
        </div>
        <div class="col-lg-4">
            <h2 class="h6 fw-bold text-brand-dark mb-1">Stok Menipis</h2>
            <p class="small text-secondary mb-3">Klik produk untuk menambah stok.</p>
            <div class="card">
                <ul class="list-group list-group-flush small">
                    @forelse ($lowStock as $p)
                        <li class="list-group-item d-flex align-items-center justify-content-between px-3 py-2">
                            <a href="{{ route('petani.products.edit', $p) }}" class="fw-medium text-decoration-none">{{ $p->name }}</a>
                            <span class="badge rounded-pill" style="background: #FCEFD6; color: #7A4B00;">{{ $p->stock }} {{ $p->unit }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-secondary py-4">Tidak ada produk dengan stok menipis.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
