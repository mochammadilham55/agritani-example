@extends('layouts.app')

@section('title', 'Pesanan Saya')
@section('heading', 'Pesanan Saya')

@section('content')
    <div class="mb-5 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('pembeli.orders.index') }}"
           @class(['rounded-full border px-3 py-1', 'border-brand bg-role-bg font-semibold text-brand-dark' => ! request('status'), 'border-line bg-white' => request('status')])>Semua</a>
        @foreach (\App\Models\Order::STATUSES as $s)
            <a href="{{ route('pembeli.orders.index', ['status' => $s]) }}"
               @class(['rounded-full border px-3 py-1', 'border-brand bg-role-bg font-semibold text-brand-dark' => request('status') === $s, 'border-line bg-white' => request('status') !== $s])>{{ ucfirst($s) }}</a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($orders as $order)
            <a href="{{ route('pembeli.orders.show', $order) }}" class="block rounded-xl border border-line bg-white p-4 transition hover:shadow-md">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="font-semibold">Pesanan #{{ $order->id }}
                        <span class="ml-2 text-xs font-normal text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</span></p>
                    <x-badge :status="$order->status" />
                </div>
                <p class="mt-1 truncate text-sm text-gray-600">
                    {{ $order->items->map(fn ($i) => $i->product->name.' ×'.$i->quantity)->join(', ') }}
                </p>
                <div class="mt-2 flex items-center justify-between text-sm">
                    <span class="font-bold text-brand-dark">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
                    @if ($order->payment)
                        <span class="text-xs text-gray-500">{{ $order->payment->method === 'cod' ? 'COD' : 'Transfer' }} · <x-badge :status="$order->payment->status" /></span>
                    @endif
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-dashed border-line bg-white py-16 text-center text-gray-400">Belum ada pesanan.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
