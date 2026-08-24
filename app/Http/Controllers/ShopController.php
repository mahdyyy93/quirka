<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ShopController extends Controller
{
    public function index()
    {
        $products = Product::where('is_available', true)->with(['stockLevels', 'images'])->paginate(12);
        return view('shop', compact('products'));
    }
}
