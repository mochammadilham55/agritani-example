<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Pesanan 7 hari terakhir, per hari (hari kosong tetap tampil = 0).
        $perDay = Order::where('created_at', '>=', today()->subDays(6))
            ->get(['created_at'])
            ->countBy(fn ($o) => $o->created_at->toDateString());

        $chart = collect(range(6, 0))->map(fn ($i) => [
            'label' => today()->subDays($i)->translatedFormat('D'),
            'count' => $perDay[today()->subDays($i)->toDateString()] ?? 0,
        ]);

        return view('admin.dashboard', [
            'userCount' => User::where('role', '!=', User::ROLE_ADMIN)->count(),
            'activeProducts' => Product::active()->count(),
            'ordersToday' => Order::whereDate('created_at', today())->count(),
            'revenue' => Order::where('status', Order::STATUS_SELESAI)->sum('total_price'),
            'chart' => $chart,
            'recentOrders' => Order::with('buyer')->latest()->limit(5)->get(),
        ]);
    }
}
