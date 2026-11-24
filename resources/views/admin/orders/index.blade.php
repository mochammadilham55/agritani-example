@extends('layouts.app')

@section('title', 'Daftar Pesanan')
@section('heading', 'Daftar Pesanan')

@section('content')
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="No. pesanan, pembeli, atau petani…" class="form-control flex-grow-1" style="min-width: 12rem; width: auto;" aria-label="Cari pesanan">
        <select name="status" class="form-select w-auto" onchange="this.form.submit()" aria-label="Status">
            <option value="">Semua status</option>
            @foreach (\App\Models\Order::STATUSES as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary">Cari</button>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr><th>Pesanan</th><th>Petani</th><th>Total</th><th>Bayar</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $o)
                        <tr>
                            <td class="fw-medium">#{{ $o->id }}<span class="d-block small fw-normal text-secondary">{{ $o->buyer->name }} · {{ $o->created_at->format('d M Y') }}</span></td>
                            <td class="text-secondary">{{ $o->items->map(fn ($i) => $i->product->farmer?->name)->unique()->join(', ') }}</td>
                            <td>Rp {{ number_format($o->grandTotal(), 0, ',', '.') }}</td>
                            <td>{{ $o->payment?->method === 'cod' ? 'COD' : 'Transfer' }} <x-badge :status="$o->payment?->status ?? 'menunggu'" /></td>
                            <td><x-badge :status="$o->status" /></td>
                            <td class="text-end"><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-outline-secondary btn-sm">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5 text-center text-secondary">{{ request()->hasAny(['q', 'status']) ? 'Tidak ada pesanan yang cocok dengan pencarian atau status ini.' : 'Belum ada pesanan di AgriTani.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
