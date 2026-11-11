@php
    // Struktur menu per role. Item tanpa route terdaftar tampil nonaktif ("segera").
    // Format: ['label', 'route.name', 'icon'] atau ['section' => 'Judul'].
    $menus = [
        'pembeli' => [
            ['Dashboard', 'pembeli.dashboard', '🏠'],
            ['Pesanan Saya', 'pembeli.orders.index', '📦'],
            ['Ulasan Saya', 'pembeli.reviews.index', '⭐'],
            ['Profil', 'profile.edit', '👤'],
        ],
        'petani' => [
            ['Dashboard', 'petani.dashboard', '🏠'],
            ['section' => 'Kelola'],
            ['Produk Saya', 'petani.products.index', '🌾'],
            ['Pesanan Masuk', 'petani.orders.index', '📥'],
            ['section' => 'Transaksi'],
            ['Pengiriman', 'petani.shipments.index', '🚚'],
            ['Pembayaran', 'petani.payments.index', '💳'],
            ['section' => 'Lainnya'],
            ['Laporan', 'petani.reports.index', '📊'],
            ['Profil', 'profile.edit', '👤'],
        ],
        'admin' => [
            ['Dashboard', 'admin.dashboard', '🏠'],
            ['Daftar Petani', 'admin.farmers.index', '👥'],
            ['Daftar Produk', 'admin.products.index', '🥬'],
            ['Kelola Kategori', 'admin.categories.index', '🏷️'],
            ['Daftar Pesanan', 'admin.orders.index', '📦'],
            ['Moderasi Ulasan', 'admin.reviews.index', '💬'],
            ['Laporan', 'admin.reports.index', '📊'],
        ],
    ];
    $items = $menus[auth()->user()->role] ?? [];
@endphp

<nav class="space-y-1 p-4 text-sm">
    @foreach ($items as $item)
        @if (isset($item['section']))
            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-white/50">{{ $item['section'] }}</p>
        @else
            @php
                [$label, $route, $icon] = $item;
                $exists = Route::has($route);
                // Dashboard aktif hanya di halamannya sendiri; menu lain aktif juga di sub-halamannya.
                $active = $exists && (\Illuminate\Support\Str::endsWith($route, '.dashboard')
                    ? request()->routeIs($route)
                    : request()->routeIs($route, \Illuminate\Support\Str::beforeLast($route, '.').'.*'));
            @endphp
            @if ($exists)
                <a href="{{ route($route) }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 font-medium transition',
                       'bg-white/15 text-white' => $active,
                       'text-white/80 hover:bg-white/10 hover:text-white' => ! $active,
                   ])>
                    <span aria-hidden="true">{{ $icon }}</span>{{ $label }}
                </a>
            @else
                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-white/35" title="Segera hadir">
                    <span aria-hidden="true">{{ $icon }}</span>{{ $label }}
                </span>
            @endif
        @endif
    @endforeach
</nav>
