@extends('layouts.app')

@section('title', 'Edit Kategori')
@section('heading', 'Edit Kategori')

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="card" style="max-width: 32rem;">
        @csrf @method('PUT')
        <div class="card-body p-4">
            <div class="mb-3">
                <label for="name" class="form-label small fw-medium">Nama</label>
                <input id="name" name="name" value="{{ old('name', $category->name) }}" required class="form-control">
                @error('name')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label small fw-medium">Deskripsi</label>
                <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea>
                @error('description')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label for="image" class="form-label small fw-medium">Gambar</label>
                @if ($category->image)<img src="{{ asset('storage/'.$category->image) }}" alt="" class="d-block mb-2 rounded border" style="height: 5rem;">@endif
                <input id="image" type="file" name="image" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
                @error('image')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary fw-semibold px-4">Simpan</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
            </div>
        </div>
    </form>
@endsection
