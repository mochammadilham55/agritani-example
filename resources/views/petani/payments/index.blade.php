@extends('layouts.app')

@section('title', 'Pembayaran')
@section('heading', 'Pembayaran')

@section('content')
    <div class="mb-5 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('petani.payments.index') }}"
           @class(['rounded-full border px-3 py-1', 'border-brand bg-role-bg font-semibold text-brand-dark' => ! request('status'), 'border-line bg-white' => request('status')])>Semua</a>
        @foreach (['menunggu', 'dikonfirmasi', 'ditolak'] as $s)
            <a href="{{ route('petani.payments.index', ['status' => $s]) }}"
               @class(['rounded-full border px-3 py-1', 'border-brand bg-role-bg font-semibold text-brand-dark' => request('status') === $s, 'border-line bg-white' => request('status') !== $s])>{{ ucfirst($s) }}</a>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
                <tr><th class="px-4 py-3">Pesanan</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3">Metode</th><th class="px-4 py-3">Bukti</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($payments as $p)
                    <tr>
                        <td class="px-4 py-3 font-medium"><a href="{{ route('petani.orders.show', $p->order_id) }}" class="hover:text-brand">#{{ $p->order_id }}</a>
                            <span class="block text-xs font-normal text-gray-500">{{ $p->order->buyer->name }}</span></td>
                        <td class="px-4 py-3">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $p->method === 'cod' ? 'COD' : 'Transfer' }}</td>
                        <td class="px-4 py-3">
                            @if ($p->proof_image)
                                <a href="{{ asset('storage/'.$p->proof_image) }}" target="_blank" rel="noopener" class="text-brand hover:underline">Lihat bukti</a>
                            @else <span class="text-gray-400">-</span> @endif
                        </td>
                        <td class="px-4 py-3"><x-badge :status="$p->status" /></td>
                        <td class="px-4 py-3">
                            @if ($p->status === 'menunggu' && $p->order->status === 'pending' && ($p->method === 'cod' || $p->proof_image))
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('petani.payments.confirm', $p->id) }}">@csrf @method('PATCH')
                                        <button class="rounded-lg bg-brand px-3 py-1 text-xs font-semibold text-white hover:bg-brand-dark">Konfirmasi</button></form>
                                    <form method="POST" action="{{ route('petani.payments.reject', $p->id) }}">@csrf @method('PATCH')
                                        <button class="rounded-lg border border-[#8E1F14]/30 px-3 py-1 text-xs font-semibold text-[#8E1F14] hover:bg-[#FBE1DE]">Tolak</button></form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $payments->links() }}</div>
@endsection
