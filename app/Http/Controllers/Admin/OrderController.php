<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['buyer', 'payment', 'items.product.farmer'])
            ->status($request->query('status'))
            ->when($request->query('q'), function ($query, $term) {
                $query->where(function ($w) use ($term) {
                    $w->whereHas('buyer', fn ($b) => $b->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('items.product.farmer', fn ($f) => $f->where('name', 'like', "%{$term}%"));
                    if (ctype_digit(ltrim($term, '#'))) {
                        $w->orWhere('id', (int) ltrim($term, '#'));
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        $order->load(['buyer', 'items.product.farmer', 'payment', 'shipment']);

        return view('admin.orders.show', ['order' => $order]);
    }
}
