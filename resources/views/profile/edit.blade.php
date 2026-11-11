@extends('layouts.app')

@section('title', 'Profil')
@section('heading', 'Profil')

@section('content')
    @php $input = 'w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20'; @endphp

    <div class="grid max-w-4xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4 rounded-xl border border-line bg-white p-6">
            @csrf @method('PUT')
            <h2 class="font-bold text-brand-dark">Data Diri</h2>

            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-role-bg text-2xl">
                    @if ($user->photo)<img src="{{ asset('storage/'.$user->photo) }}" alt="Foto profil" class="h-full w-full object-cover">@else 👤 @endif
                </div>
                <div class="flex-1">
                    <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="block w-full text-xs">
                    @error('photo')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
                </div>
            </div>

            @foreach ([['name', 'Nama', 'text'], ['email', 'Email', 'email'], ['phone', 'No. HP', 'text']] as [$f, $label, $type])
                <div>
                    <label for="{{ $f }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>
                    <input id="{{ $f }}" name="{{ $f }}" type="{{ $type }}" value="{{ old($f, $user->$f) }}" @required($f !== 'phone') class="{{ $input }}">
                    @error($f)<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
                </div>
            @endforeach

            <div>
                <label for="address" class="mb-1 block text-sm font-medium">Alamat</label>
                <textarea id="address" name="address" rows="3" class="{{ $input }}">{{ old('address', $user->address) }}</textarea>
                @error('address')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>

            <button class="rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Simpan</button>
        </form>

        <form method="POST" action="{{ route('profile.password') }}" class="h-fit space-y-4 rounded-xl border border-line bg-white p-6">
            @csrf @method('PUT')
            <h2 class="font-bold text-brand-dark">Ubah Password</h2>
            @foreach ([['current_password', 'Password saat ini'], ['password', 'Password baru'], ['password_confirmation', 'Konfirmasi password baru']] as [$f, $label])
                <div>
                    <label for="{{ $f }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>
                    <input id="{{ $f }}" name="{{ $f }}" type="password" required class="{{ $input }}">
                    @error($f)<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <button class="rounded-lg border border-line px-6 py-2.5 text-sm font-semibold hover:bg-surface">Ganti Password</button>
        </form>
    </div>
@endsection
