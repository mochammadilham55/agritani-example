@extends('layouts.guest')

@section('title', 'Masuk — AgriTani')

@section('content')
    <h1 class="text-xl font-bold text-brand-dark">Masuk</h1>
    <p class="mb-6 mt-1 text-sm text-gray-500">Masuk untuk berbelanja atau mengelola hasil tani Anda.</p>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-[#DDF0DA] px-4 py-3 text-sm text-[#1F5A22]">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('email')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" required
                   class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('password')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="remember" class="rounded border-line text-brand"> Ingat saya
        </label>
        <button type="submit" class="w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Masuk</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-brand hover:underline">Daftar</a>
    </p>
@endsection
