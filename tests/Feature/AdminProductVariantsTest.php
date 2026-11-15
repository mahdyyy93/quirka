<?php

namespace Tests\Feature;

use App\Models\PricingSetting;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductVariantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PricingSetting::current();
    }

    protected function createProduct(User $artist, string $sku): Product
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($artist)->postJson(route('products.store'), [
            'title' => 'Poster '.$sku,
            'sku' => $sku,
            'price' => 250,
        ]);
        $response->assertOk();

        return Product::where('sku', $sku)->firstOrFail();
    }

    public function test_new_product_gets_default_variants_with_print_cost_and_derived_prices(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);

        $product = $this->createProduct($artist, 'NEW-1');
        $variants = $product->variants()->get();

        $this->assertCount(4, $variants);
        $this->assertNull($variants->first(fn ($v) => $v->print_cost === null));

        $a3 = $variants->firstWhere('printlet_variant_id', 'v_a3_black');
        $a4 = $variants->firstWhere('printlet_variant_id', 'v_a4_black');

        // Repriced from print cost: larger size costs more, and not the flat product price.
        $this->assertGreaterThan((float) $a4->price, (float) $a3->price);
        $this->assertNotEquals(250.00, (float) $a4->price);
        $this->assertGreaterThan((float) $a3->print_cost, (float) $a3->price);
    }

    public function test_new_product_clones_print_cost_from_synced_templates(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);

        $existing = Product::factory()->create(['user_id' => $artist->id]);
        Variant::create([
            'product_id' => $existing->id,
            'size' => '30x40',
            'color' => 'Oak',
            'sku' => 'POSTER-SYNCED-1',
            'price' => 300,
            'print_cost' => 180.00,
            'printlet_variant_id' => 'v_synced',
        ]);

        $product = $this->createProduct($artist, 'NEW-2');
        $clone = $product->variants()->where('printlet_variant_id', 'v_synced')->firstOrFail();

        $this->assertEquals(180.00, (float) $clone->print_cost);
        $this->assertNotEquals(0.0, (float) $clone->price);
    }
}
