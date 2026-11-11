<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $reviews = $user->reviews()->with(['product', 'order'])->latest()->get();

        // Item dari pesanan Selesai yang belum diulas.
        $reviewed = $reviews->map(fn ($r) => $r->order_id.'-'.$r->product_id)->all();
        $pending = OrderItem::with(['order', 'product'])
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', Order::STATUS_SELESAI))
            ->latest()
            ->get()
            ->reject(fn ($i) => in_array($i->order_id.'-'.$i->product_id, $reviewed, true));

        return view('pembeli.reviews.index', ['reviews' => $reviews, 'pending' => $pending]);
    }

    public function create(Order $order, Product $product)
    {
        $this->authorizeReviewable($order, $product);

        if ($this->existing($order, $product)) {
            return redirect()->route('pembeli.reviews.index')->with('error', 'Anda sudah mengulas produk ini.');
        }

        return view('pembeli.reviews.form', ['order' => $order, 'product' => $product, 'review' => new Review(['rating' => 5])]);
    }

    public function store(Request $request, Order $order, Product $product): RedirectResponse
    {
        $this->authorizeReviewable($order, $product);

        if ($this->existing($order, $product)) {
            return redirect()->route('pembeli.reviews.index')->with('error', 'Anda sudah mengulas produk ini.');
        }

        Review::create($this->validated($request) + [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'is_approved' => false, // menunggu moderasi admin
        ]);

        return redirect()->route('pembeli.reviews.index')
            ->with('success', 'Terima kasih! Ulasan Anda akan tampil setelah disetujui admin.');
    }

    public function edit(Review $review)
    {
        $this->authorizeOwner($review);

        return view('pembeli.reviews.form', ['order' => $review->order, 'product' => $review->product, 'review' => $review]);
    }

    /** Mengubah ulasan mengembalikannya ke antrean moderasi. */
    public function update(Request $request, Review $review): RedirectResponse
    {
        $this->authorizeOwner($review);

        $review->update($this->validated($request) + ['is_approved' => false]);

        return redirect()->route('pembeli.reviews.index')
            ->with('success', 'Ulasan diperbarui dan menunggu persetujuan admin.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Pilih rating 1-5.',
            'rating.between' => 'Rating harus antara 1 dan 5.',
        ]);
    }

    /** Pesanan milik pembeli, sudah Selesai, dan memuat produk tersebut. */
    private function authorizeReviewable(Order $order, Product $product): void
    {
        abort_unless($order->user_id === auth()->id(), 404);
        abort_unless($order->items()->where('product_id', $product->id)->exists(), 404);

        if ($order->status !== Order::STATUS_SELESAI) {
            abort(403, 'Ulasan hanya dapat diberikan setelah pesanan selesai.');
        }
    }

    private function authorizeOwner(Review $review): void
    {
        abort_unless($review->user_id === auth()->id(), 404);
    }

    private function existing(Order $order, Product $product): bool
    {
        return Review::where(['order_id' => $order->id, 'product_id' => $product->id, 'user_id' => auth()->id()])->exists();
    }
}
