@extends('layouts.guest')

@section('title', 'Atur Ulang Password — AgriTani')

@section('content')
    <h1 class="text-xl font-bold text-brand-dark">Atur Ulang Password</h1>
    <p class="mb-6 mt-1 text-sm text-gray-500">Buat password baru untuk akun Anda (minimal 8 karakter).</p>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        @php $input = 'w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20'; @endphp
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="{{ $input }}">
            @error('email')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password baru</label>
            <x-password-input id="password" name="password" required autofocus />
            @error('password')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium">Konfirmasi password baru</label>
            <x-password-input id="password_confirmation" name="password_confirmation" required />
        </div>
        <button type="submit" class="w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Simpan Password Baru</button>
    </form>
@endsection
