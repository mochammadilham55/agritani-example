@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <p class="text-secondary mb-0">Halo, <strong>{{ auth()->user()->name }}</strong>! Berikut ringkasan pesanan Anda.</p>
        <a href="{{ route('home') }}" class="btn btn-primary btn-sm fw-semibold">Belanja Sekarang</a>
    </div>

    <div class="row g-3">
        <div class="col-sm-4"><x-stat-card label="Pesanan Aktif" :value="$activeOrders" hint="Pending, diproses, dikirim" /></div>
        <div class="col-sm-4"><x-stat-card label="Pesanan Selesai" :value="$completedOrders" /></div>
        <div class="col-sm-4"><x-stat-card label="Total Belanja" :value="'Rp '.number_format($totalSpent, 0, ',', '.')" hint="Dari pesanan selesai" /></div>
    </div>

    <h2 class="h6 fw-bold text-brand-dark mt-4 mb-3">Riwayat Terbaru</h2>
    <x-orders-table :orders="$recentOrders" />
@endsection
