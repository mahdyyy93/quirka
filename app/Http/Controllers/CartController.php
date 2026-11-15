<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use App\Services\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $items = Cart::items();

        return view('cart', ['items' => $items, 'subtotal' => $items->sum('line_total')]);
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

        $variant = null;
        if ($request->filled('variant_id')) {
            $variant = Variant::where('product_id', $product->id)->find($request->input('variant_id'));

            if (! $variant) {
                $message = 'The selected option does not belong to this product.';

                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : back()->with('error', $message);
            }
        }

        $line = Cart::add($product, $variant, $request->integer('quantity'), $request->input('size'), $request->input('color'));
        $unitPrice = (float) ($variant?->price ?? $product->price);
        $cartCount = Cart::count();

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
                    'quantity' => $line['quantity'],
                    'size' => $line['size'],
                    'color' => $line['color'],
                    'unit_price' => $unitPrice,
                    'image_url' => $firstImage ? Storage::url($firstImage->path) : null,
                ],
            ]);
        }

        return back()->with('success', "\"{$product->title}\" added to your cart.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
            'variant_id' => 'nullable|integer',
        ]);

        Cart::setQuantity(Cart::lineKey($product->id, $validated['variant_id'] ?? null), (int) $validated['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $variantId = $request->filled('variant_id') ? (int) $request->input('variant_id') : null;

        Cart::remove(Cart::lineKey($product->id, $variantId));

        return back()->with('success', 'Item removed from cart.');
    }
}
