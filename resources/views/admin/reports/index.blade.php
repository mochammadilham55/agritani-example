@extends('layouts.app')

@section('title', 'Laporan Platform')
@section('heading', 'Laporan')

@section('content')
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div><label class="mb-1 block text-xs text-gray-500" for="from">Dari</label>
            <input id="from" type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-line bg-white px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-xs text-gray-500" for="to">Sampai</label>
            <input id="to" type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-line bg-white px-3 py-2 text-sm"></div>
        <button class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Terapkan</button>
        <a href="{{ route('admin.reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="rounded-lg border border-line bg-white px-4 py-2 text-sm hover:bg-surface">⬇ Export CSV</a>
        @error('to')<p class="w-full text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
    </form>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Pendapatan Total" :value="'Rp '.number_format($revenue, 0, ',', '.')" hint="Nilai barang, pesanan selesai" />
        <x-stat-card label="Pesanan Selesai" :value="$completed" />
        <x-stat-card label="Produk Terjual" :value="number_format($qty, 0, ',', '.')" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-line bg-white p-5 lg:col-span-2">
            <h2 class="mb-4 font-bold text-brand-dark">Pendapatan per Hari</h2>
            @php $max = max(1, $chart->max('total')); $many = $chart->count() > 31; @endphp
            <div class="flex h-48 items-end gap-px overflow-x-auto" role="img" aria-label="Grafik pendapatan per hari">
                @foreach ($chart as $bar)
                    <div class="flex h-full min-w-2 flex-1 flex-col justify-end" title="{{ $bar['label'] }}: Rp {{ number_format($bar['total'], 0, ',', '.') }}">
                        <div class="w-full rounded-t bg-brand-chart" style="height: {{ $bar['total'] > 0 ? max(3, $bar['total'] / $max * 100) : 0 }}%"></div>
                        @unless ($many)<span class="mt-1 text-center text-[9px] text-gray-400">{{ $bar['label'] }}</span>@endunless
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-xl border border-line bg-white p-5">
            <h2 class="mb-3 font-bold text-brand-dark">Pesanan per Status</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($byStatus as $status => $total)
                    <li class="flex items-center justify-between"><x-badge :status="$status" /><span class="font-semibold">{{ $total }}</span></li>
                @endforeach
            </ul>
        </section>
    </div>

    <section class="mt-8">
        <h2 class="mb-3 font-bold text-brand-dark">Produk Terlaris Platform</h2>
        <div class="overflow-x-auto rounded-xl border border-line bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                    <tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Produk</th><th class="px-4 py-3">Petani</th><th class="px-4 py-3 text-right">Terjual</th><th class="px-4 py-3 text-right">Pendapatan</th></tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($topProducts as $t)
                        <tr>
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium">{{ $t->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $t->farmer }}</td>
                            <td class="px-4 py-3 text-right">{{ $t->qty }} {{ $t->unit }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada penjualan pada rentang ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
