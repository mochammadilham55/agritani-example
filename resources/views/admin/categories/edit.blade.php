@extends('layouts.app')

@section('title', 'Edit Kategori')
@section('heading', 'Edit Kategori')

@section('content')
    @php $input = 'w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20'; @endphp

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="max-w-lg space-y-4 rounded-xl border border-line bg-white p-6">
        @csrf @method('PUT')
        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Nama</label>
            <input id="name" name="name" value="{{ old('name', $category->name) }}" required class="{{ $input }}">
            @error('name')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="description" class="mb-1 block text-sm font-medium">Deskripsi</label>
            <textarea id="description" name="description" rows="3" class="{{ $input }}">{{ old('description', $category->description) }}</textarea>
            @error('description')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="image" class="mb-1 block text-sm font-medium">Gambar</label>
            @if ($category->image)<img src="{{ asset('storage/'.$category->image) }}" alt="" class="mb-2 h-20 rounded-lg border border-line">@endif
            <input id="image" type="file" name="image" accept="image/png,image/jpeg,image/webp" class="block w-full text-xs">
            @error('image')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-3">
            <button class="rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Simpan</button>
            <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-line px-6 py-2.5 text-sm hover:bg-surface">Batal</a>
        </div>
    </form>
@endsection
