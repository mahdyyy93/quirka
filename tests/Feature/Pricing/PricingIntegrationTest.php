<?php

namespace Tests\Feature\Pricing;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PricingSetting;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use App\Services\Pricing\RepriceProductVariants;
use App\Services\PrintletApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PricingSetting::current(); // Ensure settings exist
    }

    public function test_order_checkout_snapshots_pricing_breakdown_into_order_items(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);
        $customerUser = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create([
            'user_id' => $customerUser->id,
            'name' => $customerUser->name,
            'email' => $customerUser->email,
        ]);

        $product = Product::factory()->create([
            'user_id' => $artist->id,
            'artist_min_profit' => 30.00,
            'artist_max_profit' => 60.00,
            'is_available' => true,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'A3',
            'color' => 'Black',
            'sku' => 'POSTER-A3-BLK-TEST',
            'price' => 155.00,
            'print_cost' => 100.00,
            'printlet_variant_id' => 'v_mock_a3',
            'printlet_print_area_id' => 'pa_mock_front',
            'printlet_technique_id' => 'tech_mock_digital',
        ]);

        // Mock Printlet API
        $printletMock = $this->createMock(PrintletApiService::class);
        $printletMock->method('createQuote')->willReturn([
            'id' => 'quote_mock_123',
            'totals' => ['shipping' => 45.00],
        ]);
        $printletMock->method('createOrder')->willReturn([
            'id' => 'ord_mock_123',
            'status' => 'pending_verification',
        ]);
        $this->app->instance(PrintletApiService::class, $printletMock);

        $response = $this->actingAs($customerUser)->post(route('checkout.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01012345678',
            'address_line_1' => '123 Nile St',
            'variant_id' => $variant->id,
            'quantity' => 2,
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();

        $order = Order::latest()->first();
        $this->assertNotNull($order);

        $item = OrderItem::where('order_id', $order->id)->first();
        $this->assertNotNull($item);

        $this->assertEquals(100.00, (float) $item->print_cost);
        $this->assertEquals(30.00, (float) $item->artist_profit);
        $this->assertEquals(10.00, (float) $item->platform_fixed_fee);
        $this->assertEquals($artist->id, $item->artist_id);
        $this->assertEquals($variant->id, $item->variant_id);

        // Check that item unit_price equals sum of breakdown components
        $breakdownSum = (float) $item->print_cost + (float) $item->artist_profit + (float) $item->platform_cut;
        $this->assertEquals((float) $item->unit_price, round($breakdownSum, 2));

        // Check total order price matches quantity * unit_price + shipping
        $expectedTotal = ($item->unit_price * $item->quantity) + $order->shipping_amount;
        $this->assertEquals((float) $order->total_price, round($expectedTotal, 2));
    }

    public function test_order_item_uses_interpolated_artist_profit_for_most_expensive_variant(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);
        $customerUser = User::factory()->create(['role' => 'customer']);
        Customer::factory()->create([
            'user_id' => $customerUser->id,
            'name' => $customerUser->name,
            'email' => $customerUser->email,
        ]);

        $product = Product::factory()->create([
            'user_id' => $artist->id,
            'artist_min_profit' => 30.00,
            'artist_max_profit' => 60.00,
            'is_available' => true,
        ]);

        foreach ([['A4', 100.00], ['A3', 200.00]] as [$size, $cost]) {
            $variants[$size] = Variant::create([
                'product_id' => $product->id,
                'size' => $size,
                'color' => 'Black',
                'sku' => 'POSTER-'.$size.'-INTERP',
                'price' => 0,
                'print_cost' => $cost,
                'printlet_variant_id' => 'v_'.$size,
            ]);
        }
        app(RepriceProductVariants::class)->reprice($product);
        $a3 = $variants['A3']->fresh();

        $printletMock = $this->createMock(PrintletApiService::class);
        $printletMock->method('createQuote')->willReturn(['id' => 'q1', 'totals' => ['shipping' => 0]]);
        $printletMock->method('createOrder')->willReturn(['id' => 'o1', 'status' => 'pending_verification']);
        $this->app->instance(PrintletApiService::class, $printletMock);

        $this->actingAs($customerUser)->post(route('checkout.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01012345678',
            'address_line_1' => '123 Nile St',
            'variant_id' => $a3->id,
            'quantity' => 1,
            'payment_method' => 'cod',
        ]);

        $item = OrderItem::firstOrFail();
        $this->assertEquals(60.00, (float) $item->artist_profit);
        $this->assertEquals((float) $a3->price, (float) $item->unit_price);
        $this->assertEquals(
            (float) $item->unit_price,
            round((float) $item->print_cost + (float) $item->artist_profit + (float) $item->platform_cut, 2)
        );
    }
}
