@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-gray-600">Halo, <strong>{{ auth()->user()->name }}</strong>! Berikut ringkasan pesanan Anda.</p>
        <a href="{{ route('home') }}" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">🛒 Belanja Sekarang</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Pesanan Aktif" :value="$activeOrders" hint="Pending, diproses, dikirim" />
        <x-stat-card label="Pesanan Selesai" :value="$completedOrders" />
        <x-stat-card label="Total Belanja" :value="'Rp '.number_format($totalSpent, 0, ',', '.')" hint="Dari pesanan selesai" />
    </div>

    <h2 class="mb-3 mt-8 font-bold text-brand-dark">Riwayat Terbaru</h2>
    <x-orders-table :orders="$recentOrders" />
@endsection
