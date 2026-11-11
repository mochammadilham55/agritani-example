@extends('layouts.app')

@section('title', 'Daftar Produk')
@section('heading', 'Daftar Produk')

@section('content')
    @if ($errors->has('reason'))
        <div class="mb-4 rounded-lg bg-[#FBE1DE] px-4 py-3 text-sm text-[#8E1F14]">{{ $errors->first('reason') }}</div>
    @endif

    <form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…" class="min-w-48 flex-1 rounded-lg border border-line bg-white px-3 py-2 text-sm">
        <select name="status" class="rounded-lg border border-line bg-white px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Semua</option>
            <option value="tampil" @selected(request('status') === 'tampil')>Tampil di katalog</option>
            <option value="diturunkan" @selected(request('status') === 'diturunkan')>Diturunkan</option>
        </select>
        <button class="rounded-lg border border-line bg-white px-4 py-2 text-sm hover:bg-surface">Cari</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                <tr><th class="px-4 py-3">Produk</th><th class="px-4 py-3">Petani</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Stok</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($products as $p)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $p->name }}<span class="block text-xs font-normal text-gray-400">{{ $p->category?->name }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->farmer?->name }}
                            @unless ($p->farmer?->is_active)<span class="block text-xs text-[#8E1F14]">akun ditangguhkan</span>@endunless</td>
                        <td class="px-4 py-3">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $p->stock }} {{ $p->unit }}</td>
                        <td class="px-4 py-3">
                            @if ($p->is_suspended)
                                <x-badge status="ditangguhkan" label="Diturunkan" />
                                <span class="mt-1 block max-w-48 text-xs text-gray-500">{{ $p->suspend_reason }}</span>
                            @elseif (! $p->is_active)
                                <x-badge status="nonaktif" />
                            @else
                                <x-badge status="aktif" />
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.products.toggle', $p->id) }}" class="flex items-center justify-end gap-2">
                                @csrf @method('PATCH')
                                @if ($p->is_suspended)
                                    <button class="rounded-lg bg-brand px-3 py-1 text-xs font-semibold text-white hover:bg-brand-dark">Pulihkan</button>
                                @else
                                    <input name="reason" required maxlength="200" placeholder="Alasan…" class="w-36 rounded-lg border border-line px-2 py-1 text-xs" aria-label="Alasan penurunan">
                                    <button class="rounded-lg border border-[#8E1F14]/30 px-3 py-1 text-xs font-semibold text-[#8E1F14] hover:bg-[#FBE1DE]">Turunkan</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
