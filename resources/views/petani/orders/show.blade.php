@extends('layouts.app')

@section('title', 'Pesanan #'.$order->id)
@section('heading', 'Pesanan #'.$order->id)

@section('content')
    @php $payment = $order->payment; @endphp

    <a href="{{ route('petani.orders.index') }}" class="small text-secondary">← Pesanan masuk</a>

    <div class="row g-4 mt-1">
        <div class="col-lg-8 d-grid gap-4 align-content-start">
            <section class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h2 class="h6 fw-bold text-brand-dark mb-0">Produk</h2><x-badge :status="$order->status" />
                </div>
                <ul class="list-group list-group-flush small">
                    @foreach ($order->items as $item)
                        <li class="list-group-item d-flex justify-content-between gap-3 px-3 py-3">
                            <span>{{ $item->product->name }}<span class="d-block small text-secondary">{{ $item->quantity }} {{ $item->product->unit }} × Rp {{ number_format($item->price, 0, ',', '.') }}</span></span>
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

            <section class="card">
                <div class="card-body small">
                    <h2 class="h6 fw-bold text-brand-dark">Pembeli & Pengiriman</h2>
                    <p class="fw-medium mb-0">{{ $order->buyer->name }} <span class="fw-normal text-secondary">· {{ $order->buyer->phone ?? '-' }}</span></p>
                    <p class="text-secondary mt-1 mb-0">{{ $order->shipping_address }}</p>
                    @if ($order->notes)<p class="small text-secondary mt-1 mb-0">Catatan: {{ $order->notes }}</p>@endif
                    @if ($order->tracking_number)
                        <p class="mt-3 mb-0">Kurir: <strong>{{ $order->courier }}</strong> · Resi: <span class="font-monospace">{{ $order->tracking_number }}</span></p>
                    @endif
                    @if ($order->autoCompleteAt())
                        <p class="small text-secondary mt-2 mb-0">Otomatis selesai pada {{ $order->autoCompleteAt()->format('d M Y H:i') }} jika pembeli belum mengonfirmasi.</p>
                    @endif
                </div>
            </section>
        </div>

        <aside class="col-lg-4 d-grid gap-3 align-content-start">
            <section class="card">
                <div class="card-body small">
                    <h2 class="h6 fw-bold text-brand-dark">Pembayaran</h2>
                    <p class="mb-0">{{ $payment?->method === 'cod' ? 'COD' : 'Transfer Bank' }} · <x-badge :status="$payment?->status ?? 'menunggu'" /></p>
                    @if ($payment?->proof_image)
                        <a href="{{ asset('storage/'.$payment->proof_image) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/'.$payment->proof_image) }}" alt="Bukti pembayaran" class="img-fluid rounded border mt-3" style="max-height: 12rem;"></a>
                    @elseif ($payment?->method === 'transfer')
                        <p class="small text-secondary mt-2 mb-0">Belum ada bukti transfer.</p>
                    @endif
                </div>
            </section>

            @if ($order->status === 'pending')
                <form method="POST" action="{{ route('petani.orders.confirm', $order->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-primary w-100 fw-semibold">Konfirmasi & Kemas</button>
                </form>
                @if ($payment?->method === 'transfer' && $payment->status !== 'dikonfirmasi')
                    <p class="small text-secondary mb-0">Transfer harus dikonfirmasi dulu di menu <a href="{{ route('petani.payments.index') }}" class="fw-semibold">Pembayaran</a>.</p>
                @endif
                <form method="POST" action="{{ route('petani.orders.reject', $order->id) }}" onsubmit="return confirm('Tolak pesanan ini? Stok akan dikembalikan.')">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger w-100 fw-semibold">Tolak Pesanan</button>
                </form>
            @elseif ($order->status === 'diproses')
                <a href="{{ route('petani.shipments.index') }}" class="btn btn-primary w-100 fw-semibold">Isi Resi Pengiriman</a>
            @endif
        </aside>
    </div>
@endsection
