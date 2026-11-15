<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Collection;

/**
 * Session-backed shopping cart. One line per product+variant combination.
 *
 * Session shape: ['p{productId}-v{variantId|0}' => ['product_id', 'variant_id', 'quantity', 'size', 'color']]
 * Legacy entries keyed by product id (['quantity' => n, ...]) are still understood.
 */
class Cart
{
    public const SESSION_KEY = 'cart';

    public static function lineKey(int $productId, ?int $variantId): string
    {
        return 'p'.$productId.'-v'.($variantId ?? 0);
    }

    /**
     * Raw session entries normalized to include their line key, product_id and variant_id.
     *
     * @return array<string, array{key: string, product_id: int, variant_id: ?int, quantity: int, size: ?string, color: ?string}>
     */
    public static function raw(): array
    {
        $lines = [];

        foreach (session()->get(self::SESSION_KEY, []) as $key => $entry) {
            $productId = (int) ($entry['product_id'] ?? $key);
            $variantId = ! empty($entry['variant_id']) ? (int) $entry['variant_id'] : null;
            $lineKey = self::lineKey($productId, $variantId);

            $lines[$lineKey] = [
                'key' => $lineKey,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => max(1, (int) ($entry['quantity'] ?? 1)),
                'size' => $entry['size'] ?? null,
                'color' => $entry['color'] ?? null,
            ];
        }

        return $lines;
    }

    public static function count(): int
    {
        return (int) collect(self::raw())->sum('quantity');
    }

    public static function add(Product $product, ?Variant $variant, int $quantity, ?string $size = null, ?string $color = null): array
    {
        $lines = self::raw();
        $key = self::lineKey($product->id, $variant?->id);

        $lines[$key] = [
            'key' => $key,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => ($lines[$key]['quantity'] ?? 0) + $quantity,
            'size' => $variant?->size ?? $size,
            'color' => $variant?->color ?? $color,
        ];

        self::store($lines);

        return $lines[$key];
    }

    public static function setQuantity(string $key, int $quantity): void
    {
        $lines = self::raw();

        if (isset($lines[$key])) {
            $lines[$key]['quantity'] = $quantity;
            self::store($lines);
        }
    }

    public static function remove(string $key): void
    {
        $lines = self::raw();
        unset($lines[$key]);
        self::store($lines);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Cart lines hydrated with Product and Variant models. Lines whose product
     * is missing or unavailable are dropped.
     *
     * @return Collection<int, array{key: string, product: Product, variant: ?Variant, quantity: int, unit_price: float, line_total: float, size: ?string, color: ?string}>
     */
    public static function items(): Collection
    {
        $lines = self::raw();
        if (empty($lines)) {
            return collect();
        }

        $products = Product::with(['variants', 'images'])
            ->whereIn('id', collect($lines)->pluck('product_id')->unique())
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        return collect($lines)->map(function (array $line) use ($products) {
            $product = $products->get($line['product_id']);
            if (! $product) {
                return null;
            }

            $variant = null;
            if ($line['variant_id']) {
                $variant = $product->variants->firstWhere('id', $line['variant_id']);
            }
            if (! $variant && $line['size']) {
                $variant = $product->variants->first(function ($v) use ($line) {
                    $match = strtolower($v->size) === strtolower($line['size']);

                    return $line['color'] ? $match && strtolower($v->color) === strtolower($line['color']) : $match;
                });
            }

            $unitPrice = (float) ($variant?->price ?? $product->price);

            return [
                'key' => $line['key'],
                'product' => $product,
                'variant' => $variant,
                'quantity' => $line['quantity'],
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $line['quantity'],
                'size' => $variant?->size ?? $line['size'],
                'color' => $variant?->color ?? $line['color'],
            ];
        })->filter()->values();
    }

    protected static function store(array $lines): void
    {
        session()->put(self::SESSION_KEY, array_map(
            fn (array $l) => collect($l)->except('key')->all(),
            $lines
        ));
    }
}
