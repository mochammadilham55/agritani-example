<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $farmerId = auth()->id();

        // Item pesanan selesai milik produk petani ini.
        $soldItems = OrderItem::whereHas('product', fn ($q) => $q->where('user_id', $farmerId))
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_SELESAI));

        return view('petani.dashboard', [
            'productCount' => Product::where('user_id', $farmerId)->count(),
            'soldQty' => (clone $soldItems)->sum('quantity'),
            'revenue' => (clone $soldItems)->sum('subtotal'),
            'rating' => round((float) Review::approved()
                ->whereHas('product', fn ($q) => $q->where('user_id', $farmerId))->avg('rating'), 1),
            'lowStock' => Product::where('user_id', $farmerId)->lowStock()->orderBy('stock')->limit(5)->get(),
            'recentOrders' => Order::forFarmer($farmerId)->with('buyer')->latest()->limit(5)->get(),
        ]);
    }
}
