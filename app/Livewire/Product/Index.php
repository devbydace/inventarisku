<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Index extends Component
{
    public $search = '';

    protected $queryString = ['search'];

    public function render()
    {
        $products = Product::query()
            ->with(['category', 'unit'])
            ->where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('livewire.product.index', compact('products'));
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        
        $oldValues = $product->toArray();

        AuditTrail::log(
            Auth::user(),
            'Product',
            $product->id,
            'delete',
            $oldValues,
            null
        );

        $product->forceDelete();

        session()->flash('success', 'Produk berhasil dihapus');
    }
}