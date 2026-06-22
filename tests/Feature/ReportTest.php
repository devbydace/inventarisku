<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTest extends TestCase
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
    // AC-04: Authorization Tests
    // =============================================

    public function test_admin_can_view_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $response = $this->actingAs($admin)->get('/reports');
        $response->assertStatus(200);
    }

    public function test_kasir_can_view_reports(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');
        $user->givePermissionTo('view-reports');

        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_reports(): void
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login');
    }

    // =============================================
    // AC-01: Laporan Stok Saat Ini (On-Hand) Tests
    // =============================================

    public function test_stock_on_hand_displays_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 10,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Report\StockOnHand::class)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('Elektronik')
            ->assertSee('10')
            ->assertSee('5')
            ->assertSee('Normal');
    }

    public function test_stock_on_hand_shows_low_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 3,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Report\StockOnHand::class)
            ->assertSee('Low Stock');
    }

    public function test_stock_on_hand_empty_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Report\StockOnHand::class)
            ->assertSee('Tidak ada data stok');
    }

    public function test_stock_on_hand_filter_by_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $category1 = Category::create(['name' => 'Elektronik']);
        $category2 = Category::create(['name' => 'Furniture']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);

        Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category1->id,
            'unit_id' => $unit->id,
            'current_stock' => 10,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Meja Kantor',
            'sku' => 'MEJA-001',
            'category_id' => $category2->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        // Filter by category1
        Livewire::test(\App\Livewire\Report\StockOnHand::class)
            ->set('category_id', $category1->id)
            ->assertSee('Laptop Gaming')
            ->assertDontSee('Meja Kantor');
    }

    // =============================================
    // AC-02: Laporan Mutasi Stok Tests
    // =============================================

    public function test_stock_mutation_displays_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 10,
            'status' => 'approved',
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Report\StockMutation::class)
            ->assertSee('Laptop Gaming')
            ->assertSee('10')
            ->assertSee('Stok Masuk')
            ->assertSee('Approved');
    }

    public function test_stock_mutation_filters_by_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 10,
            'status' => 'approved',
            'created_at' => now()->subDays(5),
        ]);

        Livewire::actingAs($admin);

        // Filter to exclude the transaction
        Livewire::test(\App\Livewire\Report\StockMutation::class)
            ->set('date_from', now()->subDay()->format('Y-m-d'))
            ->assertDontSee('10');
    }

    public function test_stock_mutation_empty_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Report\StockMutation::class)
            ->assertSee('Tidak ada mutasi stok untuk filter yang dipilih');
    }

    // =============================================
    // AC-03: Laporan Low Stock Tests
    // =============================================

    public function test_low_stock_displays_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 3,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Report\LowStock::class)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('3')
            ->assertSee('5')
            ->assertSee('2'); // Selisih
    }

    public function test_low_stock_empty_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 10,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Report\LowStock::class)
            ->assertSee('Tidak ada barang dengan stok di bawah minimum');
    }

    public function test_low_stock_sorts_by_lowest_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('view-reports');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);

        Product::create([
            'name' => 'Produk A',
            'sku' => 'PRD-A',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 1,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Produk B',
            'sku' => 'PRD-B',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 3,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin);

        $component = Livewire::test(\App\Livewire\Report\LowStock::class);
        $component->assertSeeInOrder(['Produk A', 'Produk B']);
    }
}