@extends('layouts.public')

@section('title', $product->name.' — AgriTani')

@section('content')
    <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:underline">← Kembali ke katalog</a>

    <div class="mt-4 grid gap-8 md:grid-cols-2">
        <div class="flex aspect-square items-center justify-center overflow-hidden rounded-2xl border border-line bg-role-bg text-8xl">
            @if ($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            @else
                <span aria-hidden="true">🌿</span>
            @endif
        </div>

        <div>
            <p class="text-sm text-gray-500">{{ $product->category?->name }}</p>
            <h1 class="text-2xl font-extrabold text-brand-dark">{{ $product->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Petani: <strong>{{ $product->farmer?->name }}</strong>
                @if ($rating) · ⭐ {{ $rating }} ({{ $reviews->count() }} ulasan) @endif
            </p>

            <p class="mt-4 text-3xl font-extrabold text-brand-dark">
                Rp {{ number_format($product->price, 0, ',', '.') }}<span class="text-base font-normal text-gray-500"> / {{ $product->unit }}</span>
            </p>

            <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <dt class="text-gray-500">Stok</dt>
                <dd>@if ($product->stock > 0) {{ $product->stock }} {{ $product->unit }} @else <x-badge status="dibatalkan" label="Habis" /> @endif</dd>
                <dt class="text-gray-500">Berat</dt>
                <dd>{{ number_format($product->weight, 0, ',', '.') }} gram / {{ $product->unit }}</dd>
            </dl>

            @if ($product->description)
                <p class="mt-4 text-sm leading-relaxed text-gray-700">{{ $product->description }}</p>
            @endif

            @if ($product->stock > 0 && (! auth()->check() || auth()->user()->isPembeli()))
                <form method="POST" action="{{ route('pembeli.cart.store', $product) }}" class="mt-6 flex items-center gap-3">
                    @csrf
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                           class="w-24 rounded-lg border border-line px-3 py-2 text-sm" aria-label="Jumlah">
                    <button class="rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">🛒 Tambah ke Keranjang</button>
                </form>
                @guest<p class="mt-2 text-xs text-gray-500">Anda akan diminta masuk terlebih dahulu.</p>@endguest
            @endif
        </div>
    </div>

    <section class="mt-12">
        <h2 class="mb-3 font-bold text-brand-dark">Ulasan Pembeli</h2>
        <div class="divide-y divide-line rounded-xl border border-line bg-white">
            @forelse ($reviews as $review)
                <div class="p-4 text-sm">
                    <p class="font-semibold">{{ $review->user->name }} <span class="ml-2 font-normal text-amber-600">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span></p>
                    @if ($review->comment)<p class="mt-1 text-gray-600">{{ $review->comment }}</p>@endif
                </div>
            @empty
                <p class="p-8 text-center text-sm text-gray-400">Belum ada ulasan.</p>
            @endforelse
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mt-12">
            <h2 class="mb-3 font-bold text-brand-dark">Produk Serupa</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
