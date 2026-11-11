@props(['orders'])

<div class="overflow-x-auto rounded-xl border border-line bg-white">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-gray-500">
            <tr>
                <th class="px-4 py-3">Pesanan</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Total</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @forelse ($orders as $order)
                <tr>
                    <td class="px-4 py-3 font-medium">#{{ $order->id }}
                        @if ($order->relationLoaded('buyer') && $order->buyer)
                            <span class="block text-xs font-normal text-gray-500">{{ $order->buyer->name }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</td>
                    <td class="px-4 py-3"><x-badge :status="$order->status" /></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
