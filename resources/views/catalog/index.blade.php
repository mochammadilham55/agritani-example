@extends('layouts.public')

@section('content')
    <section class="mb-8 rounded-2xl bg-brand-dark px-6 py-10 text-white">
        <h1 class="text-2xl font-extrabold sm:text-3xl">Segar langsung dari petani lokal</h1>
        <p class="mt-2 max-w-xl text-white/80">Belanja sayur, buah, beras, dan rempah berkualitas dengan harga adil untuk petani.</p>
    </section>

    <form method="GET" action="{{ route('home') }}" class="mb-6 flex flex-wrap items-center gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…"
               class="min-w-48 flex-1 rounded-lg border border-line bg-white px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        <select name="kategori" class="rounded-lg border border-line bg-white px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Semua kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->slug }}" @selected(request('kategori') === $cat->slug)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="urut" class="rounded-lg border border-line bg-white px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Terbaru</option>
            <option value="asc" @selected(request('urut') === 'asc')>Harga terendah</option>
            <option value="desc" @selected(request('urut') === 'desc')>Harga tertinggi</option>
        </select>
        <button class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Cari</button>
        @if (request()->hasAny(['q', 'kategori', 'urut']))
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
        @endif
    </form>

    @if ($products->isEmpty())
        <div class="rounded-xl border border-dashed border-line bg-white py-16 text-center text-gray-400">Produk tidak ditemukan.</div>
    @else
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    @endif
@endsection
