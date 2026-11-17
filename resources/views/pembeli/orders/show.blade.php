@extends('layouts.app')

@section('title', 'Pesanan #'.$order->id)
@section('heading', 'Detail Pesanan #'.$order->id)

@section('content')
    @php
        $payment = $order->payment;
        $steps = ['pending' => 'Pending', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai'];
        $currentIdx = array_search($order->status, array_keys($steps));
    @endphp

    <a href="{{ route('pembeli.orders.index') }}" class="small text-secondary">← Semua pesanan</a>

    {{-- Timeline status --}}
    <div class="card mt-3">
        <div class="card-body">
            @if ($order->status === 'dibatalkan')
                <x-badge status="dibatalkan" label="Pesanan dibatalkan" />
            @else
                <ol class="list-unstyled row row-cols-4 g-2 text-center small mb-0">
                    @foreach ($steps as $key => $label)
                        @php $done = $loop->index <= $currentIdx; @endphp
                        <li class="col">
                            <div @class(['rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1', 'bg-brand text-white' => $done, 'bg-secondary-subtle text-secondary' => ! $done]) style="width: 2rem; height: 2rem;">{{ $done ? '✓' : $loop->iteration }}</div>
                            <span @class(['fw-semibold text-brand-dark' => $loop->index === $currentIdx, 'text-secondary' => ! $done])>{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8 d-grid gap-4 align-content-start">
            <section class="card">
                <h2 class="card-header h6 fw-bold text-brand-dark mb-0 py-3">Produk</h2>
                <ul class="list-group list-group-flush small">
                    @foreach ($order->items as $item)
                        <li class="list-group-item d-flex justify-content-between gap-3 px-3 py-3">
                            <span>
                                <a href="{{ route('products.show', $item->product) }}" class="fw-medium text-decoration-none">{{ $item->product->name }}</a>
                                <span class="d-block small text-secondary">{{ $item->quantity }} {{ $item->product->unit }} × Rp {{ number_format($item->price, 0, ',', '.') }} · {{ $item->product->farmer?->name }}</span>
                            </span>
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
                    <h2 class="h6 fw-bold text-brand-dark">Pengiriman</h2>
                    <p class="text-secondary mb-0">{{ $order->shipping_address }}</p>
                    @if ($order->notes)<p class="small text-secondary mt-1 mb-0">Catatan: {{ $order->notes }}</p>@endif

                    @if ($order->tracking_number)
                        <dl class="row mt-3 mb-0">
                            <dt class="col-4 fw-normal text-secondary">Kurir</dt><dd class="col-8">{{ $order->courier }}</dd>
                            <dt class="col-4 fw-normal text-secondary">No. Resi</dt><dd class="col-8 font-monospace">{{ $order->tracking_number }}</dd>
                        </dl>
                    @else
                        <p class="small text-secondary mt-3 mb-0">Resi akan muncul setelah petani mengirim pesanan.</p>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-lg-4 d-grid gap-3 align-content-start">
            <section class="card">
                <div class="card-body small">
                    <h2 class="h6 fw-bold text-brand-dark">Pembayaran</h2>
                    @if ($payment)
                        <p class="mb-0">{{ $payment->method === 'cod' ? 'Bayar di Tempat (COD)' : 'Transfer Bank' }} · <x-badge :status="$payment->status" /></p>

                        @if ($payment->proof_image)
                            <a href="{{ asset('storage/'.$payment->proof_image) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/'.$payment->proof_image) }}" alt="Bukti pembayaran" class="img-fluid rounded border mt-3" style="max-height: 12rem;">
                            </a>
                        @endif

                        @if ($payment->status === 'ditolak')
                            <p class="small mt-2 mb-0" style="color: #8E1F14;">Bukti pembayaran ditolak. Silakan unggah ulang.</p>
                        @endif

                        @if ($payment->method === 'transfer' && $order->status === 'pending' && $payment->status !== 'dikonfirmasi')
                            <form method="POST" action="{{ route('pembeli.orders.proof', $order) }}" enctype="multipart/form-data" class="mt-3">
                                @csrf
                                <input type="file" name="proof_image" accept="image/png,image/jpeg" required class="form-control form-control-sm">
                                @error('proof_image')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">JPG/PNG, maksimal 2 MB.</div>
                                <button class="btn btn-primary btn-sm w-100 fw-semibold mt-2">
                                    {{ $payment->proof_image ? 'Ganti Bukti' : 'Upload Bukti' }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </section>

            @if ($order->status === 'dikirim')
                <form method="POST" action="{{ route('pembeli.orders.complete', $order) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-primary w-100 fw-semibold">✓ Pesanan Diterima</button>
                </form>
            @endif

            @if ($order->status === 'pending')
                <form method="POST" action="{{ route('pembeli.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger w-100 fw-semibold">Batalkan Pesanan</button>
                </form>
            @endif
        </div>
    </div>
@endsection
