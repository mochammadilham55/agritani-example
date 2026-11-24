@extends('layouts.app')

@section('title', 'Produk Saya')
@section('heading', 'Produk Saya')

@section('content')
    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <form method="GET" class="d-flex flex-wrap flex-grow-1 align-items-center gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…" class="form-control flex-grow-1" style="min-width: 12rem; width: auto;" aria-label="Cari produk">
            <select name="status" class="form-select w-auto" onchange="this.form.submit()" aria-label="Status">
                <option value="">Semua status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select>
            <button class="btn btn-outline-secondary">Cari</button>
        </form>
        <a href="{{ route('petani.products.create') }}" class="btn btn-primary fw-semibold">+ Tambah Produk</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr><th>Produk</th><th>Harga</th><th>Stok</th><th>Status</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded bg-role-bg d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 2.5rem; height: 2.5rem;">
                                        @if ($p->image)<img src="{{ asset('storage/'.$p->image) }}" alt="" width="40" height="40" class="w-100 h-100 object-fit-cover">@else<span class="no-photo-sm">Tanpa foto</span>@endif
                                    </div>
                                    <div><p class="fw-medium mb-0">{{ $p->name }}</p><p class="small text-secondary mb-0">{{ $p->category?->name }}</p></div>
                                </div>
                            </td>
                            <td>Rp {{ number_format($p->price, 0, ',', '.') }} <span class="small text-secondary">/ {{ $p->unit }}</span></td>
                            <td>
                                @if ($p->stock <= \App\Models\Product::LOW_STOCK_THRESHOLD)
                                    <span class="badge rounded-pill" style="background: #FCEFD6; color: #7A4B00;">{{ $p->stock }} {{ $p->unit }}</span>
                                @else {{ $p->stock }} {{ $p->unit }} @endif
                            </td>
                            <td><x-badge :status="$p->is_active ? 'aktif' : 'nonaktif'" /></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('petani.products.edit', $p) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('petani.products.toggle', $p) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-outline-secondary btn-sm">{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-5 text-center text-secondary">@if (request()->hasAny(['q', 'status']))Tidak ada produk yang cocok dengan pencarian atau status ini.@else Belum ada produk. <a href="{{ route('petani.products.create') }}">Tambah produk pertama</a>@endif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
