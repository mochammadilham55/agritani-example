@extends('layouts.app')

@php $editing = $product->exists; @endphp

@section('title', $editing ? 'Edit Produk' : 'Tambah Produk')
@section('heading', $editing ? 'Edit Produk' : 'Tambah Produk')

@section('content')
    @php
        $input = 'w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20';
    @endphp

    <form method="POST" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-xl border border-line bg-white p-6"
          action="{{ $editing ? route('petani.products.update', $product) : route('petani.products.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div>
            <label class="mb-1 block text-sm font-medium" for="name">Nama produk</label>
            <input id="name" name="name" value="{{ old('name', $product->name) }}" required class="{{ $input }}">
            @error('name')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="category_id">Kategori</label>
            <select id="category_id" name="category_id" required class="{{ $input }}">
                <option value="">Pilih kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected((int) old('category_id', $product->category_id) === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('category_id')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium" for="price">Harga (Rp)</label>
                <input id="price" name="price" type="number" min="100" step="1" value="{{ old('price', $product->price ? (int) $product->price : '') }}" required class="{{ $input }}">
                @error('price')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="stock">Stok</label>
                <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required class="{{ $input }}">
                @error('stock')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="unit">Satuan</label>
                <input id="unit" name="unit" list="units" value="{{ old('unit', $product->unit) }}" required class="{{ $input }}">
                <datalist id="units"><option value="kg"><option value="gram"><option value="ikat"><option value="sisir"><option value="butir"><option value="liter"></datalist>
                @error('unit')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="weight">Berat per satuan (gram)</label>
                <input id="weight" name="weight" type="number" min="0" value="{{ old('weight', $product->weight ?? 0) }}" required class="{{ $input }}">
                @error('weight')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="4" class="{{ $input }}">{{ old('description', $product->description) }}</textarea>
            @error('description')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="image">Foto produk</label>
            @if ($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="" class="mb-2 h-24 rounded-lg border border-line">
            @endif
            <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm">
            <p class="mt-1 text-xs text-gray-400">JPG/PNG/WebP, maksimal 2 MB.{{ $editing ? ' Kosongkan jika tidak ingin mengganti.' : '' }}</p>
            @error('image')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" class="rounded border-line text-brand" @checked(old('is_active', $product->is_active))>
            Tampilkan di katalog (aktif)
        </label>

        <div class="flex gap-3 pt-2">
            <button class="rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">{{ $editing ? 'Simpan Perubahan' : 'Simpan Produk' }}</button>
            <a href="{{ route('petani.products.index') }}" class="rounded-lg border border-line px-6 py-2.5 text-sm hover:bg-surface">Batal</a>
        </div>
    </form>
@endsection
