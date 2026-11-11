<?php

namespace App\Http\Controllers\Petani;

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

        $totals = $this->base($from, $to)
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as revenue,
                         COALESCE(SUM(order_items.quantity), 0) as qty,
                         COUNT(DISTINCT orders.id) as orders')
            ->first();

        $perDay = $this->base($from, $to)
            ->selectRaw('DATE(orders.created_at) as day, SUM(order_items.subtotal) as total')
            ->groupBy('day')->pluck('total', 'day');

        $chart = collect();
        for ($d = $from->copy(); $d <= $to; $d->addDay()) {
            $chart->push(['label' => $d->format('d/m'), 'total' => (float) ($perDay[$d->toDateString()] ?? 0)]);
        }

        $topProducts = $this->base($from, $to)
            ->selectRaw('products.name, products.unit, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as total')
            ->groupBy('products.id', 'products.name', 'products.unit')
            ->orderByDesc('qty')->limit(5)->get();

        $transactions = $this->base($from, $to)
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select('orders.id as order_id', 'orders.created_at', 'users.name as buyer', 'products.name as product',
                'order_items.quantity', 'order_items.subtotal')
            ->orderByDesc('orders.created_at')->orderBy('order_items.id')
            ->paginate(15)->withQueryString();

        return view('petani.reports.index', [
            'from' => $from, 'to' => $to,
            'revenue' => (float) $totals->revenue,
            'orders' => (int) $totals->orders,
            'qty' => (int) $totals->qty,
            'average' => $totals->orders ? (float) $totals->revenue / $totals->orders : 0,
            'chart' => $chart,
            'topProducts' => $topProducts,
            'transactions' => $transactions,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $rows = $this->base($from, $to)
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select('orders.id as order_id', 'orders.created_at', 'users.name as buyer', 'products.name as product',
                'order_items.quantity', 'order_items.price', 'order_items.subtotal')
            ->orderBy('orders.created_at')->orderBy('order_items.id');

        $filename = "laporan-penjualan_{$from->toDateString()}_{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['No. Pesanan', 'Tanggal', 'Pembeli', 'Produk', 'Jumlah', 'Harga Satuan', 'Subtotal']);

            foreach ($rows->cursor() as $r) {
                fputcsv($out, [
                    $r->order_id,
                    Carbon::parse($r->created_at)->format('Y-m-d H:i'),
                    $this->safe($r->buyer),
                    $this->safe($r->product),
                    $r->quantity,
                    (float) $r->price,
                    (float) $r->subtotal,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Item terjual (pesanan Selesai) milik petani ini dalam rentang tanggal. */
    private function base(Carbon $from, Carbon $to)
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('products.user_id', auth()->id())
            ->where('orders.status', Order::STATUS_SELESAI)
            ->whereBetween('orders.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }
}
