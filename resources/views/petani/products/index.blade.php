@extends('layouts.app')

@section('title', 'Produk Saya')
@section('heading', 'Produk Saya')

@section('content')
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-1 flex-wrap items-center gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…"
                   class="min-w-48 flex-1 rounded-lg border border-line bg-white px-3 py-2 text-sm">
            <select name="status" class="rounded-lg border border-line bg-white px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select>
            <button class="rounded-lg border border-line bg-white px-4 py-2 text-sm hover:bg-surface">Cari</button>
        </form>
        <a href="{{ route('petani.products.create') }}" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">+ Tambah Produk</a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                <tr><th class="px-4 py-3">Produk</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Stok</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($products as $p)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-role-bg">
                                    @if ($p->image)<img src="{{ asset('storage/'.$p->image) }}" alt="" class="h-full w-full object-cover">@else 🌿 @endif
                                </div>
                                <div><p class="font-medium">{{ $p->name }}</p><p class="text-xs text-gray-400">{{ $p->category?->name }}</p></div>
                            </div>
                        </td>
                        <td class="px-4 py-3">Rp {{ number_format($p->price, 0, ',', '.') }} <span class="text-xs text-gray-400">/ {{ $p->unit }}</span></td>
                        <td class="px-4 py-3">
                            @if ($p->stock <= \App\Models\Product::LOW_STOCK_THRESHOLD)
                                <span class="rounded-full bg-[#FCEFD6] px-2.5 py-0.5 text-xs font-semibold text-[#7A4B00]">{{ $p->stock }} {{ $p->unit }}</span>
                            @else {{ $p->stock }} {{ $p->unit }} @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($p->is_suspended)
                                <x-badge status="ditangguhkan" label="Diturunkan admin" />
                                @if ($p->suspend_reason)<span class="mt-1 block max-w-48 text-xs text-gray-500">{{ $p->suspend_reason }}</span>@endif
                            @else
                                <x-badge :status="$p->is_active ? 'aktif' : 'nonaktif'" />
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('petani.products.edit', $p) }}" class="rounded-lg border border-line px-3 py-1 text-xs hover:bg-surface">Edit</a>
                                <form method="POST" action="{{ route('petani.products.toggle', $p) }}">
                                    @csrf @method('PATCH')
                                    <button class="rounded-lg border border-line px-3 py-1 text-xs hover:bg-surface">{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
