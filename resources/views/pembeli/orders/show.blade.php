@extends('layouts.app')

@section('title', 'Pesanan #'.$order->id)
@section('heading', 'Detail Pesanan #'.$order->id)

@section('content')
    @php
        $payment = $order->payment;
        $steps = ['pending' => 'Pending', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai'];
        $currentIdx = array_search($order->status, array_keys($steps));
    @endphp

    <a href="{{ route('pembeli.orders.index') }}" class="text-sm text-gray-500 hover:underline">← Semua pesanan</a>

    {{-- Timeline status --}}
    <div class="mt-4 rounded-xl border border-line bg-white p-5">
        @if ($order->status === 'dibatalkan')
            <x-badge status="dibatalkan" label="Pesanan dibatalkan" />
        @else
            <ol class="grid grid-cols-4 gap-2 text-center text-xs">
                @foreach ($steps as $key => $label)
                    @php $done = $loop->index <= $currentIdx; @endphp
                    <li>
                        <div @class(['mx-auto mb-1 flex h-8 w-8 items-center justify-center rounded-full font-bold', 'bg-brand text-white' => $done, 'bg-line text-gray-400' => ! $done])>{{ $done ? '✓' : $loop->iteration }}</div>
                        <span @class(['font-semibold text-brand-dark' => $loop->index === $currentIdx, 'text-gray-400' => ! $done])>{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-line bg-white">
                <h2 class="border-b border-line px-5 py-3 font-bold text-brand-dark">Produk</h2>
                <ul class="divide-y divide-line text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between gap-4 px-5 py-3">
                            <span>
                                <a href="{{ route('products.show', $item->product) }}" class="font-medium hover:text-brand">{{ $item->product->name }}</a>
                                <span class="block text-xs text-gray-500">{{ $item->quantity }} {{ $item->product->unit }} × Rp {{ number_format($item->price, 0, ',', '.') }} · {{ $item->product->farmer?->name }}</span>
                            </span>
                            <span class="text-right">
                                <span class="block font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                @if ($order->status === 'selesai')
                                    @if ($reviewed = $order->reviews->firstWhere('product_id', $item->product_id))
                                        <a href="{{ route('pembeli.reviews.edit', $reviewed) }}" class="text-xs text-brand hover:underline">Lihat/ubah ulasan</a>
                                    @else
                                        <a href="{{ route('pembeli.reviews.create', [$order, $item->product]) }}" class="text-xs font-semibold text-brand hover:underline">⭐ Beri ulasan</a>
                                    @endif
                                @endif
                            </span>
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
                <h2 class="mb-2 font-bold text-brand-dark">Pengiriman</h2>
                <p class="text-gray-600">{{ $order->shipping_address }}</p>
                @if ($order->notes)<p class="mt-1 text-xs text-gray-400">Catatan: {{ $order->notes }}</p>@endif

                @if ($order->shipment && $order->shipment->tracking_number)
                    <dl class="mt-3 grid grid-cols-2 gap-1">
                        <dt class="text-gray-500">Kurir</dt><dd>{{ $order->shipment->courier }}</dd>
                        <dt class="text-gray-500">No. Resi</dt><dd class="font-mono">{{ $order->shipment->tracking_number }}</dd>
                        <dt class="text-gray-500">Status</dt><dd><x-badge :status="$order->shipment->status" /></dd>
                        @if ($order->shipment->estimated_arrival)
                            <dt class="text-gray-500">Estimasi tiba</dt><dd>{{ $order->shipment->estimated_arrival->format('d M Y') }}</dd>
                        @endif
                    </dl>
                @else
                    <p class="mt-3 text-xs text-gray-400">Resi akan muncul setelah petani mengirim pesanan.</p>
                @endif
            </section>
        </div>

        <aside class="space-y-4">
            <section class="rounded-xl border border-line bg-white p-5 text-sm">
                <h2 class="mb-2 font-bold text-brand-dark">Pembayaran</h2>
                @if ($payment)
                    <p>{{ $payment->method === 'cod' ? 'Bayar di Tempat (COD)' : 'Transfer Bank' }} · <x-badge :status="$payment->status" /></p>

                    @if ($payment->proof_image)
                        <a href="{{ asset('storage/'.$payment->proof_image) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/'.$payment->proof_image) }}" alt="Bukti pembayaran" class="mt-3 max-h-48 rounded-lg border border-line">
                        </a>
                    @endif

                    @if ($payment->status === 'ditolak')
                        <p class="mt-2 text-xs text-[#8E1F14]">Bukti pembayaran ditolak. Silakan unggah ulang.</p>
                    @endif

                    @if ($payment->method === 'transfer' && $order->status === 'pending' && $payment->status !== 'dikonfirmasi')
                        <form method="POST" action="{{ route('pembeli.orders.proof', $order) }}" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            <input type="file" name="proof_image" accept="image/png,image/jpeg" required class="block w-full text-xs">
                            @error('proof_image')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
                            <p class="mt-1 text-xs text-gray-400">JPG/PNG, maksimal 2 MB.</p>
                            <button class="mt-2 w-full rounded-lg bg-brand py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                                {{ $payment->proof_image ? 'Ganti Bukti' : 'Upload Bukti' }}
                            </button>
                        </form>
                    @endif
                @endif
            </section>

            @if ($order->status === 'dikirim')
                <form method="POST" action="{{ route('pembeli.orders.complete', $order) }}">
                    @csrf @method('PATCH')
                    <button class="w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">✓ Pesanan Diterima</button>
                </form>
            @endif

            @if ($order->status === 'pending')
                <form method="POST" action="{{ route('pembeli.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
                    @csrf @method('PATCH')
                    <button class="w-full rounded-lg border border-[#8E1F14]/30 py-2.5 text-sm font-semibold text-[#8E1F14] hover:bg-[#FBE1DE]">Batalkan Pesanan</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
