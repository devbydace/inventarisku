<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles
        $this->seed(PermissionSeeder::class);

        // Reset Spatie permission cache to ensure fresh state
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    // =============================================
    // AC-02: Middleware & Authorization
    // =============================================

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));

        $response = $this->get('/admin/products');
        $response->assertRedirect(route('login'));

        $response = $this->get('/approvals');
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_permission_gets_403(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_all_routes(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $user->assignRole('admin');

        // Dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Admin routes
        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/admin/categories');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/admin/suppliers');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/admin/units');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/admin/settings');
        $response->assertStatus(200);

        // Stock routes
        $response = $this->actingAs($user)->get('/stock/in');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/stock/out');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/stock/adjustment');
        $response->assertStatus(200);

        // Approvals
        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(200);

        // Reports
        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/reports/stock');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/reports/transactions');
        $response->assertStatus(200);
    }

    // =============================================
    // AC-05: Security Testing - Kasir
    // =============================================

    public function test_kasir_gets_403_on_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/categories');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/suppliers');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/units');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/settings');
        $response->assertStatus(403);
    }

    public function test_kasir_can_access_stock_in_and_out(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/stock/in');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/stock/out');
        $response->assertStatus(200);
    }

    public function test_kasir_gets_403_on_approvals(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(403);
    }

    // =============================================
    // AC-05: Security Testing - Staff Toko
    // =============================================

    public function test_staff_toko_gets_403_on_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/categories');
        $response->assertStatus(403);
    }

    public function test_staff_toko_gets_403_on_approvals(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(403);
    }

    public function test_staff_toko_can_access_stock_routes(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/stock/in');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/stock/out');
        $response->assertStatus(200);
    }

    // =============================================
    // AC-05: Security Testing - Manager & Audit
    // =============================================

    public function test_manager_can_access_approvals(): void
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $user->assignRole('manager');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(200);
    }

    public function test_audit_can_access_approvals(): void
    {
        $user = User::factory()->create(['role' => 'audit', 'is_active' => true]);
        $user->assignRole('audit');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(200);
    }

    public function test_manager_gets_403_on_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $user->assignRole('manager');

        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_manager_can_access_reports(): void
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $user->assignRole('manager');

        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/reports/stock');
        $response->assertStatus(200);
    }

    // =============================================
    // AC-04: Permission Assignment
    // =============================================

    public function test_permission_check_works_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $kasir->assignRole('kasir');

        // Admin has all permissions
        $this->assertTrue($admin->hasPermissionTo('approve-transaction'));
        $this->assertTrue($admin->hasPermissionTo('manage-products'));
        $this->assertTrue($admin->hasPermissionTo('view-dashboard'));

        // Kasir does not have admin permissions
        $this->assertFalse($kasir->hasPermissionTo('approve-transaction'));
        $this->assertFalse($kasir->hasPermissionTo('manage-products'));
        $this->assertTrue($kasir->hasPermissionTo('view-dashboard'));
        $this->assertTrue($kasir->hasPermissionTo('create-stock-in'));
    }

    public function test_role_check_works_correctly(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('kasir'));
    }
}