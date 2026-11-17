@extends('layouts.app')

@php $editing = $product->exists; @endphp

@section('title', $editing ? 'Edit Produk' : 'Tambah Produk')
@section('heading', $editing ? 'Edit Produk' : 'Tambah Produk')

@section('content')
    <form method="POST" enctype="multipart/form-data" class="card" style="max-width: 42rem;"
          action="{{ $editing ? route('petani.products.update', $product) : route('petani.products.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label small fw-medium" for="name">Nama produk</label>
                <input id="name" name="name" value="{{ old('name', $product->name) }}" required class="form-control">
                @error('name')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label small fw-medium" for="category_id">Kategori</label>
                <select id="category_id" name="category_id" required class="form-select">
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected((int) old('category_id', $product->category_id) === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label small fw-medium" for="price">Harga (Rp)</label>
                    <input id="price" name="price" type="number" min="100" step="1" value="{{ old('price', $product->price ? (int) $product->price : '') }}" required class="form-control">
                    @error('price')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label class="form-label small fw-medium" for="stock">Stok</label>
                    <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required class="form-control">
                    @error('stock')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label class="form-label small fw-medium" for="unit">Satuan</label>
                    <input id="unit" name="unit" list="units" value="{{ old('unit', $product->unit) }}" required class="form-control">
                    <datalist id="units"><option value="kg"><option value="gram"><option value="ikat"><option value="sisir"><option value="butir"><option value="liter"></datalist>
                    @error('unit')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label class="form-label small fw-medium" for="weight">Berat per satuan (gram)</label>
                    <input id="weight" name="weight" type="number" min="0" value="{{ old('weight', $product->weight ?? 0) }}" required class="form-control">
                    @error('weight')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-medium" for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                @error('description')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label small fw-medium" for="image">Foto produk</label>
                @if ($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="" class="d-block mb-2 rounded border" style="height: 6rem;">
                @endif
                <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="form-control">
                <div class="form-text">JPG/PNG/WebP, maksimal 2 MB.{{ $editing ? ' Kosongkan jika tidak ingin mengganti.' : '' }}</div>
                @error('image')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" @checked(old('is_active', $product->is_active))>
                <label for="is_active" class="form-check-label small">Tampilkan di katalog (aktif)</label>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary fw-semibold px-4">{{ $editing ? 'Simpan Perubahan' : 'Simpan Produk' }}</button>
                <a href="{{ route('petani.products.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
            </div>
        </div>
    </form>
@endsection
