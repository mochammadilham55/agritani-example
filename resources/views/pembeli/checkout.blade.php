@extends('layouts.public')

@section('title', 'Checkout — AgriTani')

@section('content')
    <h1 class="mb-6 text-2xl font-extrabold text-brand-dark">Checkout</h1>

    <form method="POST" action="{{ route('pembeli.checkout.store') }}" class="grid gap-6 lg:grid-cols-3">
        @csrf

        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-line bg-white p-5">
                <h2 class="mb-3 font-bold text-brand-dark">Alamat Pengiriman</h2>
                <textarea name="shipping_address" rows="3" required placeholder="Nama jalan, nomor, kelurahan, kota, kode pos"
                          class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">{{ old('shipping_address', auth()->user()->address) }}</textarea>
                @error('shipping_address')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
                <textarea name="notes" rows="2" placeholder="Catatan untuk petani (opsional)"
                          class="mt-3 w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">{{ old('notes') }}</textarea>
            </section>

            <section class="rounded-xl border border-line bg-white p-5">
                <h2 class="mb-3 font-bold text-brand-dark">Metode Pembayaran</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach (['transfer' => ['Transfer Bank', 'Upload bukti transfer setelah pesan.'], 'cod' => ['Bayar di Tempat (COD)', 'Bayar tunai saat pesanan tiba.']] as $value => [$label, $hint])
                        <label class="cursor-pointer">
                            <input type="radio" name="method" value="{{ $value }}" class="peer sr-only" @checked(old('method', 'transfer') === $value)>
                            <span class="block rounded-lg border border-line p-3 peer-checked:border-brand peer-checked:bg-role-bg">
                                <span class="block text-sm font-semibold">{{ $label }}</span>
                                <span class="block text-xs text-gray-500">{{ $hint }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('method')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </section>

            <section class="rounded-xl border border-line bg-white p-5">
                <h2 class="mb-3 font-bold text-brand-dark">Rincian Pesanan</h2>
                @foreach ($groups as $lines)
                    <div class="mb-4 last:mb-0">
                        <p class="text-sm font-semibold">Dari {{ $lines->first()->product->farmer?->name }}
                            <span class="font-normal text-gray-400">· ongkir Rp {{ number_format($shipping, 0, ',', '.') }}</span></p>
                        <ul class="mt-1 divide-y divide-line text-sm">
                            @foreach ($lines as $l)
                                <li class="flex justify-between py-1.5">
                                    <span>{{ $l->product->name }} × {{ $l->quantity }} {{ $l->product->unit }}</span>
                                    <span>Rp {{ number_format($l->product->price * $l->quantity, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
                @if ($groups->count() > 1)
                    <p class="mt-3 rounded-lg bg-[#FCEFD6] px-3 py-2 text-xs text-[#7A4B00]">
                        Produk dari {{ $groups->count() }} petani berbeda akan dipecah menjadi {{ $groups->count() }} pesanan.
                    </p>
                @endif
            </section>
        </div>

        <aside class="h-fit rounded-xl border border-line bg-white p-5">
            <h2 class="font-bold text-brand-dark">Total</h2>
            <dl class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>Rp {{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Ongkir ({{ $groups->count() }}×)</dt><dd>Rp {{ number_format($shipping * $groups->count(), 0, ',', '.') }}</dd></div>
                <div class="flex justify-between border-t border-line pt-2 text-base font-extrabold text-brand-dark"><dt>Total</dt><dd>Rp {{ number_format($grand, 0, ',', '.') }}</dd></div>
            </dl>
            <button class="mt-4 w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Buat Pesanan</button>
            <a href="{{ route('pembeli.cart.index') }}" class="mt-2 block text-center text-sm text-gray-500 hover:underline">Kembali ke keranjang</a>
        </aside>
    </form>
@endsection
