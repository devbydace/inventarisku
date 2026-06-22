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

class ApprovalTest extends TestCase
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
    // AC-01: Approval Index Page Display Tests
    // =============================================

    public function test_admin_can_view_approval_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

        $response = $this->actingAs($admin)->get('/approvals');
        $response->assertStatus(200);
    }

    public function test_manager_can_view_approval_page(): void
    {
        $user = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $user->assignRole('manager');
        $user->givePermissionTo('approve-transaction');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(200);
    }

    public function test_audit_can_view_approval_page(): void
    {
        $user = User::factory()->create(['role' => 'audit', 'is_active' => true]);
        $user->assignRole('audit');
        $user->givePermissionTo('approve-transaction');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(200);
    }

    public function test_kasir_cannot_view_approval_page(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(403);
    }

    public function test_staff_toko_cannot_view_approval_page(): void
    {
        $user = User::factory()->create(['role' => 'staff_toko', 'is_active' => true]);
        $user->assignRole('staff_toko');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_approval(): void
    {
        $response = $this->get('/approvals');
        $response->assertRedirect('/login');
    }

    public function test_approval_page_shows_pending_transactions(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier = \App\Models\Supplier::create(['name' => 'Test Supplier']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        // Create pending transaction
        $pendingTransaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 10,
            'supplier_id' => $supplier->id,
            'status' => 'pending',
        ]);

        // Create approved transaction (different product to avoid confusion)
        $product2 = Product::create([
            'name' => 'Mouse',
            'sku' => 'MOU-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);
        $approvedTransaction = StockTransaction::create([
            'product_id' => $product2->id,
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 5,
            'status' => 'approved',
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Approval\Index::class)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('10')
            ->assertDontSee('Mouse'); // Approved transaction should not appear
    }

    public function test_approval_page_shows_empty_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Approval\Index::class)
            ->assertSee('Tidak ada transaksi yang menunggu approval');
    }

    // =============================================
    // AC-02: Transaction Information Display Tests
    // =============================================

    public function test_approval_page_displays_transaction_info(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

        $category = Category::create(['name' => 'Elektronik']);
        $unit = Unit::create(['name' => 'Piece', 'abbreviation' => 'PCS']);
        $supplier = \App\Models\Supplier::create(['name' => 'Test Supplier']);
        $product = Product::create([
            'name' => 'Laptop Gaming',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 10,
            'supplier_id' => $supplier->id,
            'reference_no' => 'PO-001',
            'notes' => 'Stok awal',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Approval\Index::class)
            ->assertSee('Laptop Gaming')
            ->assertSee('LAP-001')
            ->assertSee('10')
            ->assertSee('PCS')
            ->assertSee('Stok Masuk')
            ->assertSee('PO-001')
            ->assertSee('Stok awal');
    }

    // =============================================
    // AC-03: Approve Button & Confirmation Tests
    // =============================================

    public function test_approval_page_has_approve_button(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

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
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        $component = Livewire::test(\App\Livewire\Approval\Index::class);
        $component->call('confirmApprove', $transaction->id);
        $component->assertSee('Yakin ingin approve transaksi ini?');
    }

    public function test_admin_can_approve_transaction(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

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
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        $component = Livewire::test(\App\Livewire\Approval\Index::class);
        $component->call('confirmApprove', $transaction->id);
        $component->call('approve', $transaction->id);
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'status' => 'approved',
        ]);
    }

    // =============================================
    // AC-04: Reject Button & Form Tests
    // =============================================

    public function test_approval_page_has_reject_button(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

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
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        $component = Livewire::test(\App\Livewire\Approval\Index::class);
        $component->call('showRejectForm', $transaction->id);
        
        $component->assertSee('Alasan Reject (min 10 karakter)');
    }

    public function test_admin_can_reject_transaction(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

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
            'notes' => 'Original notes',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        $component = Livewire::test(\App\Livewire\Approval\Index::class);
        $component->call('showRejectForm', $transaction->id);
        $component->set('rejectReason', 'Stok tidak tersedia di gudang');
        $component->call('reject');
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'status' => 'rejected',
        ]);
    }

    // =============================================
    // AC-05: Reject Validation Tests
    // =============================================

    public function test_reject_requires_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

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
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        $component = Livewire::test(\App\Livewire\Approval\Index::class);
        $component->call('showRejectForm', $transaction->id);
        $component->set('rejectReason', '');
        $component->call('reject')
            ->assertHasErrors('rejectReason');
    }

    public function test_reject_requires_minimum_10_characters(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

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
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        // Test with 5 characters (should fail)
        $component1 = Livewire::test(\App\Livewire\Approval\Index::class);
        $component1->call('showRejectForm', $transaction->id);
        $component1->set('rejectReason', 'Tidak');
        $component1->call('reject')
            ->assertHasErrors('rejectReason');

        // Test with 10 characters (should pass)
        $component2 = Livewire::test(\App\Livewire\Approval\Index::class);
        $component2->call('showRejectForm', $transaction->id);
        $component2->set('rejectReason', 'Tidak bisa approve');
        $component2->call('reject')
            ->assertHasNoErrors();
    }

    // =============================================
    // AC-06: Authorization Tests
    // =============================================

    public function test_approval_actions_require_permission(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $user->assignRole('kasir');

        $response = $this->actingAs($user)->get('/approvals');
        $response->assertStatus(403);
    }

    // =============================================
    // AC-07: Audit Trail Tests
    // =============================================

    public function test_approve_creates_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

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
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        Livewire::test(\App\Livewire\Approval\Index::class)
            ->call('approve', $transaction->id);

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'StockTransaction',
            'action' => 'approve',
            'entity_id' => $transaction->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_reject_creates_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('approve-transaction');

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
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin);

        $component = Livewire::test(\App\Livewire\Approval\Index::class);
        $component->call('showRejectForm', $transaction->id);
        $component->set('rejectReason', 'Tidak bisa approve');
        $component->call('reject');

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'StockTransaction',
            'action' => 'reject',
            'entity_id' => $transaction->id,
            'user_id' => $admin->id,
        ]);
    }
}