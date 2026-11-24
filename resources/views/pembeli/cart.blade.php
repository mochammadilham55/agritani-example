@extends('layouts.public')

@section('title', 'Keranjang — Tanimart')

@section('content')
    <h1 class="fs-2 fw-bold text-brand-dark mb-4">Keranjang Belanja</h1>

    @if ($items->isEmpty())
        <div class="card text-center py-5" style="border-style: dashed;">
            <div class="card-body">
                <p class="text-secondary">Keranjang Anda kosong.</p>
                <a href="{{ route('home') }}" class="btn btn-primary fw-semibold">Mulai Belanja</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <ul class="list-group list-group-flush">
                        @foreach ($items as $item)
                            @php $p = $item->product; @endphp
                            <li class="list-group-item p-3">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="rounded bg-role-bg d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 4rem; height: 4rem;">
                                        @if ($p->image)
                                            <img src="{{ asset('storage/'.$p->image) }}" alt="" width="64" height="64" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <span class="no-photo-sm">Belum ada foto</span>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 10rem;">
                                        <a href="{{ route('products.show', $p) }}" class="fw-semibold text-decoration-none">{{ $p->name }}</a>
                                        <p class="small text-secondary mb-0">{{ $p->farmer?->name }} · Rp {{ number_format($p->price, 0, ',', '.') }} / {{ $p->unit }}</p>
                                        @if (! $p->isPurchasable() || $p->stock < 1)
                                            <x-badge status="dibatalkan" label="Tidak tersedia" class="mt-1" />
                                        @elseif ($item->quantity > $p->stock)
                                            <p class="small mb-0 mt-1" style="color: #8E1F14;">Stok tersisa {{ $p->stock }} {{ $p->unit }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('pembeli.cart.update', $item) }}" class="d-flex align-items-center gap-2">
                                        @csrf @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ max($p->stock, 1) }}"
                                               class="form-control form-control-sm" style="width: 5rem;" aria-label="Jumlah">
                                        <button class="btn btn-outline-secondary btn-sm">Ubah</button>
                                    </form>
                                    <p class="fw-bold text-end mb-0" style="width: 7rem;">Rp {{ number_format($p->price * $item->quantity, 0, ',', '.') }}</p>
                                    <form method="POST" action="{{ route('pembeli.cart.destroy', $item) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-link text-danger p-0" aria-label="Hapus {{ $p->name }}">Hapus</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="card">
                    <div class="card-body">
                        <h2 class="h6 fw-bold text-brand-dark">Ringkasan</h2>
                        <div class="d-flex justify-content-between small mt-3"><span class="text-secondary">Subtotal</span>
                            <span class="fw-bold">Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                        <p class="small text-secondary mt-1">Ongkir dihitung saat checkout.</p>
                        @if (Route::has('pembeli.checkout.create'))
                            <a href="{{ route('pembeli.checkout.create') }}" class="btn btn-primary w-100 fw-semibold">Checkout</a>
                        @else
                            <span class="btn btn-secondary disabled w-100">Checkout (segera hadir)</span>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    @endif
@endsection
