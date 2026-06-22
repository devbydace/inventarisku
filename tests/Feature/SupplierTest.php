<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierTest extends TestCase
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
    // AC-01: Supplier CRUD Tests
    // =============================================

    public function test_admin_can_view_supplier_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/suppliers');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_supplier_index(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/suppliers');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_supplier(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/suppliers/create');
        $response->assertStatus(200);
    }

    public function test_admin_can_store_supplier(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Supplier\Create::class)
            ->set('name', 'PT. Supplier Indonesia')
            ->set('contact', 'Budi Santoso')
            ->set('address', 'Jl. Sudirman No. 123, Jakarta')
            ->set('email', 'budi@supplier.com')
            ->set('phone', '081234567890')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', ['name' => 'PT. Supplier Indonesia']);
    }

    public function test_supplier_validation_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        // Test empty name
        Livewire::test(\App\Livewire\Supplier\Create::class)
            ->set('name', '')
            ->set('email', 'test@example.com')
            ->call('store')
            ->assertHasErrors('name');

        // Test name too long
        Livewire::test(\App\Livewire\Supplier\Create::class)
            ->set('name', str_repeat('a', 256))
            ->set('email', 'test@example.com')
            ->call('store')
            ->assertHasErrors('name');

        // Test invalid email format
        Livewire::test(\App\Livewire\Supplier\Create::class)
            ->set('name', 'PT. Supplier')
            ->set('email', 'invalid-email')
            ->call('store')
            ->assertHasErrors('email');

        // Test phone too long
        Livewire::test(\App\Livewire\Supplier\Create::class)
            ->set('name', 'PT. Supplier')
            ->set('phone', str_repeat('1', 21))
            ->call('store')
            ->assertHasErrors('phone');
    }

    public function test_admin_can_edit_supplier(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $supplier = Supplier::create([
            'name' => 'PT. Supplier Indonesia',
            'contact' => 'Budi Santoso',
            'email' => 'budi@supplier.com',
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($admin)->get("/admin/suppliers/{$supplier->id}/edit");
        $response->assertStatus(200);
    }

    public function test_admin_can_update_supplier(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $supplier = Supplier::create([
            'name' => 'PT. Supplier Indonesia',
            'contact' => 'Budi Santoso',
            'email' => 'budi@supplier.com',
            'phone' => '081234567890',
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Supplier\Edit::class, ['id' => $supplier->id])
            ->set('name', 'PT. Supplier Indonesia Baru')
            ->set('contact', 'Siti Rahayu')
            ->set('email', 'siti@supplier.com')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', ['name' => 'PT. Supplier Indonesia Baru']);
    }

    public function test_admin_can_delete_supplier(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $supplier = Supplier::create([
            'name' => 'PT. Supplier Indonesia',
            'contact' => 'Budi Santoso',
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Supplier\Index::class)
            ->call('delete', $supplier->id);

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    // =============================================
    // AC-02: Authorization Tests
    // =============================================

    public function test_kasir_cannot_access_supplier_routes(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/suppliers');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/suppliers/create');
        $response->assertStatus(403);
    }

    public function test_staff_toko_cannot_access_supplier_routes(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/admin/suppliers');
        $response->assertStatus(403);
    }

    // =============================================
    // AC-03: Audit Trail Tests
    // =============================================

    public function test_create_supplier_creates_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Supplier\Create::class)
            ->set('name', 'PT. Supplier Indonesia')
            ->set('email', 'info@supplier.com')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'Supplier',
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_update_supplier_creates_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $supplier = Supplier::create([
            'name' => 'PT. Supplier Indonesia',
            'email' => 'old@supplier.com',
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Supplier\Edit::class, ['id' => $supplier->id])
            ->set('name', 'PT. Supplier Indonesia Baru')
            ->set('email', 'new@supplier.com')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'Supplier',
            'entity_id' => $supplier->id,
            'action' => 'update',
            'user_id' => $admin->id,
        ]);
    }

    public function test_delete_supplier_creates_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $supplier = Supplier::create([
            'name' => 'PT. Supplier Indonesia',
            'email' => 'info@supplier.com',
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Supplier\Index::class)
            ->call('delete', $supplier->id);

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'Supplier',
            'entity_id' => $supplier->id,
            'action' => 'delete',
            'user_id' => $admin->id,
        ]);
    }
}