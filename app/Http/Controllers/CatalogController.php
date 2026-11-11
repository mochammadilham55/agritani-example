<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()
            ->with(['category', 'farmer'])
            ->withAvg(['reviews as avg_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
            ->when($request->query('kategori'), fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->search($request->query('q'))
            ->sortPrice($request->query('urut'))
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'farmer']);

        abort_unless($product->isPurchasable(), 404);

        return view('catalog.show', [
            'product' => $product,
            'reviews' => $product->reviews()->approved()->with('user')->latest()->limit(10)->get(),
            'rating' => $product->averageRating(),
            'related' => Product::active()->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)->limit(4)->get(),
        ]);
    }
}
