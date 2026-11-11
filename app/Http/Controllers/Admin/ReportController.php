<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesReports;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use HandlesReports;

    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        // Pendapatan platform = nilai barang pada pesanan Selesai (ongkir tidak dihitung).
        $sold = $this->soldItems($from, $to);

        $totals = (clone $sold)->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as revenue,
            COALESCE(SUM(order_items.quantity), 0) as qty, COUNT(DISTINCT orders.id) as orders')->first();

        $perDay = (clone $sold)->selectRaw('DATE(orders.created_at) as day, SUM(order_items.subtotal) as total')
            ->groupBy('day')->pluck('total', 'day');

        $chart = collect();
        for ($d = $from->copy(); $d <= $to; $d->addDay()) {
            $chart->push(['label' => $d->format('d/m'), 'total' => (float) ($perDay[$d->toDateString()] ?? 0)]);
        }

        $byStatus = Order::whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $topProducts = (clone $sold)
            ->join('users as farmers', 'farmers.id', '=', 'products.user_id')
            ->selectRaw('products.name, farmers.name as farmer, products.unit, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as total')
            ->groupBy('products.id', 'products.name', 'farmers.name', 'products.unit')
            ->orderByDesc('qty')->limit(10)->get();

        return view('admin.reports.index', [
            'from' => $from, 'to' => $to,
            'revenue' => (float) $totals->revenue,
            'completed' => (int) $totals->orders,
            'qty' => (int) $totals->qty,
            'chart' => $chart,
            'byStatus' => collect(Order::STATUSES)->mapWithKeys(fn ($s) => [$s => (int) ($byStatus[$s] ?? 0)]),
            'topProducts' => $topProducts,
        ]);
    }

    /** Export semua pesanan (semua status) pada rentang tanggal. */
    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $orders = Order::with(['buyer', 'payment'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('created_at');

        $filename = "laporan-platform_{$from->toDateString()}_{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No. Pesanan', 'Tanggal', 'Pembeli', 'Status', 'Subtotal', 'Ongkir', 'Total', 'Metode Bayar', 'Status Bayar']);

            foreach ($orders->cursor() as $o) {
                fputcsv($out, [
                    $o->id,
                    $o->created_at->format('Y-m-d H:i'),
                    $this->safe($o->buyer?->name),
                    $o->status,
                    (float) $o->total_price,
                    (float) $o->shipping_cost,
                    $o->grandTotal(),
                    $o->payment?->method,
                    $o->payment?->status,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function soldItems(Carbon $from, Carbon $to)
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', Order::STATUS_SELESAI)
            ->whereBetween('orders.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }
}
