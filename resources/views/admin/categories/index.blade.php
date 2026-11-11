@extends('layouts.app')

@section('title', 'Kelola Kategori')
@section('heading', 'Kelola Kategori')

@section('content')
    @php $input = 'w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20'; @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="h-fit space-y-3 rounded-xl border border-line bg-white p-5">
            @csrf
            <h2 class="font-bold text-brand-dark">Tambah Kategori</h2>
            <div>
                <input name="name" value="{{ old('name') }}" placeholder="Nama kategori" required class="{{ $input }}">
                @error('name')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>
            <div>
                <textarea name="description" rows="2" placeholder="Deskripsi (opsional)" class="{{ $input }}">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>
            <div>
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="block w-full text-xs">
                @error('image')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>
            <button class="w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Simpan</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-line bg-white lg:col-span-2">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Produk</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($categories as $c)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $c->name }}<span class="block text-xs font-normal text-gray-400">{{ $c->description }}</span></td>
                            <td class="px-4 py-3">{{ $c->products_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $c) }}" class="rounded-lg border border-line px-3 py-1 text-xs hover:bg-surface">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Hapus kategori {{ $c->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-[#8E1F14]/30 px-3 py-1 text-xs text-[#8E1F14] hover:bg-[#FBE1DE]">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-gray-400">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
