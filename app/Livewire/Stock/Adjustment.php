<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Adjustment extends Component
{
    public $product_id = '';
    public $physical_stock = '';
    public $reason = '';

    public $products = [];
    public $selectedProduct = null;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'physical_stock' => 'required|integer|min:0',
        'reason' => 'required|string|min:10|max:500',
    ];

    protected $messages = [
        'product_id.required' => 'Barang harus dipilih',
        'physical_stock.required' => 'Stok fisik harus diisi',
        'physical_stock.integer' => 'Stok fisik harus berupa angka',
        'physical_stock.min' => 'Stok fisik tidak boleh negatif',
        'reason.required' => 'Alasan harus diisi',
        'reason.min' => 'Alasan harus minimal 10 karakter',
        'reason.max' => 'Alasan tidak boleh lebih dari 500 karakter',
    ];

    public function mount()
    {
        $this->products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function updatedProductId($value)
    {
        if ($value) {
            $this->selectedProduct = Product::find($value);
        } else {
            $this->selectedProduct = null;
        }
        
        // Reset physical stock when product changes
        $this->physical_stock = '';
    }

    public function render()
    {
        return view('livewire.stock.adjustment');
    }

    public function store()
    {
        $this->validate();

        $stockAdjustment = StockAdjustment::create([
            'product_id' => $this->product_id,
            'user_id' => Auth::id(),
            'physical_stock' => $this->physical_stock,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        AuditTrail::log(
            Auth::user(),
            'StockAdjustment',
            $stockAdjustment->id,
            'create',
            null,
            [
                'product_id' => $this->product_id,
                'physical_stock' => $this->physical_stock,
                'reason' => $this->reason,
                'status' => 'pending',
            ]
        );

        session()->flash('success', 'Permintaan penyesuaian stok berhasil dibuat dan menunggu approval');

        // Reset form
        $this->reset(['product_id', 'physical_stock', 'reason', 'selectedProduct']);
        $this->products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}