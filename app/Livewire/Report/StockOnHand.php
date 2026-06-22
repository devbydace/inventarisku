<?php

namespace App\Livewire\Report;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ReportService;
use Livewire\Component;
use Livewire\WithPagination;

class StockOnHand extends Component
{
    use WithPagination;

    public $category_id = '';
    public $supplier_id = '';

    public function render()
    {
        $query = Product::with(['category', 'unit', 'suppliers'])
            ->where('is_active', true);

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        if ($this->supplier_id) {
            $query->whereHas('suppliers', function ($q) {
                $q->where('suppliers.id', $this->supplier_id);
            });
        }

        $products = $query->orderBy('name')->paginate(50);
        
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        $totalProducts = $products->total();
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('current_stock', '<', 'minimum_stock')
            ->count();

        return view('livewire.report.stock-on-hand', compact(
            'products', 'categories', 'suppliers', 'totalProducts', 'lowStockCount'
        ));
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingSupplierId()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->category_id = '';
        $this->supplier_id = '';
        $this->resetPage();
    }
}