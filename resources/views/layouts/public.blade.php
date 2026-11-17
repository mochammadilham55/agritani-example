<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
    <title>@yield('title', 'AgriTani — Hasil Tani Langsung dari Petani')</title>
</head>
<body class="d-flex flex-column min-vh-100">
    @php
        $user = auth()->user();
        $cartCount = $user?->isPembeli() ? $user->carts()->count() : 0;
    @endphp
    <header class="sticky-top bg-brand-dark text-white" style="z-index: 1020;">
        <div class="container d-flex align-items-center gap-3 py-2">
            <a href="{{ route('home') }}" class="fs-4 fw-bold text-white text-decoration-none">AgriTani</a>
            <form action="{{ route('home') }}" method="GET" class="d-none d-sm-block flex-grow-1" style="max-width: 28rem;">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari hasil tani…" class="form-control form-control-sm" aria-label="Cari produk">
            </form>
            <nav class="ms-auto d-flex align-items-center gap-2 small fw-medium">
                @if ($user?->isPembeli())
                    <a href="{{ route('pembeli.cart.index') }}" class="btn btn-sm text-white">
                        Keranjang
                        @if ($cartCount)<span class="badge rounded-pill bg-white text-brand-dark ms-1">{{ $cartCount }}</span>@endif
                    </a>
                @endif
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm text-white">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-primary border-white border-opacity-25">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="container flex-grow-1 py-4">
        @include('layouts.partials.alerts')
        @yield('content')
    </main>

    <footer class="border-top py-3 text-center small text-secondary">© {{ date('Y') }} AgriTani — Marketplace hasil pertanian</footer>

    @include('layouts.partials.scripts')
</body>
</html>
