@extends('layouts.app')

@section('title', 'Daftar Petani')
@section('heading', 'Daftar Petani')

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email…" class="min-w-48 flex-1 rounded-lg border border-line bg-white px-3 py-2 text-sm">
        <select name="status" class="rounded-lg border border-line bg-white px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Belum aktif / ditangguhkan</option>
        </select>
        <button class="rounded-lg border border-line bg-white px-4 py-2 text-sm hover:bg-surface">Cari</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                <tr><th class="px-4 py-3">Petani</th><th class="px-4 py-3">Kontak</th><th class="px-4 py-3">Produk</th><th class="px-4 py-3">Bergabung</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($farmers as $f)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $f->name }}<span class="block text-xs font-normal text-gray-500">{{ $f->email }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $f->phone ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $f->products_count }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $f->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3"><x-badge :status="$f->is_active ? 'aktif' : 'ditangguhkan'" :label="$f->is_active ? 'Aktif' : 'Belum aktif'" /></td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.farmers.toggle', $f) }}"
                                  @if ($f->is_active) onsubmit="return confirm('Tangguhkan akun ini? Produknya akan disembunyikan dari katalog.')" @endif>
                                @csrf @method('PATCH')
                                @if ($f->is_active)
                                    <button class="rounded-lg border border-[#8E1F14]/30 px-3 py-1 text-xs font-semibold text-[#8E1F14] hover:bg-[#FBE1DE]">Tangguhkan</button>
                                @else
                                    <button class="rounded-lg bg-brand px-3 py-1 text-xs font-semibold text-white hover:bg-brand-dark">Verifikasi / Aktifkan</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada petani.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $farmers->links() }}</div>
@endsection
