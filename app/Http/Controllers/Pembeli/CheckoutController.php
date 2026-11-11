<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function create()
    {
        $items = auth()->user()->carts()->with('product.farmer')->get();

        if ($items->isEmpty()) {
            return redirect()->route('pembeli.cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $shipping = config('agritani.shipping_cost');
        $groups = $items->groupBy(fn ($i) => $i->product->user_id);
        $subtotal = $items->sum(fn ($i) => $i->product->price * $i->quantity);

        return view('pembeli.checkout', [
            'groups' => $groups,
            'shipping' => $shipping,
            'subtotal' => $subtotal,
            'grand' => $subtotal + $shipping * $groups->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shipping_address' => ['required', 'string', 'min:10', 'max:500'],
            'method' => ['required', Rule::in(['transfer', 'cod'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = auth()->user();

        try {
            $orders = DB::transaction(fn () => $this->placeOrders($user, $data));
        } catch (DomainException $e) {
            return redirect()->route('pembeli.cart.index')->with('error', $e->getMessage());
        }

        // Satu pesanan transfer -> langsung ke halaman upload bukti.
        if ($orders->count() === 1) {
            return redirect()->route('pembeli.orders.show', $orders->first())
                ->with('success', 'Pesanan berhasil dibuat.'.($data['method'] === 'transfer' ? ' Silakan upload bukti pembayaran.' : ''));
        }

        return redirect()->route('pembeli.orders.index')
            ->with('success', "Pesanan berhasil dibuat ({$orders->count()} pesanan, satu per petani).");
    }

    /** Buat satu pesanan per petani, kurangi stok, kosongkan keranjang. */
    private function placeOrders($user, array $data)
    {
        $carts = $user->carts()->get();
        if ($carts->isEmpty()) {
            throw new DomainException('Keranjang Anda kosong.');
        }

        // Kunci baris produk agar stok tidak diperebutkan checkout lain.
        $products = Product::whereIn('id', $carts->pluck('product_id'))->lockForUpdate()->get()->keyBy('id');

        foreach ($carts as $cart) {
            $p = $products[$cart->product_id];
            if (! $p->isPurchasable() || $p->stock < $cart->quantity) {
                throw new DomainException("Stok \"{$p->name}\" tidak mencukupi atau produk tidak tersedia. Silakan perbarui keranjang.");
            }
        }

        $shipping = config('agritani.shipping_cost');
        $orders = collect();

        foreach ($carts->groupBy(fn ($c) => $products[$c->product_id]->user_id) as $farmerId => $lines) {
            $subtotal = $lines->sum(fn ($c) => $products[$c->product_id]->price * $c->quantity);

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $subtotal,
                'status' => Order::STATUS_PENDING,
                'shipping_address' => $data['shipping_address'],
                'shipping_cost' => $shipping,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lines as $c) {
                $p = $products[$c->product_id];
                $order->items()->create([
                    'product_id' => $p->id,
                    'quantity' => $c->quantity,
                    'price' => $p->price, // harga dibekukan saat checkout
                    'subtotal' => $p->price * $c->quantity,
                ]);
                $p->decrement('stock', $c->quantity);
            }

            Payment::create([
                'order_id' => $order->id,
                'amount' => $subtotal + $shipping,
                'method' => $data['method'],
                'status' => Payment::STATUS_MENUNGGU,
            ]);

            $orders->push($order);
        }

        $user->carts()->delete();

        return $orders;
    }
}
