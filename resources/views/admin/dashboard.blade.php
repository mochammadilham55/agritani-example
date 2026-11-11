@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Total Pengguna" :value="$userCount" hint="Petani + pembeli" />
        <x-stat-card label="Produk Aktif" :value="$activeProducts" />
        <x-stat-card label="Pesanan Hari Ini" :value="$ordersToday" />
        <x-stat-card label="Pendapatan" :value="'Rp '.number_format($revenue, 0, ',', '.')" hint="Dari pesanan selesai" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-line bg-white p-5">
            <h2 class="mb-4 font-bold text-brand-dark">Pesanan 7 Hari Terakhir</h2>
            @php $max = max(1, $chart->max('count')); @endphp
            <div class="flex h-40 items-end gap-2" role="img" aria-label="Grafik jumlah pesanan per hari">
                @foreach ($chart as $bar)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                        <span class="text-xs text-gray-500">{{ $bar['count'] }}</span>
                        <div class="w-full flex-none rounded-t bg-brand-chart" style="height: {{ $bar['count'] > 0 ? max(6, $bar['count'] / $max * 80) : 0 }}%"></div>
                        <span class="text-xs text-gray-400">{{ $bar['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="lg:col-span-2">
            <h2 class="mb-3 font-bold text-brand-dark">Pesanan Terbaru</h2>
            <x-orders-table :orders="$recentOrders" />
        </div>
    </div>
@endsection
