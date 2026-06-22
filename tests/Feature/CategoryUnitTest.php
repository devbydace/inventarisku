<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryUnitTest extends TestCase
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
    // AC-01: Category CRUD Tests
    // =============================================

    public function test_admin_can_view_category_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/categories');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_category_index(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/categories');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/categories/create');
        $response->assertStatus(200);
    }

    public function test_admin_can_store_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Category\Create::class)
            ->set('name', 'Elektronik')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Elektronik']);
    }

    public function test_category_name_validation_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        // Test empty name
        Livewire::test(\App\Livewire\Category\Create::class)
            ->set('name', '')
            ->call('store')
            ->assertHasErrors('name');

        // Test name too long
        Livewire::test(\App\Livewire\Category\Create::class)
            ->set('name', str_repeat('a', 101))
            ->call('store')
            ->assertHasErrors('name');
    }

    public function test_category_name_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Category::create(['name' => 'Elektronik']);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Category\Create::class)
            ->set('name', 'Elektronik')
            ->call('store')
            ->assertHasErrors('name');
    }

    public function test_admin_can_edit_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);

        $response = $this->actingAs($admin)->get("/admin/categories/{$category->id}/edit");
        $response->assertStatus(200);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Category\Edit::class, ['id' => $category->id])
            ->set('name', 'Elektronik & Gadget')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Elektronik & Gadget']);
    }

    public function test_admin_can_delete_category_without_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Category\Index::class)
            ->call('delete', $category->id);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_with_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        
        // Create a product in this category
        \App\Models\Product::create([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'stock' => 10,
            'min_stock' => 5,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Category\Index::class)
            ->call('delete', $category->id);

        // Category should still exist because it has products
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    // =============================================
    // AC-02: Unit CRUD Tests
    // =============================================

    public function test_admin_can_view_unit_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/units');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_unit_index(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/units');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/units/create');
        $response->assertStatus(200);
    }

    public function test_admin_can_store_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Unit\Create::class)
            ->set('name', 'Piece')
            ->set('abbreviation', 'PCS')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('units', ['name' => 'Piece', 'abbreviation' => 'PCS']);
    }

    public function test_unit_validation_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Livewire::actingAs($admin);

        // Test empty name
        Livewire::test(\App\Livewire\Unit\Create::class)
            ->set('name', '')
            ->set('abbreviation', 'PCS')
            ->call('store')
            ->assertHasErrors('name');

        // Test name too long
        Livewire::test(\App\Livewire\Unit\Create::class)
            ->set('name', str_repeat('a', 51))
            ->set('abbreviation', 'PCS')
            ->call('store')
            ->assertHasErrors('name');

        // Test empty abbreviation
        Livewire::test(\App\Livewire\Unit\Create::class)
            ->set('name', 'Piece')
            ->set('abbreviation', '')
            ->call('store')
            ->assertHasErrors('abbreviation');

        // Test abbreviation too long
        Livewire::test(\App\Livewire\Unit\Create::class)
            ->set('name', 'Piece')
            ->set('abbreviation', str_repeat('a', 11))
            ->call('store')
            ->assertHasErrors('abbreviation');
    }

    public function test_unit_abbreviation_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Unit\Create::class)
            ->set('name', 'Pieces')
            ->set('abbreviation', 'PCS')
            ->call('store')
            ->assertHasErrors('abbreviation');
    }

    public function test_admin_can_edit_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);

        $response = $this->actingAs($admin)->get("/admin/units/{$unit->id}/edit");
        $response->assertStatus(200);
    }

    public function test_admin_can_update_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Unit\Edit::class, ['id' => $unit->id])
            ->set('name', 'Buah')
            ->set('abbreviation', 'B')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('units', ['name' => 'Buah', 'abbreviation' => 'B']);
    }

    public function test_admin_can_delete_unit_without_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Unit\Index::class)
            ->call('delete', $unit->id);

        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }

    public function test_admin_cannot_delete_unit_with_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        
        // Create a product with this unit
        \App\Models\Product::create([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'stock' => 10,
            'min_stock' => 5,
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Unit\Index::class)
            ->call('delete', $unit->id);

        // Unit should still exist because it has products
        $this->assertDatabaseHas('units', ['id' => $unit->id]);
    }

    // =============================================
    // AC-03: Authorization Tests
    // =============================================

    public function test_kasir_cannot_access_category_routes(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/categories');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/categories/create');
        $response->assertStatus(403);
    }

    public function test_kasir_cannot_access_unit_routes(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/admin/units');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/admin/units/create');
        $response->assertStatus(403);
    }

    public function test_staff_toko_cannot_access_category_routes(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/admin/categories');
        $response->assertStatus(403);
    }

    public function test_staff_toko_cannot_access_unit_routes(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/admin/units');
        $response->assertStatus(403);
    }
}