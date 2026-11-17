@extends('layouts.app')

@section('title', 'Daftar Petani')
@section('heading', 'Daftar Petani')

@section('content')
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email…" class="form-control flex-grow-1" style="min-width: 12rem; width: auto;" aria-label="Cari petani">
        <select name="status" class="form-select w-auto" onchange="this.form.submit()" aria-label="Status">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Belum aktif / ditangguhkan</option>
        </select>
        <button class="btn btn-outline-secondary">Cari</button>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr><th>Petani</th><th>Kontak</th><th>Produk</th><th>Bergabung</th><th>Status</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($farmers as $f)
                        <tr>
                            <td class="fw-medium">{{ $f->name }}<span class="d-block small fw-normal text-secondary">{{ $f->email }}</span></td>
                            <td class="text-secondary">{{ $f->phone ?? '-' }}</td>
                            <td>{{ $f->products_count }}</td>
                            <td class="text-secondary">{{ $f->created_at->format('d M Y') }}</td>
                            <td><x-badge :status="$f->is_active ? 'aktif' : 'ditangguhkan'" :label="$f->is_active ? 'Aktif' : 'Belum aktif'" /></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.farmers.toggle', $f) }}"
                                      @if ($f->is_active) onsubmit="return confirm('Tangguhkan akun ini? Produknya akan disembunyikan dari katalog.')" @endif>
                                    @csrf @method('PATCH')
                                    @if ($f->is_active)
                                        <button class="btn btn-outline-danger btn-sm fw-semibold">Tangguhkan</button>
                                    @else
                                        <button class="btn btn-primary btn-sm fw-semibold">Verifikasi / Aktifkan</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5 text-center text-secondary">Tidak ada petani.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $farmers->links() }}</div>
@endsection
