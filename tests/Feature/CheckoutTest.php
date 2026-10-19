<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setupCartAndUser(): array
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'address' => '123 Nile Corniche, Cairo, Egypt',
        ]);

        $product = Product::create([
            'title' => 'Vintage Cairo Poster',
            'sku' => 'POSTER-CAIRO-01',
            'price' => 250.00,
            'is_available' => true,
            'printlet_artwork_id' => 'art_test_123',
            'printlet_artwork_revision' => 1,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => '50x70',
            'color' => 'Black Wood',
            'price' => 250.00,
            'sku' => 'POSTER-CAIRO-01-5070',
            'printlet_variant_id' => 'var_5070',
            'printlet_variant_revision' => 1,
        ]);

        return [$user, $customer, $product, $variant];
    }

    public function test_checkout_page_renders_with_instapay_and_cod_options(): void
    {
        [$user, $customer, $product, $variant] = $this->setupCartAndUser();

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => ['quantity' => 1]]])
            ->get(route('checkout.show'));

        $response->assertStatus(200);
        $response->assertSee('Cash on Delivery');
        $response->assertSee('Instapay Direct Transfer');
        $response->assertSee('Your order will be saved first');
    }

    public function test_instapay_order_is_placed_first_without_losing_info(): void
    {
        Http::fake([
            '*/quotes' => Http::response([
                'id' => 'quote_mock_123',
                'status' => 'valid',
                'totals' => [
                    'items' => 250.00,
                    'shipping' => 45.00,
                    'total' => 295.00,
                ],
            ], 200),
            '*/orders' => Http::response([
                'id' => 'ord_mock_123',
                'status' => 'accepted',
            ], 200),
        ]);

        [$user, $customer, $product, $variant] = $this->setupCartAndUser();

        // Customer places order choosing Instapay WITHOUT needing reference upfront
        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => ['quantity' => 1]]])
            ->post(route('checkout.store'), [
                'payment_method' => 'instapay',
                'variant_id' => $variant->id,
                'quantity' => 1,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '01012345678',
                'address_line_1' => 'Zamalek, Cairo',
            ]);

        $order = Order::where('customer_id', $customer->id)->latest()->first();

        $this->assertNotNull($order);
        $this->assertEquals('instapay', $order->payment_method);
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals('pending_payment', $order->status);
        $this->assertNull($order->payment_reference);

        $response->assertRedirect(route('order.confirmation', $order));

        // Now visit confirmation page: customer sees transfer details, mobile link, and QR code
        $confirmPage = $this->actingAs($user)->get(route('order.confirmation', $order));
        $confirmPage->assertStatus(200);
        $confirmPage->assertSee('Complete Your Transfer for Order #'.$order->id);
        $confirmPage->assertSee('quirka@instapay');
        $confirmPage->assertSee('For Mobile Users');
        $confirmPage->assertSee('Desktop Users');
        $confirmPage->assertSee('I Have Paid — Submit Reference');

        // Customer submits payment reference after transfer
        $refResponse = $this->actingAs($user)
            ->post(route('order.instapay-reference', $order), [
                'payment_reference' => 'INSTA-REF-998877',
            ]);

        $refResponse->assertRedirect(route('order.confirmation', $order));
        $order->refresh();
        $this->assertEquals('INSTA-REF-998877', $order->payment_reference);
    }

    public function test_admin_can_verify_instapay_order_payment(): void
    {
        [$user, $customer, $product, $variant] = $this->setupCartAndUser();

        $admin = User::factory()->create(['role' => 'admin']);

        $order = Order::create([
            'customer_id' => $customer->id,
            'status' => 'pending_payment',
            'payment_method' => 'instapay',
            'payment_status' => 'unpaid',
            'payment_reference' => 'INSTA-REF-998877',
            'total_price' => 295.00,
            'shipping_amount' => 45.00,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.orders.verify-instapay', $order));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Instapay payment verified successfully.']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
    }
}
