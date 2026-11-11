@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('heading', 'Laporan Penjualan')

@section('content')
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div><label class="mb-1 block text-xs text-gray-500" for="from">Dari</label>
            <input id="from" type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-line bg-white px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs text-gray-500" for="to">Sampai</label>
            <input id="to" type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-line bg-white px-3 py-2 text-sm"></div>
        <button class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Terapkan</button>
        <a href="{{ route('petani.reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
           class="rounded-lg border border-line bg-white px-4 py-2 text-sm hover:bg-surface">⬇ Export CSV</a>
        @error('to')<p class="w-full text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
    </form>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Pendapatan" :value="'Rp '.number_format($revenue, 0, ',', '.')" />
        <x-stat-card label="Pesanan Selesai" :value="$orders" />
        <x-stat-card label="Produk Terjual" :value="number_format($qty, 0, ',', '.')" />
        <x-stat-card label="Rata-rata / Pesanan" :value="'Rp '.number_format($average, 0, ',', '.')" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-line bg-white p-5 lg:col-span-2">
            <h2 class="mb-4 font-bold text-brand-dark">Penjualan per Hari</h2>
            @php $max = max(1, $chart->max('total')); $many = $chart->count() > 31; @endphp
            <div class="flex h-48 items-end gap-px overflow-x-auto" role="img" aria-label="Grafik penjualan per hari">
                @foreach ($chart as $bar)
                    <div class="flex h-full min-w-2 flex-1 flex-col justify-end" title="{{ $bar['label'] }}: Rp {{ number_format($bar['total'], 0, ',', '.') }}">
                        <div class="w-full rounded-t bg-brand-chart" style="height: {{ $bar['total'] > 0 ? max(3, $bar['total'] / $max * 100) : 0 }}%"></div>
                        @unless ($many)<span class="mt-1 text-center text-[9px] text-gray-400">{{ $bar['label'] }}</span>@endunless
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-xl border border-line bg-white p-5">
            <h2 class="mb-3 font-bold text-brand-dark">Produk Terlaris</h2>
            <ol class="space-y-2 text-sm">
                @forelse ($topProducts as $t)
                    <li class="flex justify-between gap-2"><span>{{ $loop->iteration }}. {{ $t->name }}</span>
                        <span class="text-gray-500">{{ $t->qty }} {{ $t->unit }}</span></li>
                @empty
                    <li class="py-6 text-center text-gray-400">Belum ada penjualan.</li>
                @endforelse
            </ol>
        </section>
    </div>

    <section class="mt-8">
        <h2 class="mb-3 font-bold text-brand-dark">Rincian Transaksi</h2>
        <div class="overflow-x-auto rounded-xl border border-line bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-4 py-3">Pesanan</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Pembeli</th><th class="px-4 py-3">Produk</th><th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-right">Subtotal</th></tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($transactions as $t)
                        <tr>
                            <td class="px-4 py-3">#{{ $t->order_id }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $t->buyer }}</td>
                            <td class="px-4 py-3">{{ $t->product }}</td>
                            <td class="px-4 py-3 text-right">{{ $t->quantity }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada transaksi pada rentang ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $transactions->links() }}</div>
    </section>
@endsection
