@extends('layouts.guest')

@section('title', 'Lupa Password — AgriTani')

@section('content')
    <h1 class="text-xl font-bold text-brand-dark">Lupa Password</h1>
    <p class="mb-6 mt-1 text-sm text-gray-500">Masukkan email akun Anda. Kami kirim tautan untuk mengatur ulang password.</p>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-[#DDF0DA] px-4 py-3 text-sm text-[#1F5A22]">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('email')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Kirim Tautan Reset</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">← Kembali ke halaman masuk</a>
    </p>
@endsection
