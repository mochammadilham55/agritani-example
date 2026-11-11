@extends('layouts.app')

@section('title', 'Daftar Pesanan')
@section('heading', 'Daftar Pesanan')

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="No. pesanan, pembeli, atau petani…" class="min-w-48 flex-1 rounded-lg border border-line bg-white px-3 py-2 text-sm">
        <select name="status" class="rounded-lg border border-line bg-white px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach (\App\Models\Order::STATUSES as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border border-line bg-white px-4 py-2 text-sm hover:bg-surface">Cari</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                <tr><th class="px-4 py-3">Pesanan</th><th class="px-4 py-3">Petani</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Bayar</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($orders as $o)
                    <tr>
                        <td class="px-4 py-3 font-medium">#{{ $o->id }}<span class="block text-xs font-normal text-gray-500">{{ $o->buyer->name }} · {{ $o->created_at->format('d M Y') }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $o->items->map(fn ($i) => $i->product->farmer?->name)->unique()->join(', ') }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($o->grandTotal(), 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $o->payment?->method === 'cod' ? 'COD' : 'Transfer' }} <x-badge :status="$o->payment?->status ?? 'menunggu'" /></td>
                        <td class="px-4 py-3"><x-badge :status="$o->status" /></td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.orders.show', $o) }}" class="rounded-lg border border-line px-3 py-1 text-xs hover:bg-surface">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
