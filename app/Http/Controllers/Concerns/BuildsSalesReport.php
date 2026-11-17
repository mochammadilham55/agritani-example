<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Logika laporan penjualan yang dipakai bersama oleh laporan petani (anggota 4)
 * dan laporan admin (anggota 1). Petani hanya melihat produk miliknya ($farmerId),
 * admin melihat seluruh pesanan ($farmerId = null).
 */
trait BuildsSalesReport
{
    protected function salesReport(Request $request, ?int $farmerId): array
    {
        $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'status' => ['nullable', Rule::in(Order::STATUSES)],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
            'status.in' => 'Status tidak dikenal.',
        ]);

        $from = Carbon::parse($request->query('dari') ?: now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($request->query('sampai') ?: now())->endOfDay();
        $status = $request->query('status');

        $orders = Order::with(['buyer', 'items.product.farmer'])
            ->when($farmerId, fn ($q) => $q->forFarmer($farmerId))
            ->whereBetween('created_at', [$from, $to])
            ->status($status)
            ->oldest()
            ->get();

        // Satu baris per pesanan. Untuk petani, hanya item miliknya yang dihitung.
        $rows = $orders->map(function (Order $order) use ($farmerId) {
            $items = $farmerId
                ? $order->items->filter(fn ($i) => $i->product->user_id === $farmerId)
                : $order->items;

            return (object) [
                'order' => $order,
                'items' => $items,
                'amount' => $farmerId ? (float) $items->sum('subtotal') : $order->grandTotal(),
            ];
        });

        $completed = $rows->filter(fn ($r) => $r->order->isCompleted());

        $topProducts = $completed
            ->flatMap(fn ($r) => $r->items)
            ->groupBy(fn ($i) => $i->product->name)
            ->map(fn ($group, $name) => (object) [
                'name' => $name,
                'qty' => $group->sum('quantity'),
                'total' => (float) $group->sum('subtotal'),
            ])
            ->sortByDesc('qty')
            ->take(5)
            ->values();

        return [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'summary' => (object) [
                'count' => $rows->count(),
                'revenue' => (float) $completed->sum('amount'),
                'completed' => $completed->count(),
                'cancelled' => $rows->filter(fn ($r) => $r->order->status === Order::STATUS_DIBATALKAN)->count(),
            ],
            'topProducts' => $topProducts,
        ];
    }

    protected function salesCsv(array $report, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fwrite($out, "sep=;\n");       // Excel regional Indonesia memakai titik koma sebagai pemisah kolom

            $write = fn (array $cells) => fputcsv($out, $cells, ';');

            $write(['Laporan Penjualan AgriTani']);
            $write(['Periode', $report['from']->format('d/m/Y').' - '.$report['to']->format('d/m/Y')]);
            $write([]);
            $write(['No. Pesanan', 'Tanggal', 'Pembeli', 'Produk', 'Status', 'Total (Rp)']);

            foreach ($report['rows'] as $row) {
                $write([
                    $row->order->id,
                    $row->order->created_at->format('d/m/Y'),
                    $row->order->buyer->name,
                    $row->items->map(fn ($i) => $i->product->name.' x'.$i->quantity)->join(', '),
                    $row->order->status,
                    (int) $row->amount,
                ]);
            }

            $write([]);
            $write(['Pendapatan (pesanan selesai)', '', '', '', '', (int) $report['summary']->revenue]);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
