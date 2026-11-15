<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PricingSetting;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function makeProduct(string $sku, array $variantDefs): array
    {
        $product = Product::factory()->create(['sku' => $sku, 'is_available' => true]);
        $variants = [];
        foreach ($variantDefs as $i => [$size, $color, $cost, $price]) {
            $variants[] = Variant::create([
                'product_id' => $product->id,
                'size' => $size,
                'color' => $color,
                'sku' => $sku.'-'.$i,
                'price' => $price,
                'print_cost' => $cost,
                'printlet_variant_id' => 'v_'.$sku.'_'.$i,
            ]);
        }

        return [$product, $variants];
    }

    public function test_two_variants_of_the_same_product_stay_as_separate_cart_lines(): void
    {
        [$product, [$a4, $a3]] = $this->makeProduct('P1', [['A4', 'Black', 100, 155], ['A3', 'White', 200, 300]]);

        $this->postJson(route('cart.add', $product), ['quantity' => 1, 'variant_id' => $a4->id])->assertOk();
        $response = $this->postJson(route('cart.add', $product), ['quantity' => 2, 'variant_id' => $a3->id]);

        $response->assertOk()->assertJsonPath('cartCount', 3)->assertJsonPath('item.size', 'A3')->assertJsonPath('item.unit_price', 300);

        $cart = $this->get(route('cart.index'))->assertOk()->viewData('items');
        $this->assertCount(2, $cart);
        $this->assertEquals(155 + 600, $cart->sum('line_total'));
    }

    public function test_adding_same_variant_again_increases_quantity(): void
    {
        [$product, [$a4]] = $this->makeProduct('P2', [['A4', 'Black', 100, 155]]);

        $this->postJson(route('cart.add', $product), ['quantity' => 1, 'variant_id' => $a4->id]);
        $this->postJson(route('cart.add', $product), ['quantity' => 2, 'variant_id' => $a4->id]);

        $items = $this->get(route('cart.index'))->viewData('items');
        $this->assertCount(1, $items);
        $this->assertEquals(3, $items->first()['quantity']);
    }

    public function test_updating_quantity_keeps_the_selected_variant(): void
    {
        [$product, [$a4, $a3]] = $this->makeProduct('P3', [['A4', 'Black', 100, 155], ['A3', 'White', 200, 300]]);

        $this->postJson(route('cart.add', $product), ['quantity' => 1, 'variant_id' => $a4->id]);
        $this->postJson(route('cart.add', $product), ['quantity' => 1, 'variant_id' => $a3->id]);

        $this->patch(route('cart.update', $product), ['quantity' => 5, 'variant_id' => $a3->id])->assertRedirect();

        $items = $this->get(route('cart.index'))->viewData('items');
        $byVariant = $items->keyBy(fn ($i) => $i['variant']->id);
        $this->assertEquals(1, $byVariant[$a4->id]['quantity']);
        $this->assertEquals(5, $byVariant[$a3->id]['quantity']);
        $this->assertEquals(300.0, $byVariant[$a3->id]['unit_price']);
    }

    public function test_removing_one_variant_leaves_the_other(): void
    {
        [$product, [$a4, $a3]] = $this->makeProduct('P4', [['A4', 'Black', 100, 155], ['A3', 'White', 200, 300]]);

        $this->postJson(route('cart.add', $product), ['quantity' => 1, 'variant_id' => $a4->id]);
        $this->postJson(route('cart.add', $product), ['quantity' => 1, 'variant_id' => $a3->id]);

        $this->delete(route('cart.remove', $product), ['variant_id' => $a4->id])->assertRedirect();

        $items = $this->get(route('cart.index'))->viewData('items');
        $this->assertCount(1, $items);
        $this->assertEquals($a3->id, $items->first()['variant']->id);
    }

    public function test_variant_from_another_product_is_rejected(): void
    {
        [$product] = $this->makeProduct('P5', [['A4', 'Black', 100, 155]]);
        [, [$foreign]] = $this->makeProduct('P6', [['A3', 'White', 200, 300]]);

        $this->postJson(route('cart.add', $product), ['quantity' => 1, 'variant_id' => $foreign->id])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertCount(0, $this->get(route('cart.index'))->viewData('items'));
    }

    protected function customerUser(): User
    {
        $user = User::factory()->create(['role' => 'customer']);
        Customer::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'address' => 'Cairo']);

        return $user;
    }

    protected function checkoutPayload(array $extra = []): array
    {
        return array_merge([
            'payment_method' => 'cod',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01012345678',
            'address_line_1' => '123 Nile St',
        ], $extra);
    }

    public function test_checkout_orders_every_cart_line_and_quotes_all_of_them(): void
    {
        PricingSetting::current();
        Http::fake([
            '*/quotes' => Http::response(['id' => 'q1', 'totals' => ['shipping' => 45.00]], 200),
            '*/orders' => Http::response(['id' => 'o1', 'status' => 'pending_verification'], 200),
        ]);

        [$p1, [$a4, $a3]] = $this->makeProduct('M1', [['A4', 'Black', 100, 155], ['A3', 'White', 200, 300]]);
        [$p2, [$b4]] = $this->makeProduct('M2', [['A4', 'Black', 100, 155]]);
        $user = $this->customerUser();

        $this->actingAs($user);
        $this->postJson(route('cart.add', $p1), ['quantity' => 1, 'variant_id' => $a4->id]);
        $this->postJson(route('cart.add', $p1), ['quantity' => 2, 'variant_id' => $a3->id]);
        $this->postJson(route('cart.add', $p2), ['quantity' => 1, 'variant_id' => $b4->id]);

        $this->get(route('checkout.show'))->assertOk()->assertSee('EGP');
        $this->post(route('checkout.store'), $this->checkoutPayload())->assertRedirect();

        $order = Order::firstOrFail();
        $items = OrderItem::where('order_id', $order->id)->get();
        $this->assertCount(3, $items);
        $this->assertEqualsCanonicalizing([$a4->id, $a3->id, $b4->id], $items->pluck('variant_id')->all());
        $this->assertEquals(155 + 600 + 155 + 45, (float) $order->total_price);

        Http::assertSent(function (HttpRequest $request) {
            if (! str_ends_with($request->url(), '/quotes')) {
                return false;
            }

            return count($request['items']) === 3
                && collect($request['items'])->pluck('configuration.quantity')->sort()->values()->all() === [1, 1, 2];
        });
        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/orders') && $r['end_customer_payment']['collection_amount'] === '955.00');

        $this->assertEmpty(session('cart'));
    }

    public function test_checkout_with_cart_line_that_has_no_variant_does_not_invent_one(): void
    {
        Http::fake();
        $product = Product::factory()->create(['is_available' => true]);
        $user = $this->customerUser();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => ['quantity' => 1, 'size' => 'M', 'color' => 'Default']]])
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseMissing('variants', ['printlet_variant_id' => 'v_default']);
        Http::assertNothingSent();
    }
}
