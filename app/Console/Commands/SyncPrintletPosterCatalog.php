<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Variant;
use App\Services\PrintletApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to discover Printlet Egypt domestic catalog:
 * 1. Discovers Egypt country_id and shipping methods.
 * 2. Matches active poster products in Printlet.
 * 3. Pulls poster variants, print areas, placement presets, and syncs them to Quirka models/cache.
 */
class SyncPrintletPosterCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printlet:sync-poster-catalog {--product-id= : Optional specific Printlet product ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover Egypt fulfillment country, shipping methods, and sync poster catalog variants from Printlet.';

    protected PrintletApiService $printletApi;

    public function __construct(PrintletApiService $printletApi)
    {
        parent::__construct();
        $this->printletApi = $printletApi;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Printlet Egypt poster catalog discovery...');

        // 1. Discover Egypt Country ID
        $countriesResponse = $this->printletApi->getCountries();
        $countries = $countriesResponse['data'] ?? [];

        $egypt = collect($countries)->first(function ($c) {
            return strtoupper($c['code'] ?? '') === 'EG' || strtolower($c['name'] ?? '') === 'egypt';
        });

        if (! $egypt) {
            $this->error('Could not find Egypt (EG) in Printlet supported countries.');

            return self::FAILURE;
        }

        $countryId = $egypt['id'];
        $currency = $egypt['currency'] ?? 'EGP';
        $this->info(sprintf('Discovered Egypt Country ID: %s (%s)', $countryId, $currency));

        // Cache Egypt discovery data for runtime checkout use
        Cache::forever('printlet:egypt_country_id', $countryId);
        Cache::forever('printlet:currency', $currency);

        // 2. Discover and Cache Egypt Shipping Methods
        $shippingResponse = $this->printletApi->getShippingMethods($countryId);
        $shippingMethods = $shippingResponse['data'] ?? [];

        $this->info(sprintf('Discovered %d shipping method(s) for Egypt.', count($shippingMethods)));
        foreach ($shippingMethods as $sm) {
            $this->line(sprintf(' - [%s] %s (Cost: %s EGP, Est: %s)', $sm['id'] ?? 'N/A', $sm['name'] ?? 'Standard', $sm['cost'] ?? '0.00', $sm['estimated_days'] ?? 'N/A'));
        }

        if (! empty($shippingMethods)) {
            $primaryShippingMethodId = $shippingMethods[0]['id'] ?? 'sm_standard';
            Cache::forever('printlet:default_shipping_method_id', $primaryShippingMethodId);
            Cache::forever('printlet:shipping_methods', $shippingMethods);
        }

        // 3. Discover Poster Products
        $specificProductId = $this->option('product-id');
        $productsToSync = [];

        if ($specificProductId) {
            $productsToSync[] = ['id' => $specificProductId, 'title' => 'Target Poster'];
        } else {
            $productsResponse = $this->printletApi->getProducts($countryId);
            $allProducts = $productsResponse['data'] ?? [];

            // Filter for posters or framed posters
            $posterProducts = collect($allProducts)->filter(function ($p) {
                $title = strtolower($p['title'] ?? '');
                $cat = strtolower($p['category'] ?? '');

                return str_contains($title, 'poster') || str_contains($title, 'frame') || str_contains($cat, 'poster');
            });

            // If none explicitly matched "poster", take active products or fallback
            $productsToSync = $posterProducts->isNotEmpty() ? $posterProducts->values()->all() : $allProducts;
        }

        if (empty($productsToSync)) {
            $this->warn('No poster products found in Printlet catalog response.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d product candidate(s) to synchronize.', count($productsToSync)));

        // 4. Sync product variants, print areas, and placement presets
        foreach ($productsToSync as $prodItem) {
            $pId = $prodItem['id'];
            $this->line("Fetching configuration for product [{$pId}]...");

            try {
                $detail = $this->printletApi->getProduct($pId, $countryId);
            } catch (\Exception $e) {
                $this->error("Failed to load details for product {$pId}: ".$e->getMessage());
                continue;
            }

            $variants = $detail['variants'] ?? [];
            $printAreas = $detail['print_areas'] ?? [];
            $techniques = $detail['techniques'] ?? [];
            $presets = $detail['placement_presets'] ?? ['centered'];

            $printAreaId = $printAreas[0]['id'] ?? null;
            $techniqueId = $techniques[0]['id'] ?? null;
            $placementPreset = is_array($presets) ? ($presets[0] ?? 'centered') : 'centered';

            $this->info(sprintf(' Product "%s" has %d variant(s). Default Print Area: %s, Technique: %s', $detail['title'] ?? 'Poster', count($variants), $printAreaId ?? 'none', $techniqueId ?? 'none'));

            // Find or associate local poster products
            $localProducts = Product::all();
            if ($localProducts->isEmpty()) {
                $this->warn(' No local products found in database to map variants to. You can create variants once products exist.');
                continue;
            }

            foreach ($localProducts as $localProduct) {
                foreach ($variants as $v) {
                    $variantName = $v['name'] ?? 'Standard';
                    // Parse size and color from name (e.g. "A3 / Black Frame" or "30x40 Black")
                    $parts = array_map('trim', explode('/', $variantName));
                    $size = $parts[0] ?? 'Standard';
                    $color = $parts[1] ?? 'Natural';

                    // In Quirka, the variants.sku column has a UNIQUE constraint across the whole table.
                    // Therefore, the SKU must be scoped to the specific product (e.g. POSTER-7-A3-BLK)
                    $cleanSize = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $size));
                    $cleanColor = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $color));
                    $sku = 'POSTER-'.$localProduct->id.'-'.$cleanSize.'-'.$cleanColor;

                    Variant::updateOrCreate(
                        [
                            'product_id' => $localProduct->id,
                            'printlet_variant_id' => $v['id'],
                        ],
                        [
                            'size' => $size,
                            'color' => $color,
                            'sku' => $sku,
                            'price' => $localProduct->price ?? 150.00,
                            'printlet_print_area_id' => $printAreaId,
                            'printlet_technique_id' => $techniqueId,
                            'placement_preset' => $placementPreset,
                        ]
                    );
                }
            }

            $this->info(" Synced variants to local catalog for product [{$pId}].");
        }

        $this->info('Printlet catalog sync completed successfully!');

        return self::SUCCESS;
    }
}
