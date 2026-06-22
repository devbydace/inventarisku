<?php

namespace App\Livewire\Report;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class LowStock extends Component
{
    use WithPagination;

    public function render()
    {
        $products = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->whereColumn('current_stock', '<', 'minimum_stock')
            ->orderBy('current_stock')
            ->paginate(50);

        return view('livewire.report.low-stock', compact('products'));
    }
}