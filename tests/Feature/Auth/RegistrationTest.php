<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Customer');
        $response->assertSee('Artist');
    }

    public function test_new_users_can_register_as_customer(): void
    {
        $response = $this->post('/register', [
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'customer',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('shop.index'));

        $user = User::where('email', 'customer@example.com')->first();
        $this->assertEquals('customer', $user->role);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertNotNull($user->customer);
    }

    public function test_new_users_can_register_as_artist(): void
    {
        $response = $this->post('/register', [
            'name' => 'Artist User',
            'email' => 'artist@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'artist',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'artist@example.com')->first();
        $this->assertEquals('artist', $user->role);
        $this->assertTrue($user->hasRole('artist'));
        $this->assertTrue($user->can('products.view'));
    }

    public function test_registration_rejects_unauthorized_account_types(): void
    {
        $response = $this->post('/register', [
            'name' => 'Hacker User',
            'email' => 'hacker@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'admin',
        ]);

        $response->assertSessionHasErrors('account_type');
        $this->assertGuest();
    }
}
