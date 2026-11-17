@extends('layouts.app')

@section('title', 'Pesanan Saya')
@section('heading', 'Pesanan Saya')

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('pembeli.orders.index') }}" @class(['filter-pill', 'active' => ! request('status')])>Semua</a>
        @foreach (\App\Models\Order::STATUSES as $s)
            <a href="{{ route('pembeli.orders.index', ['status' => $s]) }}" @class(['filter-pill', 'active' => request('status') === $s])>{{ ucfirst($s) }}</a>
        @endforeach
    </div>

    <div class="d-grid gap-3">
        @forelse ($orders as $order)
            <a href="{{ route('pembeli.orders.show', $order) }}" class="card product-card">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <p class="fw-semibold mb-0">Pesanan #{{ $order->id }}
                            <span class="ms-2 small fw-normal text-secondary">{{ $order->created_at->format('d M Y, H:i') }}</span></p>
                        <x-badge :status="$order->status" />
                    </div>
                    <p class="small text-secondary text-truncate mt-1 mb-0">
                        {{ $order->items->map(fn ($i) => $i->product->name.' ×'.$i->quantity)->join(', ') }}
                    </p>
                    <div class="d-flex align-items-center justify-content-between small mt-2">
                        <span class="fw-bold text-brand-dark">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
                        @if ($order->payment)
                            <span class="text-secondary">{{ $order->payment->method === 'cod' ? 'COD' : 'Transfer' }} · <x-badge :status="$order->payment->status" /></span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="card text-center text-secondary py-5" style="border-style: dashed;"><div class="card-body">Belum ada pesanan.</div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
