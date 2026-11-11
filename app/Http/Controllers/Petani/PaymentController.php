<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::whereHas('order', fn ($q) => $q->forFarmer(auth()->id()))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('order.buyer')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petani.payments.index', ['payments' => $payments]);
    }

    public function confirm(int $id): RedirectResponse
    {
        $payment = $this->findPayment($id);

        if ($error = $this->blocked($payment, requireProof: true)) {
            return back()->with('error', $error);
        }

        $payment->update(['status' => Payment::STATUS_DIKONFIRMASI, 'confirmed_at' => now()]);

        return back()->with('success', 'Pembayaran dikonfirmasi.');
    }

    public function reject(int $id): RedirectResponse
    {
        $payment = $this->findPayment($id);

        if ($error = $this->blocked($payment, requireProof: true)) {
            return back()->with('error', $error);
        }

        $payment->update(['status' => Payment::STATUS_DITOLAK, 'confirmed_at' => null]);

        return back()->with('success', 'Pembayaran ditolak. Pembeli dapat mengunggah ulang bukti.');
    }

    private function findPayment(int $id): Payment
    {
        return Payment::whereHas('order', fn ($q) => $q->forFarmer(auth()->id()))
            ->with('order')->findOrFail($id);
    }

    /** Pesan error bila pembayaran tidak boleh diproses saat ini, atau null. */
    private function blocked(Payment $payment, bool $requireProof): ?string
    {
        if ($payment->order->status !== Order::STATUS_PENDING) {
            return 'Pesanan sudah tidak berstatus Pending.';
        }
        if ($payment->status !== Payment::STATUS_MENUNGGU) {
            return 'Pembayaran ini sudah diproses.';
        }
        if ($requireProof && $payment->method === 'transfer' && ! $payment->proof_image) {
            return 'Pembeli belum mengunggah bukti transfer.';
        }

        return null;
    }
}
