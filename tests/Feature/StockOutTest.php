<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOutTest extends TestCase
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
    // AC-01: Stock Out Form Display Tests
    // =============================================

    public function test_admin_can_view_stock_out_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/stock/out');
        $response->assertStatus(200);
    }

    public function test_kasir_can_view_stock_out_form(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/stock/out');
        $response->assertStatus(200);
    }

    public function test_staff_toko_can_view_stock_out_form(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/stock/out');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_stock_out(): void
    {
        $response = $this->get('/stock/out');
        $response->assertRedirect('/login');
    }

    public function test_stock_out_form_shows_only_products_with_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);

        // Product with stock
        $productWithStock = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 10,
            'is_active' => true,
        ]);

        // Product without stock
        $productWithoutStock = Product::create([
            'name' => 'Mouse',
            'sku' => 'MOU-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        $response = Livewire::test(\App\Livewire\Stock\Out::class)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertDontSee('Mouse');
    }

    // =============================================
    // AC-02: Stock Out Validation Tests
    // =============================================

    public function test_stock_out_validation_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        // Test empty product
        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('quantity', 10)
            ->set('reason', 'penjualan')
            ->call('store')
            ->assertHasErrors('product_id');

        // Test quantity = 0
        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', 1)
            ->set('quantity', 0)
            ->set('reason', 'penjualan')
            ->call('store')
            ->assertHasErrors('quantity');

        // Test negative quantity
        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', 1)
            ->set('quantity', -5)
            ->set('reason', 'penjualan')
            ->call('store')
            ->assertHasErrors('quantity');

        // Test missing reason
        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', 1)
            ->set('quantity', 10)
            ->call('store')
            ->assertHasErrors('reason');
    }

    // =============================================
    // AC-03: Stock Availability Validation Tests
    // =============================================

    public function test_stock_out_validates_availability(): void
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
            'current_stock' => 5,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        // Try to take out more than available
        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', $product->id)
            ->set('quantity', 10)
            ->set('reason', 'penjualan')
            ->call('store')
            ->assertHasErrors('quantity');
    }

    public function test_stock_out_rejects_when_stock_is_zero(): void
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
            'current_stock' => 0,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        // Product with 0 stock should not appear in dropdown
        $component = Livewire::test(\App\Livewire\Stock\Out::class);
        $products = $component->get('products');
        
        $this->assertNotContains($product->id, $products->pluck('id')->toArray());
    }

    // =============================================
    // AC-04: Stock Out Submission Tests
    // =============================================

    public function test_admin_can_create_stock_out_transaction(): void
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
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', $product->id)
            ->set('quantity', 5)
            ->set('reason', 'penjualan')
            ->set('notes', 'Penjualan ke customer')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stock_transactions', [
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 5,
            'reason' => 'penjualan',
            'notes' => 'Penjualan ke customer',
            'status' => 'pending',
        ]);
    }

    public function test_stock_out_does_not_update_product_stock(): void
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
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', $product->id)
            ->set('quantity', 5)
            ->set('reason', 'penjualan')
            ->call('store')
            ->assertHasNoErrors();

        // Stock should NOT be updated (still 20)
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 20,
        ]);
    }

    public function test_stock_out_form_resets_after_submission(): void
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
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', $product->id)
            ->set('quantity', 5)
            ->set('reason', 'penjualan')
            ->set('notes', 'Test')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('product_id', '')
            ->assertSet('quantity', '')
            ->assertSet('reason', '')
            ->assertSet('notes', '');
    }

    // =============================================
    // AC-05: Dynamic Display Tests
    // =============================================

    public function test_stock_out_displays_product_info(): void
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
            'current_stock' => 15,
            'sell_price' => 7000000,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        $response = Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', $product->id)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('15')
            ->assertSee('PCS')
            ->assertSee('7.000.000');
    }

    // =============================================
    // AC-06: Authorization Tests
    // =============================================

    public function test_manager_can_access_stock_out(): void
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $user->assignRole('manager');
        $user->givePermissionTo('create-stock-out');

        $response = $this->actingAs($user)->get('/stock/out');
        $response->assertStatus(200);
    }

    public function test_audit_can_access_stock_out(): void
    {
        $user = User::factory()->create(['role' => 'audit', 'is_active' => true]);
        $user->assignRole('audit');
        $user->givePermissionTo('create-stock-out');

        $response = $this->actingAs($user)->get('/stock/out');
        $response->assertStatus(200);
    }

    // =============================================
    // AC-07: Audit Trail Tests
    // =============================================

    public function test_stock_out_creates_audit_trail(): void
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
            'current_stock' => 20,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', $product->id)
            ->set('quantity', 5)
            ->set('reason', 'penjualan')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'StockTransaction',
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    // =============================================
    // AC-08: Data Display Tests
    // =============================================

    public function test_stock_out_form_displays_product_info_correctly(): void
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
            'current_stock' => 10,
            'sell_price' => 7000000,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        $response = Livewire::test(\App\Livewire\Stock\Out::class)
            ->set('product_id', $product->id)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('10')
            ->assertSee('PCS')
            ->assertSee('Elektronik');
    }
}