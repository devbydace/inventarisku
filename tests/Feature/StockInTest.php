<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockInTest extends TestCase
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
    // AC-01: Stock In Form Display Tests
    // =============================================

    public function test_admin_can_view_stock_in_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/stock/in');
        $response->assertStatus(200);
    }

    public function test_kasir_can_view_stock_in_form(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/stock/in');
        $response->assertStatus(200);
    }

    public function test_staff_toko_can_view_stock_in_form(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/stock/in');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_stock_in(): void
    {
        $response = $this->get('/stock/in');
        $response->assertRedirect('/login');
    }

    // =============================================
    // AC-02: Stock In Validation Tests
    // =============================================

    public function test_stock_in_validation_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        // Test empty product
        Livewire::test(\App\Livewire\Stock\In::class)
            ->set('quantity', 10)
            ->set('supplier_id', 1)
            ->call('store')
            ->assertHasErrors('product_id');

        // Test quantity = 0
        Livewire::test(\App\Livewire\Stock\In::class)
            ->set('product_id', 1)
            ->set('quantity', 0)
            ->set('supplier_id', 1)
            ->call('store')
            ->assertHasErrors('quantity');

        // Test negative quantity
        Livewire::test(\App\Livewire\Stock\In::class)
            ->set('product_id', 1)
            ->set('quantity', -5)
            ->set('supplier_id', 1)
            ->call('store')
            ->assertHasErrors('quantity');

        // Test missing supplier
        Livewire::test(\App\Livewire\Stock\In::class)
            ->set('product_id', 1)
            ->set('quantity', 10)
            ->call('store')
            ->assertHasErrors('supplier_id');
    }

    // =============================================
    // AC-03: Stock In Submission Tests
    // =============================================

    public function test_admin_can_create_stock_in_transaction(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'buy_price' => 5000000,
            'sell_price' => 7000000,
            'min_stock' => 5,
            'is_active' => true,
        ]);
        $product->suppliers()->attach($supplier->id);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\In::class)
            ->set('product_id', $product->id)
            ->set('quantity', 10)
            ->set('supplier_id', $supplier->id)
            ->set('reference_no', 'PO-001')
            ->set('notes', 'Stok awal')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stock_transactions', [
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 10,
            'supplier_id' => $supplier->id,
            'reference_no' => 'PO-001',
            'notes' => 'Stok awal',
            'status' => 'pending',
        ]);
    }

    public function test_stock_in_does_not_update_product_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'buy_price' => 5000000,
            'sell_price' => 7000000,
            'min_stock' => 5,
            'current_stock' => 0,
            'is_active' => true,
        ]);
        $product->suppliers()->attach($supplier->id);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\In::class)
            ->set('product_id', $product->id)
            ->set('quantity', 10)
            ->set('supplier_id', $supplier->id)
            ->call('store')
            ->assertHasNoErrors();

        // Stock should NOT be updated (still 0)
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 0,
        ]);
    }

    public function test_stock_in_form_resets_after_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'buy_price' => 5000000,
            'sell_price' => 7000000,
            'min_stock' => 5,
            'is_active' => true,
        ]);
        $product->suppliers()->attach($supplier->id);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\In::class)
            ->set('product_id', $product->id)
            ->set('quantity', 10)
            ->set('supplier_id', $supplier->id)
            ->set('reference_no', 'PO-001')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('product_id', '')
            ->assertSet('quantity', '')
            ->assertSet('supplier_id', '')
            ->assertSet('reference_no', '')
            ->assertSet('notes', '');
    }

    // =============================================
    // AC-04: Dynamic Dropdown Tests
    // =============================================

    public function test_supplier_dropdown_filters_by_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier1 = Supplier::create(['name' => 'Supplier A']);
        $supplier2 = Supplier::create(['name' => 'Supplier B']);
        $supplier3 = Supplier::create(['name' => 'Supplier C']);

        $product1 = Product::create([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
        $product1->suppliers()->attach([$supplier1->id, $supplier2->id]);

        $product2 = Product::create([
            'name' => 'Mouse',
            'sku' => 'MOU-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
        $product2->suppliers()->attach([$supplier2->id, $supplier3->id]);

        Livewire::actingAs($admin);

        // Test with product1 selected
        $component = Livewire::test(\App\Livewire\Stock\In::class);
        $component->set('product_id', $product1->id);
        
        // Suppliers should be filtered to only supplier1 and supplier2
        $this->assertTrue(
            in_array($supplier1->id, $component->get('suppliers')->pluck('id')->toArray())
        );
        $this->assertTrue(
            in_array($supplier2->id, $component->get('suppliers')->pluck('id')->toArray())
        );
    }

    // =============================================
    // AC-05: Authorization Tests
    // =============================================

    public function test_manager_can_access_stock_in(): void
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $user->assignRole('manager');
        $user->givePermissionTo('create-stock-in');

        $response = $this->actingAs($user)->get('/stock/in');
        $response->assertStatus(200);
    }

    public function test_audit_can_access_stock_in(): void
    {
        $user = User::factory()->create(['role' => 'audit', 'is_active' => true]);
        $user->assignRole('audit');
        $user->givePermissionTo('create-stock-in');

        $response = $this->actingAs($user)->get('/stock/in');
        $response->assertStatus(200);
    }

    // =============================================
    // AC-06: Audit Trail Tests
    // =============================================

    public function test_stock_in_creates_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier = Supplier::create(['name' => 'Supplier A']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);
        $product->suppliers()->attach($supplier->id);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\In::class)
            ->set('product_id', $product->id)
            ->set('quantity', 10)
            ->set('supplier_id', $supplier->id)
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'StockTransaction',
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    // =============================================
    // AC-07: Data Display Tests
    // =============================================

    public function test_stock_in_form_displays_product_info(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier = Supplier::create(['name' => 'Supplier A', 'phone' => '081234567890']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 5,
            'is_active' => true,
        ]);
        $product->suppliers()->attach($supplier->id);

        Livewire::actingAs($admin);

        $response = Livewire::test(\App\Livewire\Stock\In::class)
            ->set('product_id', $product->id)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('5')
            ->assertSee('PCS');
    }
}