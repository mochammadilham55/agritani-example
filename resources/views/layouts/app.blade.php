<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — AgriTani</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface font-sans text-gray-800 antialiased">
    <input type="checkbox" id="sidebar-toggle" class="peer sr-only">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full overflow-y-auto bg-brand-dark transition-transform peer-checked:translate-x-0 md:translate-x-0">
        <a href="{{ route('home') }}" class="block px-7 pb-2 pt-6 text-xl font-extrabold text-white">🌱 AgriTani</a>
        @include('layouts.partials.sidebar')
    </aside>
    <label for="sidebar-toggle" class="fixed inset-0 z-20 hidden bg-black/40 peer-checked:block md:!hidden"></label>

    <div class="md:pl-64">
        {{-- Topbar --}}
        <header class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-line bg-white px-4 md:px-8">
            <div class="flex items-center gap-3">
                <label for="sidebar-toggle" class="cursor-pointer rounded-lg border border-line px-2.5 py-1.5 md:hidden" aria-label="Buka menu">☰</label>
                <h1 class="text-lg font-bold text-brand-dark">@yield('heading', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm font-medium sm:inline">{{ auth()->user()->name }}</span>
                <span class="rounded-full bg-role-bg px-2.5 py-0.5 text-xs font-semibold capitalize text-brand-dark">{{ auth()->user()->role }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg border border-line px-3 py-1.5 text-sm hover:bg-surface">Keluar</button>
                </form>
            </div>
        </header>

        <main class="p-4 md:p-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-[#DDF0DA] px-4 py-3 text-sm text-[#1F5A22]">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg bg-[#FBE1DE] px-4 py-3 text-sm text-[#8E1F14]">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
