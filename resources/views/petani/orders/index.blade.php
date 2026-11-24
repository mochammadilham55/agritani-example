@extends('layouts.app')

@section('title', 'Pesanan Masuk')
@section('heading', 'Pesanan Masuk')

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('petani.orders.index') }}" @class(['filter-pill', 'active' => ! request('status')])>Semua</a>
        @foreach (\App\Models\Order::STATUSES as $s)
            <a href="{{ route('petani.orders.index', ['status' => $s]) }}" @class(['filter-pill', 'active' => request('status') === $s])>{{ ucfirst($s) }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr><th>Pesanan</th><th>Produk</th><th>Total</th><th>Bayar</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $o)
                        <tr>
                            <td class="fw-medium">#{{ $o->id }}<span class="d-block small fw-normal text-secondary">{{ $o->buyer->name }} · {{ $o->created_at->format('d M Y') }}</span></td>
                            <td class="truncate-cell text-secondary">{{ $o->items->map(fn ($i) => $i->product->name.' ×'.$i->quantity)->join(', ') }}</td>
                            <td>Rp {{ number_format($o->grandTotal(), 0, ',', '.') }}</td>
                            <td>{{ $o->payment?->method === 'cod' ? 'COD' : 'Transfer' }} <x-badge :status="$o->payment?->status ?? 'menunggu'" /></td>
                            <td><x-badge :status="$o->status" /></td>
                            <td class="text-end"><a href="{{ route('petani.orders.show', $o->id) }}" class="btn btn-outline-secondary btn-sm">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5 text-center text-secondary">{{ request('status') ? 'Tidak ada pesanan berstatus '.request('status').'.' : 'Belum ada pesanan masuk. Pesanan muncul di sini setelah pembeli checkout produk Anda.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
