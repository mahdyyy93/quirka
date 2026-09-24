<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $products = Product::where('is_available', true)
            ->with(['stockLevels', 'images', 'variants'])
            ->latest()
            ->paginate(12);

        return view('shop', compact('products'));
    }

    public function show(Product $product): View
    {
        if (! $product->is_available) {
            abort(404, 'Product not found or unavailable.');
        }

        $product->load([
            'images',
            'variants',
            'supplier',
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
