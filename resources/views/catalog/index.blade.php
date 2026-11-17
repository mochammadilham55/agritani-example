@extends('layouts.public')

@section('content')
    <section class="rounded-4 bg-brand-dark text-white px-4 px-md-5 py-5 mb-4">
        <h1 class="fw-bold fs-2">Segar langsung dari petani lokal</h1>
        <p class="mb-0 mt-2 text-white-50" style="max-width: 36rem;">Belanja sayur, buah, beras, dan rempah berkualitas dengan harga adil untuk petani.</p>
    </section>

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
        <div class="card text-center text-secondary py-5" style="border-style: dashed;"><div class="card-body">Produk tidak ditemukan.</div></div>
    @else
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach ($products as $product)
                <div class="col"><x-product-card :product="$product" /></div>
            @endforeach
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
@endsection
