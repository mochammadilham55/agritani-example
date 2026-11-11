@extends('layouts.app')

@section('title', 'Moderasi Ulasan')
@section('heading', 'Moderasi Ulasan')

@section('content')
    <div class="mb-5 flex flex-wrap gap-2 text-sm">
        @foreach (['menunggu' => 'Menunggu'.($pendingCount ? " ({$pendingCount})" : ''), 'disetujui' => 'Disetujui', 'semua' => 'Semua'] as $key => $label)
            <a href="{{ route('admin.reviews.index', ['status' => $key]) }}"
               @class(['rounded-full border px-3 py-1', 'border-brand bg-role-bg font-semibold text-brand-dark' => $status === $key, 'border-line bg-white' => $status !== $key])>{{ $label }}</a>
        @endforeach
    </div>

    <div class="divide-y divide-line rounded-xl border border-line bg-white">
        @forelse ($reviews as $r)
            <div class="px-5 py-4 text-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-60 flex-1">
                        <p class="font-semibold">{{ $r->product->name }}
                            <span class="ml-2 font-normal text-amber-600">{{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5 - $r->rating) }}</span></p>
                        <p class="text-xs text-gray-500">oleh {{ $r->user->name }} · petani {{ $r->product->farmer?->name }} · {{ $r->created_at->format('d M Y') }}</p>
                        <p class="mt-2 text-gray-700">{{ $r->comment ?: '(tanpa komentar)' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($r->is_approved)
                            <x-badge status="disetujui" label="Tampil" />
                        @else
                            <form method="POST" action="{{ route('admin.reviews.approve', $r) }}">@csrf @method('PATCH')
                                <button class="rounded-lg bg-brand px-3 py-1 text-xs font-semibold text-white hover:bg-brand-dark">Setujui</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.reviews.reject', $r) }}" onsubmit="return confirm('Tolak dan hapus ulasan ini?')">@csrf @method('DELETE')
                            <button class="rounded-lg border border-[#8E1F14]/30 px-3 py-1 text-xs font-semibold text-[#8E1F14] hover:bg-[#FBE1DE]">Tolak</button></form>
                    </div>
                </div>
            </div>
        @empty
            <p class="px-5 py-12 text-center text-sm text-gray-400">Tidak ada ulasan.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
@endsection
