<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cart = session()->get('cart', []);

        $productIds = array_keys($cart);
        $products = Product::with(['variants', 'images'])
            ->whereIn('id', $productIds)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        return view('cart', compact('cart', 'products'));
    }

    public function add(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
            'variant_id' => 'nullable|exists:variants,id',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ]);

        if (! $product->is_available) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is no longer available.',
                ], 422);
            }

            return back()->with('error', 'This product is no longer available.');
        }

        $cart = session()->get('cart', []);
        $qty = $request->integer('quantity');
        $variantId = $request->input('variant_id');
        $size = $request->input('size');
        $color = $request->input('color');
        $unitPrice = (float) $product->price;

        if ($variantId) {
            $variant = Variant::find($variantId);
            if ($variant && $variant->product_id === $product->id) {
                $size = $variant->size;
                $color = $variant->color;
                $unitPrice = (float) $variant->price;
            }
        }

        $cart[$product->id] = [
            'quantity' => ($cart[$product->id]['quantity'] ?? 0) + $qty,
            'variant_id' => $variantId ?? ($cart[$product->id]['variant_id'] ?? null),
            'size' => $size ?? ($cart[$product->id]['size'] ?? null),
            'color' => $color ?? ($cart[$product->id]['color'] ?? null),
        ];

        session()->put('cart', $cart);

        $cartCount = collect($cart)->sum('quantity');

        if ($request->wantsJson()) {
            $firstImage = $product->images()->first();

            return response()->json([
                'success' => true,
                'message' => "\"{$product->title}\" added to your cart.",
                'cartCount' => $cartCount,
                'item' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'quantity' => $cart[$product->id]['quantity'],
                    'size' => $cart[$product->id]['size'],
                    'color' => $cart[$product->id]['color'],
                    'unit_price' => $unitPrice,
                    'image_url' => $firstImage ? Storage::url($firstImage->path) : null,
                ],
            ]);
        }

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
