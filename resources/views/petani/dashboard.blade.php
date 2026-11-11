@extends('layouts.app')

@section('title', 'Dashboard Petani')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Produk" :value="$productCount" />
        <x-stat-card label="Terjual" :value="number_format($soldQty, 0, ',', '.')" hint="Dari pesanan selesai" />
        <x-stat-card label="Pendapatan" :value="'Rp '.number_format($revenue, 0, ',', '.')" />
        <x-stat-card label="Rating" :value="$rating ? $rating.' / 5' : '-'" hint="Ulasan yang disetujui" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="mb-3 font-bold text-brand-dark">Pesanan Terbaru</h2>
            <x-orders-table :orders="$recentOrders" />
        </div>
        <div>
            <h2 class="mb-3 font-bold text-brand-dark">Stok Menipis</h2>
            <div class="divide-y divide-line rounded-xl border border-line bg-white">
                @forelse ($lowStock as $p)
                    <div class="flex items-center justify-between px-4 py-3 text-sm">
                        <span class="font-medium">{{ $p->name }}</span>
                        <span class="rounded-full bg-[#FCEFD6] px-2.5 py-0.5 text-xs font-semibold text-[#7A4B00]">
                            {{ $p->stock }} {{ $p->unit }}
                        </span>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-gray-400">Semua stok aman.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
