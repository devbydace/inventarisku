<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;
use Illuminate\Validation\ValidationException;

class Out extends Component
{
    public $product_id = '';
    public $quantity = '';
    public $reason = '';
    public $notes = '';

    public $products = [];
    public $selectedProduct = null;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'reason' => 'required|in:penjualan,rusak,adjustment,lainnya',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'product_id.required' => 'Barang harus dipilih',
        'quantity.required' => 'Jumlah harus diisi',
        'quantity.integer' => 'Jumlah harus berupa angka',
        'quantity.min' => 'Jumlah harus lebih besar dari 0',
        'reason.required' => 'Alasan keluar harus dipilih',
        'reason.in' => 'Alasan keluar tidak valid',
        'notes.max' => 'Catatan tidak boleh lebih dari 500 karakter',
    ];

    public function mount()
    {
        $this->products = Product::where('is_active', true)
            ->where('current_stock', '>', 0)
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
        
        // Reset quantity when product changes
        $this->quantity = '';
    }

    public function updatedQuantity()
    {
        // Validate stock availability in real-time
        if ($this->product_id && $this->quantity && $this->quantity > 0) {
            $product = Product::find($this->product_id);
            if ($product && $this->quantity > $product->current_stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok tidak mencukupi. Stok saat ini: {$product->current_stock}, yang diminta: {$this->quantity}",
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.stock.out');
    }

    public function store()
    {
        $this->validate();

        // Additional stock availability validation
        $product = Product::findOrFail($this->product_id);
        if ($this->quantity > $product->current_stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok tidak mencukupi. Stok saat ini: {$product->current_stock}, yang diminta: {$this->quantity}",
            ]);
        }

        $stockTransaction = StockTransaction::create([
            'product_id' => $this->product_id,
            'user_id' => Auth::id(),
            'type' => 'out',
            'quantity' => $this->quantity,
            'reason' => $this->reason,
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

        session()->flash('success', 'Transaksi stok keluar berhasil dibuat dan menunggu approval');

        // Reset form
        $this->reset(['product_id', 'quantity', 'reason', 'notes', 'selectedProduct']);
        $this->products = Product::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get();
    }
}