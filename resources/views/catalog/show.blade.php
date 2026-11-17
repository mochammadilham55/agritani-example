@extends('layouts.public')

@section('title', $product->name.' — AgriTani')

@section('content')
    <a href="{{ route('home') }}" class="small text-secondary">← Kembali ke katalog</a>

    <div class="row g-4 mt-1">
        <div class="col-md-6">
            <div class="ratio ratio-1x1 rounded-4 border bg-role-bg overflow-hidden">
                @if ($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="object-fit-cover w-100 h-100">
                @else
                    <div class="d-flex align-items-center justify-content-center" style="font-size: 6rem;" aria-hidden="true"></div>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <p class="small text-secondary mb-1">{{ $product->category?->name }}</p>
            <h1 class="fs-2 fw-bold text-brand-dark">{{ $product->name }}</h1>
            <p class="small text-secondary">Petani: <strong>{{ $product->farmer?->name }}</strong></p>

            <p class="display-6 fw-bold text-brand-dark mb-3">
                Rp {{ number_format($product->price, 0, ',', '.') }}<span class="fs-6 fw-normal text-secondary"> / {{ $product->unit }}</span>
            </p>

            <dl class="row small mb-3">
                <dt class="col-4 fw-normal text-secondary">Stok</dt>
                <dd class="col-8">@if ($product->stock > 0) {{ $product->stock }} {{ $product->unit }} @else <x-badge status="dibatalkan" label="Habis" /> @endif</dd>
                <dt class="col-4 fw-normal text-secondary">Berat</dt>
                <dd class="col-8">{{ number_format($product->weight, 0, ',', '.') }} gram / {{ $product->unit }}</dd>
            </dl>

            @if ($product->description)
                <p class="small">{{ $product->description }}</p>
            @endif

            @if ($product->stock > 0 && (! auth()->check() || auth()->user()->isPembeli()))
                <form method="POST" action="{{ route('pembeli.cart.store', $product) }}" class="d-flex align-items-center gap-3 mt-4">
                    @csrf
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="form-control" style="width: 6rem;" aria-label="Jumlah">
                    <button class="btn btn-primary fw-semibold">Tambah ke Keranjang</button>
                </form>
                @guest<p class="small text-secondary mt-2 mb-0">Anda akan diminta masuk terlebih dahulu.</p>@endguest
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-5">
            <h2 class="h6 fw-bold text-brand-dark mb-3">Produk Serupa</h2>
            <div class="row row-cols-2 row-cols-md-4 g-3">
                @foreach ($related as $item)
                    <div class="col"><x-product-card :product="$item" /></div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
