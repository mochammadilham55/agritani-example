<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
    <title>@yield('title', 'Dashboard') — Tanimart</title>
</head>
<body>
    <div class="d-md-flex min-vh-100">
        {{-- Sidebar: laci geser di layar kecil, tetap tampil di layar >= md --}}
        <aside class="sidebar offcanvas-md offcanvas-start" tabindex="-1" id="sidebar" aria-label="Menu utama">
            <div class="sidebar-panel bg-brand-dark h-100 w-100">
                <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-2">
                    <a href="{{ route('home') }}" class="fs-4 fw-bold text-white text-decoration-none">🌱 Tanimart</a>
                    <button type="button" class="btn-close btn-close-white d-md-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
                </div>
                @include('layouts.partials.sidebar')
            </div>
        </aside>

        <div class="flex-grow-1 min-w-0">
            {{-- Topbar --}}
            <header class="topbar sticky-top d-flex align-items-center justify-content-between bg-white border-bottom px-3 px-md-4">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm d-md-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">Menu</button>
                    <h1 class="h5 fw-bold text-brand-dark mb-0">@yield('heading', 'Dashboard')</h1>
                </div>
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <span class="d-none d-sm-inline small fw-medium">{{ auth()->user()->name }}</span>
                    <span class="badge rounded-pill bg-role-bg text-brand-dark text-capitalize">{{ auth()->user()->role }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="p-3 p-md-4">
                @include('layouts.partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
