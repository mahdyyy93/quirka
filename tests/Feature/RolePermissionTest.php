<?php

namespace Tests\Feature;

use App\Livewire\Admin\RoleManager;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $adminRole = Role::findByName('admin');
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'role_id' => $adminRole->id,
        ]);
    }

    public function test_guests_cannot_access_roles_page(): void
    {
        $response = $this->get(route('roles.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_customers_cannot_access_roles_page(): void
    {
        $customerRole = Role::findByName('customer');
        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => $customerRole->id,
        ]);

        $response = $this->actingAs($customer)->get(route('roles.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_roles_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('roles.index'));
        $response->assertOk();
        $response->assertSee('Roles & Permissions');
    }

    public function test_admin_can_create_new_role_via_livewire(): void
    {
        Livewire::actingAs($this->admin)
            ->test(RoleManager::class)
            ->set('roleName', 'auditor')
            ->call('saveRole')
            ->assertSet('showRoleModal', false);

        $this->assertDatabaseHas('roles', [
            'name' => 'auditor',
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_can_create_dynamic_permission_via_livewire(): void
    {
        Livewire::actingAs($this->admin)
            ->test(RoleManager::class)
            ->set('permissionName', 'analytics.export')
            ->set('permissionGroup', 'Analytics')
            ->call('savePermission')
            ->assertSet('showPermissionModal', false);

        $this->assertDatabaseHas('permissions', [
            'name' => 'analytics.export',
            'group_name' => 'Analytics',
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_can_toggle_and_assign_permissions_to_role(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $permission = Permission::findByName('products.view');

        Livewire::actingAs($this->admin)
            ->test(RoleManager::class)
            ->call('selectRoleForMatrix', $role->id)
            ->call('togglePermission', 'products.view');

        $this->assertTrue($role->fresh()->hasPermissionTo('products.view'));
    }

    public function test_single_role_assignment_to_user_syncs_role_id_and_spatie(): void
    {
        $managerRole = Role::findByName('manager');

        $user = User::factory()->create(['role' => 'customer']);

        Livewire::actingAs($this->admin)
            ->test(RoleManager::class)
            ->call('updateUserRole', $user->id, $managerRole->id);

        $user->refresh();

        $this->assertEquals($managerRole->id, $user->role_id);
        $this->assertEquals('manager', $user->role);
        $this->assertTrue($user->hasRole('manager'));
        $this->assertTrue($user->can('products.view'));
    }

    public function test_admin_role_cannot_be_deleted(): void
    {
        $adminRole = Role::findByName('admin');

        Livewire::actingAs($this->admin)
            ->test(RoleManager::class)
            ->call('deleteRole', $adminRole->id)
            ->assertSet('flashType', 'error');

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }

    public function test_super_admin_bypass_allows_any_ability(): void
    {
        $this->assertTrue($this->admin->can('products.view'));
        $this->assertTrue($this->admin->can('any.arbitrary.permission'));
    }

    public function test_customer_with_products_permission_sees_products_tab_and_can_access_dashboard(): void
    {
        $customerRole = Role::findByName('customer');
        $customerRole->givePermissionTo('products.view');

        $customer = User::factory()->create([
            'role' => 'customer',
            'role_id' => $customerRole->id,
        ]);

        $response = $this->actingAs($customer)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Products');
    }

    public function test_artist_can_only_view_their_own_products(): void
    {
        $artistRole = Role::findByName('artist');

        $artistA = User::factory()->create([
            'role' => 'artist',
            'role_id' => $artistRole->id,
        ]);

        $artistB = User::factory()->create([
            'role' => 'artist',
            'role_id' => $artistRole->id,
        ]);

        $productA = Product::factory()->create([
            'user_id' => $artistA->id,
            'title' => 'Artist A Painting',
        ]);

        $productB = Product::factory()->create([
            'user_id' => $artistB->id,
            'title' => 'Artist B Sculpture',
        ]);

        // Artist A query should only return Product A
        $responseA = $this->actingAs($artistA)->getJson('/admin/products');
        $responseA->assertOk();
        $responseA->assertJsonCount(1, 'data');
        $responseA->assertJsonFragment(['id' => $productA->id]);
        $responseA->assertJsonMissing(['id' => $productB->id]);

        // Admin query should return both products (has products.view-all)
        $responseAdmin = $this->actingAs($this->admin)->getJson('/admin/products');
        $responseAdmin->assertOk();
        $responseAdmin->assertJsonCount(2, 'data');
    }

    public function test_artist_cannot_update_or_delete_another_artists_product(): void
    {
        $artistRole = Role::findByName('artist');

        $artistA = User::factory()->create([
            'role' => 'artist',
            'role_id' => $artistRole->id,
        ]);

        $artistB = User::factory()->create([
            'role' => 'artist',
            'role_id' => $artistRole->id,
        ]);

        $productB = Product::factory()->create([
            'user_id' => $artistB->id,
            'title' => 'Artist B Masterpiece',
        ]);

        // Artist A tries to update Artist B's product
        $updateResponse = $this->actingAs($artistA)->putJson("/admin/products/{$productB->id}", [
            'title' => 'Hacked title',
            'sku' => $productB->sku,
            'price' => 100,
        ]);
        $updateResponse->assertStatus(403);

        // Artist A tries to delete Artist B's product
        $deleteResponse = $this->actingAs($artistA)->deleteJson("/admin/products/{$productB->id}");
        $deleteResponse->assertStatus(403);

        // Artist B can update their own product
        $validUpdateResponse = $this->actingAs($artistB)->putJson("/admin/products/{$productB->id}", [
            'title' => 'Updated by B',
            'sku' => $productB->sku,
            'price' => 120,
        ]);
        $validUpdateResponse->assertOk();
    }

    public function test_artist_can_only_view_orders_containing_their_products(): void
    {
        $artistRole = Role::findByName('artist');

        $artistA = User::factory()->create(['role' => 'artist', 'role_id' => $artistRole->id]);
        $artistB = User::factory()->create(['role' => 'artist', 'role_id' => $artistRole->id]);
        $customerUser = User::factory()->create(['role' => 'customer']);

        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'name' => 'Jane Buyer',
            'email' => 'jane@example.com',
        ]);

        $productA = Product::factory()->create([
            'user_id' => $artistA->id,
            'title' => 'Artwork A',
            'price' => 100,
        ]);

        $productB = Product::factory()->create([
            'user_id' => $artistB->id,
            'title' => 'Artwork B',
            'price' => 200,
        ]);

        // Order 1 contains Product A (Artist A)
        $order1 = Order::create([
            'customer_id' => $customer->id,
            'status' => 'pending',
            'total_price' => 100,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $productA->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        // Order 2 contains Product B (Artist B)
        $order2 = Order::create([
            'customer_id' => $customer->id,
            'status' => 'processing',
            'total_price' => 200,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $productB->id,
            'quantity' => 1,
            'unit_price' => 200,
        ]);

        // Artist A should only see Order 1
        $responseA = $this->actingAs($artistA)->getJson('/admin/orders');
        $responseA->assertOk();
        $responseA->assertJsonCount(1, 'data');
        $responseA->assertJsonFragment(['id' => $order1->id]);
        $responseA->assertJsonMissing(['id' => $order2->id]);

        // Artist B should only see Order 2
        $responseB = $this->actingAs($artistB)->getJson('/admin/orders');
        $responseB->assertOk();
        $responseB->assertJsonCount(1, 'data');
        $responseB->assertJsonFragment(['id' => $order2->id]);
        $responseB->assertJsonMissing(['id' => $order1->id]);

        // Admin sees both orders (orders.view-all)
        $responseAdmin = $this->actingAs($this->admin)->getJson('/admin/orders');
        $responseAdmin->assertOk();
        $responseAdmin->assertJsonCount(2, 'data');
    }

    public function test_artist_cannot_update_order_status_or_verify_orders(): void
    {
        $artistRole = Role::findByName('artist');
        $artist = User::factory()->create(['role' => 'artist', 'role_id' => $artistRole->id]);
        $customerUser = User::factory()->create(['role' => 'customer']);

        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'name' => 'John Buyer',
            'email' => 'john@example.com',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'status' => 'pending',
            'total_price' => 50,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);

        // Artist cannot update order status
        $statusResponse = $this->actingAs($artist)->patchJson("/admin/orders/{$order->id}/status", [
            'status' => 'shipped',
        ]);
        $statusResponse->assertStatus(403);

        // Artist cannot verify COD order
        $codResponse = $this->actingAs($artist)->postJson("/admin/orders/{$order->id}/verify-cod");
        $codResponse->assertStatus(403);

        // Artist cannot verify Instapay payment
        $instapayResponse = $this->actingAs($artist)->postJson("/admin/orders/{$order->id}/verify-instapay");
        $instapayResponse->assertStatus(403);
    }
}
