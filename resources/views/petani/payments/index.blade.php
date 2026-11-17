@extends('layouts.app')

@section('title', 'Pembayaran')
@section('heading', 'Pembayaran')

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('petani.payments.index') }}" @class(['filter-pill', 'active' => ! request('status')])>Semua</a>
        @foreach (['menunggu', 'dikonfirmasi', 'ditolak'] as $s)
            <a href="{{ route('petani.payments.index', ['status' => $s]) }}" @class(['filter-pill', 'active' => request('status') === $s])>{{ ucfirst($s) }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr><th>Pesanan</th><th>Jumlah</th><th>Metode</th><th>Bukti</th><th>Status</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($payments as $p)
                        <tr>
                            <td class="fw-medium"><a href="{{ route('petani.orders.show', $p->order_id) }}" class="text-decoration-none">#{{ $p->order_id }}</a>
                                <span class="d-block small fw-normal text-secondary">{{ $p->order->buyer->name }}</span></td>
                            <td>Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            <td>{{ $p->method === 'cod' ? 'COD' : 'Transfer' }}</td>
                            <td>
                                @if ($p->proof_image)
                                    <a href="{{ asset('storage/'.$p->proof_image) }}" target="_blank" rel="noopener">Lihat bukti</a>
                                @else <span class="text-secondary">-</span> @endif
                            </td>
                            <td><x-badge :status="$p->status" /></td>
                            <td>
                                @if ($p->status === 'menunggu' && $p->order->status === 'pending' && ($p->method === 'cod' || $p->proof_image))
                                    <div class="d-flex justify-content-end gap-2">
                                        <form method="POST" action="{{ route('petani.payments.confirm', $p->id) }}">@csrf @method('PATCH')
                                            <button class="btn btn-primary btn-sm fw-semibold">Konfirmasi</button></form>
                                        <form method="POST" action="{{ route('petani.payments.reject', $p->id) }}">@csrf @method('PATCH')
                                            <button class="btn btn-outline-danger btn-sm fw-semibold">Tolak</button></form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5 text-center text-secondary">Belum ada pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
