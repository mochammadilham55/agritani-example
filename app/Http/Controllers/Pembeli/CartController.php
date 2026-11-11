<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $items = auth()->user()->carts()->with('product.farmer')->get();

        return view('pembeli.cart', [
            'items' => $items,
            'total' => $items->sum(fn (Cart $i) => $i->product->price * $i->quantity),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1']]);

        if (! $product->isPurchasable() || $product->stock < 1) {
            return back()->with('error', 'Produk tidak tersedia.');
        }

        $cart = auth()->user()->carts()->firstOrNew(['product_id' => $product->id]);
        $wanted = ($cart->quantity ?? 0) + ($data['quantity'] ?? 1);
        $cart->quantity = min($wanted, $product->stock);
        $cart->save();

        $msg = $wanted > $product->stock
            ? "Jumlah disesuaikan dengan stok tersedia ({$product->stock} {$product->unit})."
            : 'Produk ditambahkan ke keranjang.';

        return back()->with('success', $msg);
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless($cart->user_id === auth()->id(), 404);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $stock = $cart->product->stock;

        $cart->update(['quantity' => min($data['quantity'], max($stock, 1))]);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Cart $cart): RedirectResponse
    {
        abort_unless($cart->user_id === auth()->id(), 404);

        $cart->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
