@extends('layouts.public')

@section('title', 'Keranjang — AgriTani')

@section('content')
    <h1 class="mb-6 text-2xl font-extrabold text-brand-dark">Keranjang Belanja</h1>

    @if ($items->isEmpty())
        <div class="rounded-xl border border-dashed border-line bg-white py-16 text-center">
            <p class="text-gray-400">Keranjang Anda kosong.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block rounded-lg bg-brand px-5 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Mulai Belanja</a>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="divide-y divide-line rounded-xl border border-line bg-white lg:col-span-2">
                @foreach ($items as $item)
                    @php $p = $item->product; @endphp
                    <div class="flex flex-wrap items-center gap-4 p-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-role-bg text-2xl">🌿</div>
                        <div class="min-w-40 flex-1">
                            <a href="{{ route('products.show', $p) }}" class="font-semibold hover:text-brand">{{ $p->name }}</a>
                            <p class="text-xs text-gray-500">{{ $p->farmer?->name }} · Rp {{ number_format($p->price, 0, ',', '.') }} / {{ $p->unit }}</p>
                            @if (! $p->isPurchasable() || $p->stock < 1)
                                <x-badge status="dibatalkan" label="Tidak tersedia" class="mt-1" />
                            @elseif ($item->quantity > $p->stock)
                                <p class="mt-1 text-xs text-[#8E1F14]">Stok tersisa {{ $p->stock }} {{ $p->unit }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('pembeli.cart.update', $item) }}" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ max($p->stock, 1) }}"
                                   class="w-20 rounded-lg border border-line px-2 py-1.5 text-sm" aria-label="Jumlah">
                            <button class="rounded-lg border border-line px-3 py-1.5 text-xs hover:bg-surface">Ubah</button>
                        </form>
                        <p class="w-28 text-right font-bold">Rp {{ number_format($p->price * $item->quantity, 0, ',', '.') }}</p>
                        <form method="POST" action="{{ route('pembeli.cart.destroy', $item) }}">
                            @csrf @method('DELETE')
                            <button class="text-[#8E1F14] hover:underline" aria-label="Hapus {{ $p->name }}">🗑</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <aside class="h-fit rounded-xl border border-line bg-white p-5">
                <h2 class="font-bold text-brand-dark">Ringkasan</h2>
                <div class="mt-3 flex justify-between text-sm"><span class="text-gray-500">Subtotal</span>
                    <span class="font-bold">Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                <p class="mt-1 text-xs text-gray-400">Ongkir dihitung saat checkout.</p>
                @if (Route::has('pembeli.checkout.create'))
                    <a href="{{ route('pembeli.checkout.create') }}" class="mt-4 block rounded-lg bg-brand py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-dark">Checkout</a>
                @else
                    <span class="mt-4 block cursor-not-allowed rounded-lg bg-gray-200 py-2.5 text-center text-sm font-semibold text-gray-500">Checkout (segera hadir)</span>
                @endif
            </aside>
        </div>
    @endif
@endsection
