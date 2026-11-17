@props(['orders'])

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead>
                <tr>
                    <th>Pesanan</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-medium">#{{ $order->id }}
                            @if ($order->relationLoaded('buyer') && $order->buyer)
                                <span class="d-block small fw-normal text-secondary">{{ $order->buyer->name }}</span>
                            @endif
                        </td>
                        <td class="text-secondary">{{ $order->created_at->format('d M Y') }}</td>
                        <td>Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</td>
                        <td><x-badge :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-secondary">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
