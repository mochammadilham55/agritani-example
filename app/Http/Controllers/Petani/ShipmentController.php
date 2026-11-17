<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /** Pesanan yang siap dikirim (Diproses) atau sudah dikirim (resi masih bisa diubah). */
    public function index()
    {
        $orders = Order::forFarmer(auth()->id())
            ->whereIn('status', [Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM])
            ->with(['buyer', 'items.product'])
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
        ]);

        $firstShip = $order->status === Order::STATUS_DIPROSES;

        $order->fill($data);
        $order->shipped_at ??= now();
        $order->status = Order::STATUS_DIKIRIM;
        $order->save();

        return back()->with('success', $firstShip ? 'Resi disimpan, pesanan berstatus Dikirim.' : 'Data pengiriman diperbarui.');
    }
}
