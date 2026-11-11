<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        $orders = Order::forFarmer(auth()->id())
            ->whereIn('status', [Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM])
            ->with(['buyer', 'shipment', 'items.product'])
            ->oldest()
            ->paginate(10);

        return view('petani.shipments.index', ['orders' => $orders]);
    }

    /** Isi / ubah resi. Pertama kali diisi: pesanan menjadi Dikirim. */
    public function update(Request $request, int $id): RedirectResponse
    {
        $order = Order::forFarmer(auth()->id())->findOrFail($id);

        if (! in_array($order->status, [Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM], true)) {
            return back()->with('error', 'Pesanan ini tidak dapat dikirim.');
        }

        $data = $request->validate([
            'courier' => ['required', 'string', 'max:50'],
            'tracking_number' => ['required', 'string', 'max:50'],
            'estimated_arrival' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $firstShip = $order->status === Order::STATUS_DIPROSES;

        DB::transaction(function () use ($order, $data, $firstShip) {
            $shipment = Shipment::firstOrNew(['order_id' => $order->id]);
            $shipment->fill($data);
            $shipment->status = Shipment::STATUS_DIKIRIM;
            $shipment->shipped_at ??= now();
            $shipment->save();

            if ($firstShip) {
                $order->update(['status' => Order::STATUS_DIKIRIM]);
            }
        });

        return back()->with('success', $firstShip ? 'Resi disimpan, pesanan berstatus Dikirim.' : 'Data pengiriman diperbarui.');
    }

    /** Tandai paket sudah tiba. Status pesanan menjadi Selesai setelah pembeli mengonfirmasi. */
    public function arrived(int $id): RedirectResponse
    {
        $order = Order::forFarmer(auth()->id())->with('shipment')->findOrFail($id);

        if ($order->status !== Order::STATUS_DIKIRIM || ! $order->shipment) {
            return back()->with('error', 'Pesanan belum dalam pengiriman.');
        }

        $order->shipment->update(['status' => Shipment::STATUS_TIBA]);

        return back()->with('success', 'Pengiriman ditandai tiba. Menunggu konfirmasi pembeli.');
    }
}
