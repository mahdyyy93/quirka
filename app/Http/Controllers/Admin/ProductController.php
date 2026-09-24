<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
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
        $product->is_available = ! $product->is_available;
        $product->save();

        return response()->json([
            'message' => 'Product availability updated.',
            'is_available' => $product->is_available,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'customizations' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if (! empty($validated['customizations'])) {
            $validated['customizations'] = json_decode($validated['customizations'], true);
        }

        $product = Product::create(Arr::except($validated, ['images']));

        // Automatically assign standard Printlet poster variants (A4/A3 Black/White frames)
        $this->attachDefaultPosterVariants($product);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        return response()->json(['message' => 'Product created successfully.', 'product' => $product->load(['images', 'variants'])]);
    }

    /**
     * Attach standard poster variants so newly created products are immediately checkout-ready.
     */
    protected function attachDefaultPosterVariants(Product $product): void
    {
        // Check if template variants already exist in the database from other products
        $templateVariants = Variant::where('product_id', '!=', $product->id)
            ->get()
            ->unique(fn ($v) => $v->printlet_variant_id);

        if ($templateVariants->isNotEmpty()) {
            foreach ($templateVariants as $template) {
                $cleanSize = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $template->size));
                $cleanColor = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $template->color));

                Variant::create([
                    'product_id' => $product->id,
                    'size' => $template->size,
                    'color' => $template->color,
                    'sku' => 'POSTER-'.$product->id.'-'.$cleanSize.'-'.$cleanColor,
                    'price' => $product->price,
                    'printlet_variant_id' => $template->printlet_variant_id,
                    'printlet_variant_revision' => $template->printlet_variant_revision,
                    'printlet_print_area_id' => $template->printlet_print_area_id,
                    'printlet_print_area_revision' => $template->printlet_print_area_revision,
                    'printlet_technique_id' => $template->printlet_technique_id,
                    'printlet_technique_revision' => $template->printlet_technique_revision,
                    'placement_preset' => $template->placement_preset,
                ]);
            }
        } else {
            // Default baseline poster configurations if catalog hasn't been synced yet
            $defaults = [
                ['size' => 'A3', 'color' => 'Black Frame', 'printlet_variant_id' => 'v_a3_black'],
                ['size' => 'A3', 'color' => 'White Frame', 'printlet_variant_id' => 'v_a3_white'],
                ['size' => 'A4', 'color' => 'Black Frame', 'printlet_variant_id' => 'v_a4_black'],
                ['size' => 'A4', 'color' => 'White Frame', 'printlet_variant_id' => 'v_a4_white'],
            ];

            foreach ($defaults as $d) {
                $cleanSize = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $d['size']));
                $cleanColor = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $d['color']));

                Variant::create([
                    'product_id' => $product->id,
                    'size' => $d['size'],
                    'color' => $d['color'],
                    'sku' => 'POSTER-'.$product->id.'-'.$cleanSize.'-'.$cleanColor,
                    'price' => $product->price,
                    'printlet_variant_id' => $d['printlet_variant_id'],
                    'printlet_print_area_id' => 'pa_front',
                    'printlet_technique_id' => 'tech_digital_print',
                    'placement_preset' => 'centered',
                ]);
            }
        }
    }

    public function update(Request $request, Product $product)
    {
        //         dd([
        //     'has_file' => $request->hasFile('images'),
        //     'files' => $request->file('images'),
        //     'all' => $request->all(),
        // ]);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,'.$product->id,
            'price' => 'required|numeric|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'customizations' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5048',
        ]);

        // Decode JSON string into array before saving
        if (! empty($validated['customizations'])) {
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
