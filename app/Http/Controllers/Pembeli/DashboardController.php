<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $orders = $user->orders();

        return view('pembeli.dashboard', [
            'activeOrders' => (clone $orders)->whereIn('status', [
                Order::STATUS_PENDING, Order::STATUS_DIPROSES, Order::STATUS_DIKIRIM,
            ])->count(),
            'completedOrders' => (clone $orders)->where('status', Order::STATUS_SELESAI)->count(),
            'totalSpent' => (clone $orders)->where('status', Order::STATUS_SELESAI)->sum(DB::raw('total_price + shipping_cost')),
            'recentOrders' => $user->orders()->with('items.product')->latest()->limit(5)->get(),
        ]);
    }
}
