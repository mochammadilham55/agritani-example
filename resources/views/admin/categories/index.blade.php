@extends('layouts.app')

@section('title', 'Kelola Kategori')
@section('heading', 'Kelola Kategori')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="card">
                @csrf
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark mb-3">Tambah Kategori</h2>
                    <div class="mb-3">
                        <input name="name" value="{{ old('name') }}" placeholder="Nama kategori" required class="form-control" aria-label="Nama kategori">
                        @error('name')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <textarea name="description" rows="2" placeholder="Deskripsi (opsional)" class="form-control" aria-label="Deskripsi">{{ old('description') }}</textarea>
                        @error('description')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm" aria-label="Gambar">
                        @error('image')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary w-100 fw-semibold">Simpan</button>
                </div>
            </form>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead>
                            <tr><th>Kategori</th><th>Produk</th><th class="text-end">Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $c)
                                <tr>
                                    <td class="fw-medium">{{ $c->name }}<span class="d-block small fw-normal text-secondary">{{ $c->description }}</span></td>
                                    <td>{{ $c->products_count }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Hapus kategori {{ $c->name }}?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-5 text-center text-secondary">Belum ada kategori.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
