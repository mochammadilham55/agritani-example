@extends('layouts.public')

@section('content')
    <div class="mb-4">
        <h1 class="fw-bold fs-3 text-brand-dark mb-1">Hasil tani dari petani lokal</h1>
        <p class="text-secondary mb-0">{{ number_format($products->total(), 0, ',', '.') }} produk tersedia, dijual langsung oleh petaninya.</p>
    </div>

    <form method="GET" action="{{ route('home') }}" class="row g-2 align-items-center mb-4">
        <div class="col-12 col-md">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…" class="form-control" aria-label="Cari produk">
        </div>
        <div class="col-6 col-md-auto">
            <select name="kategori" class="form-select" onchange="this.form.submit()" aria-label="Kategori">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(request('kategori') === $cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-auto">
            <select name="urut" class="form-select" onchange="this.form.submit()" aria-label="Urutkan">
                <option value="">Terbaru</option>
                <option value="asc" @selected(request('urut') === 'asc')>Harga terendah</option>
                <option value="desc" @selected(request('urut') === 'desc')>Harga tertinggi</option>
            </select>
        </div>
        <div class="col-auto d-flex align-items-center gap-3">
            <button class="btn btn-primary fw-semibold">Cari</button>
            @if (request()->hasAny(['q', 'kategori', 'urut']))
                <a href="{{ route('home') }}" class="small text-secondary">Reset</a>
            @endif
        </div>
    </form>

    @if ($products->isEmpty())
        <div class="card text-center text-secondary py-5" style="border-style: dashed;"><div class="card-body">
            @if (request()->hasAny(['q', 'kategori']))
                Tidak ada produk yang cocok dengan pencarian atau kategori ini. <a href="{{ route('home') }}">Tampilkan semua produk</a>
            @else
                Belum ada produk yang dijual. Produk muncul di sini setelah petani menambahkannya.
            @endif
        </div></div>
    @else
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach ($products as $product)
                <div class="col"><x-product-card :product="$product" /></div>
            @endforeach
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
@endsection
