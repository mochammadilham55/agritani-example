<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::forFarmer(auth()->id())
            ->status($request->query('status'))
            ->with(['buyer', 'payment', 'items.product'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petani.orders.index', ['orders' => $orders]);
    }

    public function show(int $id)
    {
        $order = $this->findOrder($id)->load(['buyer', 'items.product', 'payment', 'shipment']);

        return view('petani.orders.show', ['order' => $order]);
    }

    /** Terima pesanan: pending -> diproses (transfer harus sudah dikonfirmasi pembayarannya). */
    public function confirm(int $id): RedirectResponse
    {
        $order = $this->findOrder($id)->load('payment');

        if ($order->status !== Order::STATUS_PENDING) {
            return back()->with('error', 'Hanya pesanan Pending yang dapat dikonfirmasi.');
        }

        if ($order->payment?->method === 'transfer' && $order->payment->status !== Payment::STATUS_DIKONFIRMASI) {
            return back()->with('error', 'Konfirmasi pembayaran transfer terlebih dahulu di menu Pembayaran.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => Order::STATUS_DIPROSES]);
            Shipment::firstOrCreate(['order_id' => $order->id], ['status' => Shipment::STATUS_DISIAPKAN]);
        });

        return back()->with('success', 'Pesanan dikonfirmasi dan siap dikemas.');
    }

    /** Tolak pesanan: pending -> dibatalkan, stok dikembalikan. */
    public function reject(int $id): RedirectResponse
    {
        $order = $this->findOrder($id);

        if ($order->status !== Order::STATUS_PENDING) {
            return back()->with('error', 'Hanya pesanan Pending yang dapat ditolak.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items()->with('product')->get() as $item) {
                $item->product->increment('stock', $item->quantity);
            }
            $order->update(['status' => Order::STATUS_DIBATALKAN]);
        });

        return back()->with('success', 'Pesanan ditolak dan stok dikembalikan.');
    }

    private function findOrder(int $id): Order
    {
        return Order::forFarmer(auth()->id())->findOrFail($id);
    }
}
