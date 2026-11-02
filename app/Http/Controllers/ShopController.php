<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $featuredProducts = Product::featured()
            ->with(['images', 'user'])
            ->take(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::where('is_available', true)
                ->with(['images', 'user'])
                ->latest()
                ->take(4)
                ->get();
        }

        $products = Product::where('is_available', true)
            ->with(['stockLevels', 'images', 'variants'])
            ->latest()
            ->paginate(12);

        return view('shop', compact('products', 'featuredProducts'));
    }

    public function show(Product $product): View
    {
        if (! $product->is_available) {
            abort(404, 'Product not found or unavailable.');
        }

        $product->load([
            'images',
            'variants',
            'user',
            'stockLevels',
        ]);

        // Related or recommended products
        $relatedProducts = Product::where('is_available', true)
            ->where('id', '!=', $product->id)
            ->with(['images', 'variants'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
