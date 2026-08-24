<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cart = session()->get('cart', []);

        $productIds = array_keys($cart);
        $products = Product::with('supplier')
            ->whereIn('id', $productIds)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        return view('cart', compact('cart', 'products'));
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:99']);

        if (! $product->is_available) {
            return back()->with('error', 'This product is no longer available.');
        }

        $cart = session()->get('cart', []);
        $qty = $request->integer('quantity');

        $cart[$product->id] = [
            'quantity' => ($cart[$product->id]['quantity'] ?? 0) + $qty,
        ];

        session()->put('cart', $cart);

        return back()->with('success', "\"{$product->title}\" added to your cart.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:99']);

        $cart = session()->get('cart', []);
        $cart[$product->id] = ['quantity' => $request->integer('quantity')];
        session()->put('cart', $cart);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Product $product): RedirectResponse
    {
        $cart = session()->get('cart', []);
        unset($cart[$product->id]);
        session()->put('cart', $cart);

        return back()->with('success', 'Item removed from cart.');
    }
}
