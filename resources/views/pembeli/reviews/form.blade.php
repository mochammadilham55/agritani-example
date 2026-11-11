@extends('layouts.app')

@php $editing = $review->exists; @endphp

@section('title', 'Ulasan Produk')
@section('heading', $editing ? 'Edit Ulasan' : 'Beri Ulasan')

@section('content')
    <form method="POST" class="max-w-xl space-y-5 rounded-xl border border-line bg-white p-6"
          action="{{ $editing ? route('pembeli.reviews.update', $review) : route('pembeli.reviews.store', [$order, $product]) }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div>
            <p class="font-semibold">{{ $product->name }}</p>
            <p class="text-xs text-gray-400">Pesanan #{{ $order->id }}</p>
        </div>

        <fieldset>
            <legend class="mb-2 text-sm font-medium">Rating</legend>
            <div class="flex flex-row-reverse justify-end gap-1">
                @foreach (range(5, 1) as $n)
                    <input type="radio" id="star{{ $n }}" name="rating" value="{{ $n }}" class="sr-only" @checked((int) old('rating', $review->rating) === $n)>
                    <label for="star{{ $n }}" title="{{ $n }} bintang"
                           class="cursor-pointer text-3xl text-gray-300 hover:text-amber-500 [input:checked~&]:text-amber-500">★</label>
                @endforeach
            </div>
            @error('rating')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </fieldset>

        <div>
            <label for="comment" class="mb-1 block text-sm font-medium">Komentar (opsional)</label>
            <textarea id="comment" name="comment" rows="4" maxlength="1000"
                      class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">{{ old('comment', $review->comment) }}</textarea>
            @error('comment')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>

        @if ($editing)<p class="text-xs text-gray-500">Setelah diubah, ulasan akan ditinjau ulang oleh admin.</p>@endif

        <div class="flex gap-3">
            <button class="rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Kirim Ulasan</button>
            <a href="{{ route('pembeli.reviews.index') }}" class="rounded-lg border border-line px-6 py-2.5 text-sm hover:bg-surface">Batal</a>
        </div>
    </form>
@endsection
