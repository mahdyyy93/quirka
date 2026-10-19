<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createProductWithVariants(array $attributes = []): Product
    {
        $product = Product::create(array_merge([
            'title' => 'Vintage Botanical Print',
            'sku' => 'BOT-001',
            'price' => 250.00,
            'is_available' => true,
            'description' => 'A wonderful botanical museum reproduction.',
        ], $attributes));

        Variant::create([
            'product_id' => $product->id,
            'size' => '30x40 cm',
            'color' => 'Black Wood',
            'price' => 290.00,
            'sku' => 'BOT-001-3040-BLK',
            'printlet_variant_id' => 'PRT-VAR-001',
        ]);

        Variant::create([
            'product_id' => $product->id,
            'size' => '50x70 cm',
            'color' => 'Natural Oak',
            'price' => 450.00,
            'sku' => 'BOT-001-5070-OAK',
            'printlet_variant_id' => 'PRT-VAR-002',
        ]);

        return $product;
    }

    public function test_product_detail_page_can_be_rendered_by_slug(): void
    {
        $product = $this->createProductWithVariants();

        $this->assertNotEmpty($product->slug);

        $response = $this->get(route('shop.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Vintage Botanical Print');
        $response->assertSee('A wonderful botanical museum reproduction.');
        $response->assertSee('BOT-001');
        $response->assertSee('30x40 cm');
        $response->assertSee('Natural Oak');
    }

    public function test_unavailable_product_returns_404(): void
    {
        $product = $this->createProductWithVariants([
            'is_available' => false,
        ]);

        $response = $this->get(route('shop.show', $product));

        $response->assertStatus(404);
    }

    public function test_product_can_be_added_to_cart_via_ajax_with_variant(): void
    {
        $product = $this->createProductWithVariants();
        $variant = $product->variants->first();

        $response = $this->postJson(route('cart.add', $product), [
            'quantity' => 2,
            'variant_id' => $variant->id,
            'size' => $variant->size,
            'color' => $variant->color,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cartCount' => 2,
            'item' => [
                'id' => $product->id,
                'title' => $product->title,
                'quantity' => 2,
                'size' => '30x40 cm',
                'color' => 'Black Wood',
                'unit_price' => 290.00,
            ],
        ]);

        $cart = session()->get('cart', []);
        $this->assertArrayHasKey($product->id, $cart);
        $this->assertEquals(2, $cart[$product->id]['quantity']);
        $this->assertEquals($variant->id, $cart[$product->id]['variant_id']);
    }

    public function test_admin_can_toggle_update_and_delete_product_by_id(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $product = $this->createProductWithVariants();

        // Toggle availability via /admin/products/{id}/toggle-availability
        $toggleResponse = $this->actingAs($admin)->patchJson("/admin/products/{$product->id}/toggle-availability");
        $toggleResponse->assertStatus(200);
        $this->assertFalse($product->fresh()->is_available);

        // Update product via PUT /admin/products/{id}
        $updateResponse = $this->actingAs($admin)->putJson("/admin/products/{$product->id}", [
            'title' => 'Updated Vintage Poster',
            'sku' => 'BOT-001-UPDATED',
            'price' => 310.00,
        ]);
        $updateResponse->assertStatus(200);
        $this->assertEquals('Updated Vintage Poster', $product->fresh()->title);

        // Delete product via DELETE /admin/products/{id}
        $deleteResponse = $this->actingAs($admin)->deleteJson("/admin/products/{$product->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
