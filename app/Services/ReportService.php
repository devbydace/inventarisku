<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get stock on-hand report with filters
     */
    public function getStockOnHand(array $filters = [])
    {
        $query = Product::with(['category', 'unit', 'suppliers'])
            ->where('is_active', true);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->whereHas('suppliers', function ($q) use ($filters) {
                $q->where('suppliers.id', $filters['supplier_id']);
            });
        }

        return $query->orderBy('name')->paginate(50);
    }

    /**
     * Get stock mutation report with filters
     */
    public function getStockMutation(array $filters = [])
    {
        $query = StockTransaction::with(['product', 'user', 'approval.user'])
            ->whereIn('status', ['pending', 'approved']);

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderByDesc('created_at')->paginate(50);
    }

    /**
     * Get low stock products
     */
    public function getLowStock()
    {
        return Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->whereColumn('current_stock', '<', 'minimum_stock')
            ->orderBy('current_stock')
            ->paginate(50);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalStock = Product::where('is_active', true)->sum('current_stock');
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('current_stock', '<', 'minimum_stock')
            ->count();
        $pendingApprovals = StockTransaction::where('status', 'pending')->count();
        $totalSuppliers = Supplier::count();
        $totalCategories = Category::count();

        return [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'low_stock_count' => $lowStockCount,
            'pending_approvals' => $pendingApprovals,
            'total_suppliers' => $totalSuppliers,
            'total_categories' => $totalCategories,
        ];
    }

    /**
     * Get stock mutation summary for header
     */
    public function getStockMutationSummary(array $filters = []): array
    {
        $query = StockTransaction::whereIn('status', ['approved']);

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $summary = $query->selectRaw("
            SUM(CASE WHEN type = 'in' THEN quantity ELSE 0 END) as total_in,
            SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END) as total_out,
            SUM(CASE WHEN type = 'adjustment' THEN quantity ELSE 0 END) as total_adjustment
        ")->first();

        return [
            'total_in' => $summary->total_in ?? 0,
            'total_out' => $summary->total_out ?? 0,
            'total_adjustment' => $summary->total_adjustment ?? 0,
        ];
    }
}