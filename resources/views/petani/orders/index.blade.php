@extends('layouts.app')

@section('title', 'Pesanan Masuk')
@section('heading', 'Pesanan Masuk')

@section('content')
    <div class="mb-5 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('petani.orders.index') }}"
           @class(['rounded-full border px-3 py-1', 'border-brand bg-role-bg font-semibold text-brand-dark' => ! request('status'), 'border-line bg-white' => request('status')])>Semua</a>
        @foreach (\App\Models\Order::STATUSES as $s)
            <a href="{{ route('petani.orders.index', ['status' => $s]) }}"
               @class(['rounded-full border px-3 py-1', 'border-brand bg-role-bg font-semibold text-brand-dark' => request('status') === $s, 'border-line bg-white' => request('status') !== $s])>{{ ucfirst($s) }}</a>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                <tr><th class="px-4 py-3">Pesanan</th><th class="px-4 py-3">Produk</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Bayar</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($orders as $o)
                    <tr>
                        <td class="px-4 py-3 font-medium">#{{ $o->id }}<span class="block text-xs font-normal text-gray-500">{{ $o->buyer->name }} · {{ $o->created_at->format('d M Y') }}</span></td>
                        <td class="max-w-56 truncate px-4 py-3 text-gray-600">{{ $o->items->map(fn ($i) => $i->product->name.' ×'.$i->quantity)->join(', ') }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($o->grandTotal(), 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $o->payment?->method === 'cod' ? 'COD' : 'Transfer' }} <x-badge :status="$o->payment?->status ?? 'menunggu'" /></td>
                        <td class="px-4 py-3"><x-badge :status="$o->status" /></td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('petani.orders.show', $o->id) }}" class="rounded-lg border border-line px-3 py-1 text-xs hover:bg-surface">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
