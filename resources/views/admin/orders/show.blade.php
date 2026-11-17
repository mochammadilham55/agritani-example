@extends('layouts.app')

@section('title', 'Pesanan #'.$order->id)
@section('heading', 'Pesanan #'.$order->id)

@section('content')
    @php $payment = $order->payment; @endphp

    <a href="{{ route('admin.orders.index') }}" class="small text-secondary">← Daftar pesanan</a>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <section class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h2 class="h6 fw-bold text-brand-dark mb-0">Produk</h2><x-badge :status="$order->status" />
                </div>
                <ul class="list-group list-group-flush small">
                    @foreach ($order->items as $item)
                        <li class="list-group-item d-flex justify-content-between gap-3 px-3 py-3">
                            <span>{{ $item->product->name }}
                                <span class="d-block small text-secondary">{{ $item->quantity }} {{ $item->product->unit }} × Rp {{ number_format($item->price, 0, ',', '.') }} · {{ $item->product->farmer?->name }}</span></span>
                            <span class="fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="small border-top px-3 py-3 mb-0">
                    <div class="d-flex justify-content-between"><dt class="fw-normal text-secondary">Subtotal</dt><dd class="mb-1">Rp {{ number_format($order->total_price, 0, ',', '.') }}</dd></div>
                    <div class="d-flex justify-content-between"><dt class="fw-normal text-secondary">Ongkir</dt><dd class="mb-1">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd></div>
                    <div class="d-flex justify-content-between fs-6 fw-bold text-brand-dark"><dt>Total</dt><dd class="mb-0">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</dd></div>
                </dl>
            </section>
        </div>

        <aside class="col-lg-4 d-grid gap-3 align-content-start small">
            <section class="card">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark">Pembeli</h2>
                    <p class="fw-medium mb-0">{{ $order->buyer->name }}</p>
                    <p class="text-secondary mb-0">{{ $order->buyer->email }} · {{ $order->buyer->phone ?? '-' }}</p>
                    <p class="text-secondary mt-2 mb-0">{{ $order->shipping_address }}</p>
                </div>
            </section>
            <section class="card">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark">Pembayaran</h2>
                    <p class="mb-0">{{ $payment?->method === 'cod' ? 'COD' : 'Transfer Bank' }} · <x-badge :status="$payment?->status ?? 'menunggu'" /></p>
                    @if ($payment?->proof_image)
                        <a href="{{ asset('storage/'.$payment->proof_image) }}" target="_blank" rel="noopener" class="d-inline-block mt-2">Lihat bukti</a>
                    @endif
                </div>
            </section>
            <section class="card">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark">Pengiriman</h2>
                    @if ($order->tracking_number)
                        <p class="mb-0">{{ $order->courier }} · <span class="font-monospace">{{ $order->tracking_number }}</span></p>
                    @else
                        <p class="text-secondary mb-0">Belum dikirim.</p>
                    @endif
                </div>
            </section>
        </aside>
    </div>
@endsection
