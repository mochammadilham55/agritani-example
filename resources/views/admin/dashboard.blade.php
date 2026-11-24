@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard')

@section('content')
    <div class="row g-3">
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Total Pengguna" :value="$userCount" hint="Petani + pembeli" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Produk Aktif" :value="$activeProducts" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Pesanan Hari Ini" :value="$ordersToday" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat-card label="Pendapatan" :value="'Rp '.number_format($revenue, 0, ',', '.')" hint="Dari pesanan selesai" /></div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark mb-3">Jumlah pesanan per hari, 7 hari terakhir</h2>
                    @php $max = max(1, $chart->max('count')); @endphp
                    <div class="d-flex align-items-end gap-2" style="height: 10rem;" role="img" aria-label="Grafik jumlah pesanan per hari">
                        @foreach ($chart as $bar)
                            <div class="d-flex flex-column align-items-center justify-content-end gap-1 flex-fill h-100">
                                <span class="small text-secondary">{{ $bar['count'] }}</span>
                                <div class="chart-bar" style="height: {{ $bar['count'] > 0 ? max(6, $bar['count'] / $max * 80) : 0 }}%"></div>
                                <span class="small text-secondary">{{ $bar['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <h2 class="h6 fw-bold text-brand-dark mb-3">Pesanan Terbaru</h2>
            <x-orders-table :orders="$recentOrders" />
        </div>
    </div>
@endsection
