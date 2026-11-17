@php
    // Struktur menu per role. Item tanpa route terdaftar tampil nonaktif ("segera").
    // Format: ['label', 'route.name'] atau ['section' => 'Judul'].
    $menus = [
        'pembeli' => [
            ['Dashboard', 'pembeli.dashboard'],
            ['Pesanan Saya', 'pembeli.orders.index'],
            ['Profil', 'profile.edit'],
        ],
        'petani' => [
            ['Dashboard', 'petani.dashboard'],
            ['section' => 'Kelola'],
            ['Produk Saya', 'petani.products.index'],
            ['Pesanan Masuk', 'petani.orders.index'],
            ['section' => 'Transaksi'],
            ['Pengiriman', 'petani.shipments.index'],
            ['Pembayaran', 'petani.payments.index'],
            ['Laporan Penjualan', 'petani.reports.index'],
            ['section' => 'Lainnya'],
            ['Profil', 'profile.edit'],
        ],
        'admin' => [
            ['Dashboard', 'admin.dashboard'],
            ['Daftar Petani', 'admin.farmers.index'],
            ['Kelola Kategori', 'admin.categories.index'],
            ['Daftar Pesanan', 'admin.orders.index'],
            ['Laporan Penjualan', 'admin.reports.index'],
        ],
    ];
    $items = $menus[auth()->user()->role] ?? [];
@endphp

<nav class="nav flex-column px-3 pb-4 small">
    @foreach ($items as $item)
        @if (isset($item['section']))
            <p class="sidebar-section mb-0">{{ $item['section'] }}</p>
        @else
            @php
                [$label, $route] = $item;
                $exists = Route::has($route);
                // Dashboard aktif hanya di halamannya sendiri; menu lain aktif juga di sub-halamannya.
                $active = $exists && (\Illuminate\Support\Str::endsWith($route, '.dashboard')
                    ? request()->routeIs($route)
                    : request()->routeIs($route, \Illuminate\Support\Str::beforeLast($route, '.').'.*'));
            @endphp
            @if ($exists)
                <a href="{{ route($route) }}" @class(['nav-link d-flex align-items-center gap-3', 'active' => $active])>
                    {{ $label }}
                </a>
            @else
                <span class="nav-link disabled d-flex align-items-center gap-3" title="Segera hadir">
                    {{ $label }}
                </span>
            @endif
        @endif
    @endforeach
</nav>
