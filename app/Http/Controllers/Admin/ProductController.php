<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['supplier', 'stockLevels', 'images']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('low_stock')) {
            $query->whereHas('stockLevels', function ($q) {
                $q->where('quantity', '<', 10);
            });
        }

        return response()->json($query->paginate(15));
    }
    public function toggleAvailability(Product $product)
    {
        $product->is_available = !$product->is_available;
        $product->save();

        return response()->json([
            'message' => 'Product availability updated.',
            'is_available' => $product->is_available
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'sku'            => 'required|string|max:255|unique:products,sku',
            'price'          => 'required|numeric|min:0',
            'supplier_id'    => 'required|exists:suppliers,id',
            'customizations' => 'nullable|string',
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if (!empty($validated['customizations'])) {
            $validated['customizations'] = json_decode($validated['customizations'], true);
        }

        $product = Product::create(Arr::except($validated, ['images']));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        return response()->json(['message' => 'Product created successfully.', 'product' => $product->load('images')]);
    }

    public function update(Request $request, Product $product)
    {
    //         dd([
    //     'has_file' => $request->hasFile('images'),
    //     'files' => $request->file('images'),
    //     'all' => $request->all(),
    // ]);
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'sku'            => 'required|string|max:255|unique:products,sku,' . $product->id,
            'price'          => 'required|numeric|min:0',
            'supplier_id'    => 'required|exists:suppliers,id',
            'customizations' => 'nullable|string',
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp|max:5048',
        ]);

        // Decode JSON string into array before saving
        if (!empty($validated['customizations'])) {
            $validated['customizations'] = json_decode($validated['customizations'], true);
        }

        $product->update(Arr::except($validated, ['images']));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        return response()->json(['message' => 'Product updated successfully.', 'product' => $product->load('images')]);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
