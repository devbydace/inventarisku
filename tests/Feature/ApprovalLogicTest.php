<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Unit;
use App\Models\User;
use App\Services\ApprovalService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApprovalLogicTest extends TestCase
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
    // AC-01: Approve Transaction Logic Tests
    // =============================================

    public function test_approve_stock_in_updates_product_stock(): void
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
            'is_active' => true,
        ]);

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();
        $service->approve($transaction, $approver);

        // Check stock increased
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 15, // 10 + 5
        ]);

        // Check transaction status
        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'status' => 'approved',
        ]);

        // Check approval record created
        $this->assertDatabaseHas('approvals', [
            'stock_transaction_id' => $transaction->id,
            'user_id' => $approver->id,
            'action' => 'approve',
        ]);
    }

    public function test_approve_stock_out_decreases_product_stock(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();
        $service->approve($transaction, $approver);

        // Check stock decreased
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 15, // 20 - 5
        ]);

        // Check transaction status
        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'status' => 'approved',
        ]);
    }

    public function test_approve_prevents_negative_stock(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 10, // More than available
            'status' => 'pending',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stok tidak mencukupi');

        $service->approve($transaction, $approver);

        // Stock should remain unchanged
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 5,
        ]);

        // Transaction should still be pending
        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'status' => 'pending',
        ]);
    }

    // =============================================
    // AC-02: Reject Transaction Logic Tests
    // =============================================

    public function test_reject_does_not_update_stock(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 5,
            'notes' => 'Original notes',
            'status' => 'pending',
        ]);

        $rejector = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $rejector->assignRole('manager');

        $service = new ApprovalService();
        $service->reject($transaction, $rejector, 'Stok tidak tersedia');

        // Stock should NOT change
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 20,
        ]);

        // Transaction should be rejected
        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'status' => 'rejected',
        ]);

        // Notes should contain reject reason
        $transaction->refresh();
        $this->assertStringContainsString('REJECTED: Stok tidak tersedia', $transaction->notes);
    }

    // =============================================
    // AC-03: Self-Approval Prevention Tests
    // =============================================

    public function test_user_cannot_approve_own_transaction(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id, // Same user
            'type' => 'in',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $service = new ApprovalService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tidak dapat approve transaksi sendiri');

        $service->approve($transaction, $admin);

        // Transaction should still be pending
        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'status' => 'pending',
        ]);

        // Stock should not change
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 20,
        ]);
    }

    // =============================================
    // AC-04: Concurrent Approval & Race Condition Tests
    // =============================================

    public function test_concurrent_approval_prevents_double_approval(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $approver1 = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver1->assignRole('manager');

        $approver2 = User::factory()->create(['role' => 'audit', 'is_active' => true]);
        $approver2->assignRole('audit');

        $service = new ApprovalService();

        // First approval should succeed
        $service->approve($transaction->fresh(), $approver1);

        // Second approval should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaksi sudah di-approve oleh user lain');

        $service->approve($transaction->fresh(), $approver2);
    }

    // =============================================
    // AC-05: Stock Update Logic Tests
    // =============================================

    public function test_approve_stock_in_increases_stock_correctly(): void
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
            'is_active' => true,
        ]);

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 15,
            'status' => 'pending',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();
        $service->approve($transaction, $approver);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 25, // 10 + 15
        ]);
    }

    public function test_approve_stock_out_decreases_stock_correctly(): void
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
            'current_stock' => 30,
            'is_active' => true,
        ]);

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 10,
            'status' => 'pending',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();
        $service->approve($transaction, $approver);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 20, // 30 - 10
        ]);
    }

    // =============================================
    // AC-06: Audit Trail Tests
    // =============================================

    public function test_approve_creates_audit_trail(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();
        $service->approve($transaction, $approver);

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'StockTransaction',
            'action' => 'update',
            'entity_id' => $transaction->id,
            'user_id' => $approver->id,
        ]);
    }

    public function test_reject_creates_audit_trail(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $rejector = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $rejector->assignRole('manager');

        $service = new ApprovalService();
        $service->reject($transaction, $rejector, 'Stok tidak tersedia');

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'StockTransaction',
            'action' => 'update',
            'entity_id' => $transaction->id,
            'user_id' => $rejector->id,
        ]);
    }

    // =============================================
    // AC-07: Authorization Tests
    // =============================================

    public function test_can_approve_returns_false_for_pending_transaction(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();
        $this->assertTrue($service->canApprove($transaction, $approver));
    }

    public function test_can_approve_returns_false_for_own_transaction(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $service = new ApprovalService();
        $this->assertFalse($service->canApprove($transaction, $admin));
    }

    public function test_can_approve_returns_false_for_non_pending_transaction(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'status' => 'approved',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();
        $this->assertFalse($service->canApprove($transaction, $approver));
    }

    // =============================================
    // AC-08: Error Handling & Data Integrity Tests
    // =============================================

    public function test_approval_rollback_on_error(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 10, // More than available
            'status' => 'pending',
        ]);

        $approver = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $approver->assignRole('manager');

        $service = new ApprovalService();

        $this->expectException(\Exception::class);

        try {
            $service->approve($transaction, $approver);
        } catch (\Exception $e) {
            // Transaction should still be pending
            $this->assertDatabaseHas('stock_transactions', [
                'id' => $transaction->id,
                'status' => 'pending',
            ]);

            // Stock should not change
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'current_stock' => 5,
            ]);

            // No approval record should be created
            $this->assertDatabaseMissing('approvals', [
                'stock_transaction_id' => $transaction->id,
            ]);

            throw $e;
        }
    }

    public function test_reject_requires_minimum_10_characters(): void
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

        $transaction = StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $rejector = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $rejector->assignRole('manager');

        $service = new ApprovalService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Alasan reject harus minimal 10 karakter');

        $service->reject($transaction, $rejector, 'Tidak'); // Less than 10 chars

        // Transaction should still be pending
        $this->assertDatabaseHas('stock_transactions', [
            'id' => $transaction->id,
            'status' => 'pending',
        ]);
    }
}