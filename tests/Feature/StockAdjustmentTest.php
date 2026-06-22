<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
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
    // AC-01: Stock Adjustment Form Tests
    // =============================================

    public function test_admin_can_view_adjustment_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $response = $this->actingAs($admin)->get('/stock/adjustment');
        $response->assertStatus(200);
    }

    public function test_manager_can_view_adjustment_form(): void
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $user->assignRole('manager');
        $user->givePermissionTo('create-stock-adjustment');

        $response = $this->actingAs($user)->get('/stock/adjustment');
        $response->assertStatus(200);
    }

    public function test_audit_can_view_adjustment_form(): void
    {
        $user = User::factory()->create(['role' => 'audit', 'is_active' => true]);
        $user->assignRole('audit');
        $user->givePermissionTo('create-stock-adjustment');

        $response = $this->actingAs($user)->get('/stock/adjustment');
        $response->assertStatus(200);
    }

    public function test_kasir_cannot_view_adjustment_form(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');
        $user->givePermissionTo('create-stock-adjustment');

        $response = $this->actingAs($user)->get('/stock/adjustment');
        $response->assertStatus(403);
    }

    public function test_staff_toko_cannot_view_adjustment_form(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');
        $user->givePermissionTo('create-stock-adjustment');

        $response = $this->actingAs($user)->get('/stock/adjustment');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_adjustment(): void
    {
        $response = $this->get('/stock/adjustment');
        $response->assertRedirect('/login');
    }

    // =============================================
    // AC-02: Validation Tests
    // =============================================

    public function test_adjustment_validation_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        Livewire::actingAs($admin);

        // Test empty product
        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('physical_stock', 10)
            ->set('reason', 'Stok fisik berbeda dengan stok sistem')
            ->call('store')
            ->assertHasErrors('product_id');

        // Test negative physical stock
        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->call('store')
            ->assertHasErrors(['product_id', 'physical_stock', 'reason']);
    }

    public function test_adjustment_rejects_negative_physical_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('product_id', $product->id)
            ->set('physical_stock', -5)
            ->set('reason', 'Stok fisik berbeda dengan stok sistem')
            ->call('store')
            ->assertHasErrors('physical_stock');
    }

    public function test_adjustment_rejects_empty_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('product_id', $product->id)
            ->set('physical_stock', 10)
            ->set('reason', '')
            ->call('store')
            ->assertHasErrors('reason');
    }

    public function test_adjustment_rejects_short_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('product_id', $product->id)
            ->set('physical_stock', 10)
            ->set('reason', 'Tes') // Less than 10 chars
            ->call('store')
            ->assertHasErrors('reason');
    }

    // =============================================
    // AC-03: Adjustment Creation Tests
    // =============================================

    public function test_admin_can_create_adjustment(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('product_id', $product->id)
            ->set('physical_stock', 15)
            ->set('reason', 'Stok fisik berbeda dengan stok sistem setelah opname')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'physical_stock' => 15,
            'reason' => 'Stok fisik berbeda dengan stok sistem setelah opname',
            'status' => 'pending',
        ]);
    }

    public function test_adjustment_does_not_update_product_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('product_id', $product->id)
            ->set('physical_stock', 15)
            ->set('reason', 'Stok fisik berbeda dengan stok sistem setelah opname')
            ->call('store')
            ->assertHasNoErrors();

        // Stock should NOT be updated (still 20)
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 20,
        ]);
    }

    public function test_adjustment_form_resets_after_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('product_id', $product->id)
            ->set('physical_stock', 15)
            ->set('reason', 'Stok fisik berbeda dengan stok sistem setelah opname')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('product_id', '')
            ->assertSet('physical_stock', '')
            ->assertSet('reason', '');
    }

    // =============================================
    // AC-04: Dynamic Display Tests
    // =============================================

    public function test_adjustment_displays_system_stock_info(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 15,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('product_id', $product->id)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('15')
            ->assertSee('PCS')
            ->assertSee('Stok Sistem Saat Ini');
    }

    // =============================================
    // AC-05: Audit Trail Tests
    // =============================================

    public function test_adjustment_creates_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('create-stock-adjustment');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Adjustment::class)
            ->set('product_id', $product->id)
            ->set('physical_stock', 15)
            ->set('reason', 'Stok fisik berbeda dengan stok sistem setelah opname')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'StockAdjustment',
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }
}