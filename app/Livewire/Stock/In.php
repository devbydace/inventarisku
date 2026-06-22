<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class In extends Component
{
    public $product_id = '';
    public $quantity = '';
    public $supplier_id = '';
    public $reference_no = '';
    public $notes = '';

    public $products = [];
    public $suppliers = [];
    public $selectedProduct = null;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'supplier_id' => 'required|exists:suppliers,id',
        'reference_no' => 'nullable|string|max:100',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'product_id.required' => 'Barang harus dipilih',
        'quantity.required' => 'Jumlah harus diisi',
        'quantity.integer' => 'Jumlah harus berupa angka',
        'quantity.min' => 'Jumlah harus lebih besar dari 0',
        'supplier_id.required' => 'Supplier harus dipilih',
        'reference_no.max' => 'Referensi tidak boleh lebih dari 100 karakter',
        'notes.max' => 'Catatan tidak boleh lebih dari 500 karakter',
    ];

    public function mount()
    {
        $this->products = Product::where('is_active', true)->orderBy('name')->get();
        $this->suppliers = Supplier::all();
    }

    public function updatedProductId($value)
    {
        if ($value) {
            $this->selectedProduct = Product::with('suppliers')->find($value);
            // Update suppliers list based on selected product
            if ($this->selectedProduct && $this->selectedProduct->suppliers->count() > 0) {
                $this->suppliers = $this->selectedProduct->suppliers;
            } else {
                $this->suppliers = Supplier::all();
            }
        } else {
            $this->selectedProduct = null;
            $this->suppliers = Supplier::all();
        }
        
        // Reset supplier selection when product changes
        $this->supplier_id = '';
    }

    public function render()
    {
        return view('livewire.stock.in');
    }

    public function store()
    {
        $this->validate();

        $stockTransaction = StockTransaction::create([
            'product_id' => $this->product_id,
            'user_id' => Auth::id(),
            'type' => 'in',
            'quantity' => $this->quantity,
            'supplier_id' => $this->supplier_id,
            'reference_no' => $this->reference_no,
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        AuditTrail::log(
            Auth::user(),
            'StockTransaction',
            $stockTransaction->id,
            'create',
            null,
            $stockTransaction->toArray()
        );

        session()->flash('success', 'Transaksi stok masuk berhasil dibuat dan menunggu approval');

        // Reset form
        $this->reset(['product_id', 'quantity', 'supplier_id', 'reference_no', 'notes', 'selectedProduct']);
        $this->suppliers = Supplier::all();
    }
}