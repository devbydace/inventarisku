<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductTest extends TestCase
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
    // AC-01: Product Index Tests
    // =============================================

    public function test_admin_can_view_product_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/products');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_product_index(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(403);
    }

    // =============================================
    // AC-02: Product Create Tests
    // =============================================

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/products/create');
        $response->assertStatus(200);
    }

    public function test_admin_can_store_product_with_suppliers_and_prices(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier1 = Supplier::create(['name' => 'Supplier A']);
        $supplier2 = Supplier::create(['name' => 'Supplier B']);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Product\Create::class)
            ->set('name', 'Laptop Gaming')
            ->set('sku', 'LAP-001')
            ->set('category_id', $category->id)
            ->set('unit_id', $unit->id)
            ->set('supplier_ids', [$supplier1->id, $supplier2->id])
            ->set('buy_price', 5000000)
            ->set('sell_price', 7000000)
            ->set('min_stock', 5)
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'buy_price' => 5000000,
            'sell_price' => 7000000,
            'min_stock' => 5,
        ]);
    }

    public function test_product_validation_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        // Test empty name
        Livewire::test(\App\Livewire\Product\Create::class)
            ->set('sku', 'LAP-001')
            ->set('category_id', 1)
            ->set('unit_id', 1)
            ->set('supplier_ids', [1])
            ->set('buy_price', 1000)
            ->set('sell_price', 2000)
            ->call('store')
            ->assertHasErrors('name');

        // Test negative buy_price
        Livewire::test(\App\Livewire\Product\Create::class)
            ->set('name', 'Laptop')
            ->set('sku', 'LAP-001')
            ->set('category_id', 1)
            ->set('unit_id', 1)
            ->set('supplier_ids', [1])
            ->set('buy_price', -1000)
            ->set('sell_price', 2000)
            ->call('store')
            ->assertHasErrors('buy_price');

        // Test negative sell_price
        Livewire::test(\App\Livewire\Product\Create::class)
            ->set('name', 'Laptop')
            ->set('sku', 'LAP-001')
            ->set('category_id', 1)
            ->set('unit_id', 1)
            ->set('supplier_ids', [1])
            ->set('buy_price', 1000)
            ->set('sell_price', -2000)
            ->call('store')
            ->assertHasErrors('sell_price');

        // Test missing suppliers
        Livewire::test(\App\Livewire\Product\Create::class)
            ->set('name', 'Laptop')
            ->set('sku', 'LAP-001')
            ->set('category_id', 1)
            ->set('unit_id', 1)
            ->set('buy_price', 1000)
            ->set('sell_price', 2000)
            ->call('store')
            ->assertHasErrors('supplier_ids');
    }

    public function test_sku_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier = Supplier::create(['name' => 'Supplier A']);

        Product::create([
            'name' => 'Laptop 1',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'buy_price' => 5000000,
            'sell_price' => 7000000,
            'min_stock' => 5,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Product\Create::class)
            ->set('name', 'Laptop 2')
            ->set('sku', 'LAP-001')
            ->set('category_id', $category->id)
            ->set('unit_id', $unit->id)
            ->set('supplier_ids', [$supplier->id])
            ->set('buy_price', 6000000)
            ->set('sell_price', 8000000)
            ->call('store')
            ->assertHasErrors('sku');
    }

    // =============================================
    // AC-03: Product Edit Tests
    // =============================================

    public function test_admin_can_edit_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'buy_price' => 5000000,
            'sell_price' => 7000000,
            'min_stock' => 5,
        ]);

        $response = $this->actingAs($admin)->get("/admin/products/{$product->id}/edit");
        $response->assertStatus(200);
    }

    public function test_admin_can_update_product_with_suppliers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier1 = Supplier::create(['name' => 'Supplier A']);
        $supplier2 = Supplier::create(['name' => 'Supplier B']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'buy_price' => 5000000,
            'sell_price' => 7000000,
            'min_stock' => 5,
        ]);
        $product->suppliers()->attach($supplier1->id);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Product\Edit::class, ['id' => $product->id])
            ->set('name', 'Laptop Gaming Pro')
            ->set('sku', 'LAP-002')
            ->set('supplier_ids', [$supplier2->id])
            ->set('buy_price', 6000000)
            ->set('sell_price', 8000000)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Laptop Gaming Pro',
            'sku' => 'LAP-002',
            'buy_price' => 6000000,
            'sell_price' => 8000000,
        ]);

        // Check that supplier was synced
        $this->assertDatabaseHas('product_supplier', [
            'product_id' => $product->id,
            'supplier_id' => $supplier2->id,
        ]);
    }

    // =============================================
    // AC-04: Product Archive/Delete Tests
    // =============================================

    public function test_admin_can_archive_product(): void
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

        Livewire::test(\App\Livewire\Product\Index::class)
            ->call('archive', $product->id);

        // Product should be soft deleted (not in active products)
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_admin_can_delete_product_permanently(): void
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

        Livewire::test(\App\Livewire\Product\Index::class)
            ->call('delete', $product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // =============================================
    // AC-06: Authorization Tests
    // =============================================

    public function test_kasir_cannot_access_product_routes(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/products/create');
        $response->assertStatus(403);
    }

    public function test_staff_toko_cannot_access_product_routes(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(403);
    }

    // =============================================
    // AC-07: Data Display Tests
    // =============================================

    public function test_product_index_displays_suppliers_and_prices(): void
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

        Livewire::test(\App\Livewire\Product\Index::class)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('Elektronik')
            ->assertSee('Piece')
            ->assertSee('Supplier A')
            ->assertSee('5.000.000')
            ->assertSee('7.000.000');
    }
}