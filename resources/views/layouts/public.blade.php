<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AgriTani — Hasil Tani Langsung dari Petani')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-surface font-sans text-gray-800 antialiased">
    @php
        $user = auth()->user();
        $cartCount = $user?->isPembeli() ? $user->carts()->count() : 0;
    @endphp
    <header class="sticky top-0 z-10 bg-brand-dark text-white">
        <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
            <a href="{{ route('home') }}" class="text-xl font-extrabold">🌱 AgriTani</a>
            <form action="{{ route('home') }}" method="GET" class="hidden flex-1 sm:block">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari hasil tani…"
                       class="w-full max-w-md rounded-lg border-0 bg-white px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-chart">
            </form>
            <nav class="ml-auto flex items-center gap-3 text-sm font-medium">
                @if ($user?->isPembeli())
                    <a href="{{ route('pembeli.cart.index') }}" class="relative rounded-lg px-3 py-1.5 hover:bg-white/10">
                        🛒 Keranjang
                        @if ($cartCount)<span class="ml-1 rounded-full bg-white px-1.5 text-xs font-bold text-brand-dark">{{ $cartCount }}</span>@endif
                    </a>
                @endif
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-white/15 px-3 py-1.5 hover:bg-white/25">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-1.5 hover:bg-white/10">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-brand px-3 py-1.5 hover:bg-brand-chart">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-[#DDF0DA] px-4 py-3 text-sm text-[#1F5A22]">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-[#FBE1DE] px-4 py-3 text-sm text-[#8E1F14]">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-line py-6 text-center text-xs text-gray-400">© {{ date('Y') }} AgriTani — Marketplace hasil pertanian</footer>
</body>
</html>
