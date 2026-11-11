@extends('layouts.app')

@section('title', 'Pesanan #'.$order->id)
@section('heading', 'Pesanan #'.$order->id)

@section('content')
    @php $payment = $order->payment; @endphp

    <a href="{{ route('petani.orders.index') }}" class="text-sm text-gray-500 hover:underline">← Pesanan masuk</a>

    <div class="mt-4 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-line bg-white">
                <div class="flex items-center justify-between border-b border-line px-5 py-3">
                    <h2 class="font-bold text-brand-dark">Produk</h2><x-badge :status="$order->status" />
                </div>
                <ul class="divide-y divide-line text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between gap-4 px-5 py-3">
                            <span>{{ $item->product->name }}<span class="block text-xs text-gray-500">{{ $item->quantity }} {{ $item->product->unit }} × Rp {{ number_format($item->price, 0, ',', '.') }}</span></span>
                            <span class="font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="space-y-1 border-t border-line px-5 py-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>Rp {{ number_format($order->total_price, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Ongkir</dt><dd>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between text-base font-extrabold text-brand-dark"><dt>Total</dt><dd>Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</dd></div>
                </dl>
            </section>

            <section class="rounded-xl border border-line bg-white p-5 text-sm">
                <h2 class="mb-2 font-bold text-brand-dark">Pembeli & Pengiriman</h2>
                <p class="font-medium">{{ $order->buyer->name }} <span class="font-normal text-gray-500">· {{ $order->buyer->phone ?? '-' }}</span></p>
                <p class="mt-1 text-gray-600">{{ $order->shipping_address }}</p>
                @if ($order->notes)<p class="mt-1 text-xs text-gray-400">Catatan: {{ $order->notes }}</p>@endif
                @if ($order->shipment?->tracking_number)
                    <p class="mt-3">Kurir: <strong>{{ $order->shipment->courier }}</strong> · Resi: <span class="font-mono">{{ $order->shipment->tracking_number }}</span> <x-badge :status="$order->shipment->status" /></p>
                @endif
            </section>
        </div>

        <aside class="space-y-4">
            <section class="rounded-xl border border-line bg-white p-5 text-sm">
                <h2 class="mb-2 font-bold text-brand-dark">Pembayaran</h2>
                <p>{{ $payment?->method === 'cod' ? 'COD' : 'Transfer Bank' }} · <x-badge :status="$payment?->status ?? 'menunggu'" /></p>
                @if ($payment?->proof_image)
                    <a href="{{ asset('storage/'.$payment->proof_image) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/'.$payment->proof_image) }}" alt="Bukti pembayaran" class="mt-3 max-h-48 rounded-lg border border-line"></a>
                @elseif ($payment?->method === 'transfer')
                    <p class="mt-2 text-xs text-gray-400">Belum ada bukti transfer.</p>
                @endif
            </section>

            @if ($order->status === 'pending')
                <form method="POST" action="{{ route('petani.orders.confirm', $order->id) }}">
                    @csrf @method('PATCH')
                    <button class="w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Konfirmasi & Kemas</button>
                </form>
                @if ($payment?->method === 'transfer' && $payment->status !== 'dikonfirmasi')
                    <p class="text-xs text-gray-500">Transfer harus dikonfirmasi dulu di menu <a href="{{ route('petani.payments.index') }}" class="font-semibold text-brand hover:underline">Pembayaran</a>.</p>
                @endif
                <form method="POST" action="{{ route('petani.orders.reject', $order->id) }}" onsubmit="return confirm('Tolak pesanan ini? Stok akan dikembalikan.')">
                    @csrf @method('PATCH')
                    <button class="w-full rounded-lg border border-[#8E1F14]/30 py-2.5 text-sm font-semibold text-[#8E1F14] hover:bg-[#FBE1DE]">Tolak Pesanan</button>
                </form>
            @elseif ($order->status === 'diproses')
                <a href="{{ route('petani.shipments.index') }}" class="block rounded-lg bg-brand py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-dark">Isi Resi Pengiriman →</a>
            @endif
        </aside>
    </div>
@endsection
