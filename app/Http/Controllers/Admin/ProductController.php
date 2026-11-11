<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'farmer'])
            ->search($request->query('q'))
            ->when($request->query('status') === 'tampil', fn ($q) => $q->active())
            ->when($request->query('status') === 'diturunkan', fn ($q) => $q->where('is_suspended', true))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', ['products' => $products]);
    }

    /** Turunkan produk bermasalah (alasan wajib) atau pulihkan. */
    public function toggle(Request $request, Product $product): RedirectResponse
    {
        if ($product->is_suspended) {
            $product->forceFill(['is_suspended' => false, 'suspend_reason' => null])->save();

            return back()->with('success', 'Produk dipulihkan.');
        }

        $data = $request->validate(['reason' => ['required', 'string', 'max:200']], [
            'reason.required' => 'Alasan penurunan produk wajib diisi.',
        ]);

        $product->forceFill(['is_suspended' => true, 'suspend_reason' => $data['reason']])->save();

        return back()->with('success', 'Produk diturunkan dari katalog.');
    }
}
