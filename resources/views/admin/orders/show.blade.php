@extends('layouts.app')

@section('title', 'Pesanan #'.$order->id)
@section('heading', 'Pesanan #'.$order->id)

@section('content')
    @php $payment = $order->payment; @endphp

    <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:underline">← Daftar pesanan</a>

    <div class="mt-4 grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-line bg-white lg:col-span-2">
            <div class="flex items-center justify-between border-b border-line px-5 py-3">
                <h2 class="font-bold text-brand-dark">Produk</h2><x-badge :status="$order->status" />
            </div>
            <ul class="divide-y divide-line text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 px-5 py-3">
                        <span>{{ $item->product->name }}
                            <span class="block text-xs text-gray-500">{{ $item->quantity }} {{ $item->product->unit }} × Rp {{ number_format($item->price, 0, ',', '.') }} · {{ $item->product->farmer?->name }}</span></span>
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

        <aside class="space-y-4 text-sm">
            <section class="rounded-xl border border-line bg-white p-5">
                <h2 class="mb-2 font-bold text-brand-dark">Pembeli</h2>
                <p class="font-medium">{{ $order->buyer->name }}</p>
                <p class="text-gray-500">{{ $order->buyer->email }} · {{ $order->buyer->phone ?? '-' }}</p>
                <p class="mt-2 text-gray-600">{{ $order->shipping_address }}</p>
            </section>
            <section class="rounded-xl border border-line bg-white p-5">
                <h2 class="mb-2 font-bold text-brand-dark">Pembayaran</h2>
                <p>{{ $payment?->method === 'cod' ? 'COD' : 'Transfer Bank' }} · <x-badge :status="$payment?->status ?? 'menunggu'" /></p>
                @if ($payment?->proof_image)
                    <a href="{{ asset('storage/'.$payment->proof_image) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-brand hover:underline">Lihat bukti</a>
                @endif
            </section>
            <section class="rounded-xl border border-line bg-white p-5">
                <h2 class="mb-2 font-bold text-brand-dark">Pengiriman</h2>
                @if ($order->shipment?->tracking_number)
                    <p>{{ $order->shipment->courier }} · <span class="font-mono">{{ $order->shipment->tracking_number }}</span> <x-badge :status="$order->shipment->status" /></p>
                @else
                    <p class="text-gray-400">Belum dikirim.</p>
                @endif
            </section>
        </aside>
    </div>
@endsection
