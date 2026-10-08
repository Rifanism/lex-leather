<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_customer_is_forbidden_from_admin_area(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($customer)->get('/admin/products')->assertForbidden();
        $this->actingAs($customer)->get('/admin/orders')->assertForbidden();
        $this->actingAs($customer)->get('/admin/categories')->assertForbidden();
    }

    public function test_admin_can_reach_admin_area(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->actingAs($admin)->get('/admin/orders')->assertOk();
        $this->actingAs($admin)->get('/admin/categories')->assertOk();
        $this->actingAs($admin)->get('/admin/settings/payment')->assertOk();
    }
}
