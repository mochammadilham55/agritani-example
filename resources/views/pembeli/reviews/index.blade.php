@extends('layouts.app')

@section('title', 'Ulasan Saya')
@section('heading', 'Ulasan Saya')

@section('content')
    <section>
        <h2 class="mb-3 font-bold text-brand-dark">Menunggu Diulas</h2>
        <div class="divide-y divide-line rounded-xl border border-line bg-white">
            @forelse ($pending as $item)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                    <span><span class="font-medium">{{ $item->product->name }}</span>
                        <span class="ml-2 text-xs text-gray-400">Pesanan #{{ $item->order_id }}</span></span>
                    <a href="{{ route('pembeli.reviews.create', [$item->order_id, $item->product]) }}"
                       class="rounded-lg bg-brand px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-dark">Beri Ulasan</a>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">Tidak ada produk yang menunggu ulasan.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-8">
        <h2 class="mb-3 font-bold text-brand-dark">Ulasan yang Saya Tulis</h2>
        <div class="divide-y divide-line rounded-xl border border-line bg-white">
            @forelse ($reviews as $r)
                <div class="px-5 py-4 text-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold">{{ $r->product->name }}
                            <span class="ml-2 font-normal text-amber-600">{{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5 - $r->rating) }}</span></p>
                        <div class="flex items-center gap-2">
                            <x-badge :status="$r->is_approved ? 'disetujui' : 'menunggu'" :label="$r->is_approved ? 'Tampil' : 'Menunggu moderasi'" />
                            <a href="{{ route('pembeli.reviews.edit', $r) }}" class="rounded-lg border border-line px-3 py-1 text-xs hover:bg-surface">Edit</a>
                        </div>
                    </div>
                    @if ($r->comment)<p class="mt-1 text-gray-600">{{ $r->comment }}</p>@endif
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">Belum ada ulasan.</p>
            @endforelse
        </div>
    </section>
@endsection
