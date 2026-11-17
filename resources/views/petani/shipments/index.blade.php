@extends('layouts.app')

@section('title', 'Pengiriman')
@section('heading', 'Pengiriman')

@section('content')
    @php $input = 'rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20'; @endphp

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-[#FBE1DE] px-4 py-3 text-sm text-[#8E1F14]">{{ $errors->first() }}</div>
    @endif

    <div class="space-y-4">
        @forelse ($orders as $o)
            <section class="rounded-xl border border-line bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <a href="{{ route('petani.orders.show', $o->id) }}" class="font-semibold hover:text-brand">Pesanan #{{ $o->id }}</a>
                        <span class="ml-2 text-sm text-gray-500">{{ $o->buyer->name }}</span>
                    </div>
                    <x-badge :status="$o->status" />
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ $o->items->map(fn ($i) => $i->product->name.' ×'.$i->quantity)->join(', ') }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $o->shipping_address }}</p>

                <form method="POST" action="{{ route('petani.shipments.update', $o->id) }}" class="mt-4 grid gap-3 sm:grid-cols-3">
                    @csrf @method('PUT')
                    <input name="courier" value="{{ old('courier', $o->courier) }}" placeholder="Kurir (JNE, J&T, …)" required class="{{ $input }}">
                    <input name="tracking_number" value="{{ old('tracking_number', $o->tracking_number) }}" placeholder="No. resi" required class="{{ $input }}">
                    <button class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">{{ $o->status === 'diproses' ? 'Kirim Pesanan' : 'Perbarui' }}</button>
                </form>
            </section>
        @empty
            <div class="rounded-xl border border-dashed border-line bg-white py-16 text-center text-gray-400">Tidak ada pesanan yang perlu dikirim.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
