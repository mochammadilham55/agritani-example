<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = auth()->user()->orders()
            ->status($request->query('status'))
            ->with(['items.product', 'payment'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pembeli.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        $this->authorizeOwner($order);

        $order->load(['items.product.farmer', 'payment', 'shipment', 'reviews']);

        return view('pembeli.orders.show', ['order' => $order]);
    }

    public function uploadProof(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);
        $payment = $order->payment;

        if (! $payment || $payment->method !== 'transfer'
            || $order->status !== Order::STATUS_PENDING
            || $payment->status === Payment::STATUS_DIKONFIRMASI) {
            return back()->with('error', 'Bukti pembayaran tidak dapat diunggah untuk pesanan ini.');
        }

        $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'proof_image.required' => 'Pilih file bukti pembayaran.',
            'proof_image.image' => 'File harus berupa gambar.',
            'proof_image.mimes' => 'Format yang diizinkan: JPG atau PNG.',
            'proof_image.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        if ($payment->proof_image) {
            Storage::disk('public')->delete($payment->proof_image);
        }

        $payment->update([
            'proof_image' => $request->file('proof_image')->store('payment-proofs', 'public'),
            'status' => Payment::STATUS_MENUNGGU, // diajukan ulang setelah ditolak
        ]);

        return back()->with('success', 'Bukti pembayaran terkirim. Menunggu konfirmasi petani.');
    }

    /** Batalkan pesanan yang belum diproses; stok dikembalikan. */
    public function cancel(Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);

        if ($order->status !== Order::STATUS_PENDING) {
            return back()->with('error', 'Hanya pesanan berstatus Pending yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items()->with('product')->get() as $item) {
                $item->product->increment('stock', $item->quantity);
            }
            $order->update(['status' => Order::STATUS_DIBATALKAN]);
        });

        return back()->with('success', 'Pesanan dibatalkan.');
    }

    /** Pembeli menandai pesanan yang dikirim sebagai diterima. */
    public function complete(Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);

        if ($order->status !== Order::STATUS_DIKIRIM) {
            return back()->with('error', 'Pesanan belum dalam status Dikirim.');
        }

        $order->update(['status' => Order::STATUS_SELESAI]);
        $order->shipment?->update(['status' => 'tiba']);

        return back()->with('success', 'Terima kasih! Anda kini dapat memberi ulasan.');
    }

    private function authorizeOwner(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 404);
    }
}
