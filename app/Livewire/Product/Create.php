<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Create extends Component
{
    public $name = '';
    public $sku = '';
    public $category_id = '';
    public $unit_id = '';

    public $categories = [];
    public $units = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|max:100|unique:products,sku',
        'category_id' => 'required|exists:categories,id',
        'unit_id' => 'required|exists:units,id',
    ];

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 255 karakter',
        'sku.required' => 'SKU harus diisi',
        'sku.max' => 'SKU tidak boleh lebih dari 100 karakter',
        'sku.unique' => 'SKU sudah digunakan',
        'category_id.required' => 'Kategori harus dipilih',
        'unit_id.required' => 'Satuan harus dipilih',
    ];

    public function mount()
    {
        $this->categories = Category::where('is_active', true)->orderBy('name')->get();
        $this->units = Unit::where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.product.create');
    }

    public function store()
    {
        $this->validate();

        $product = Product::create([
            'name' => $this->name,
            'sku' => $this->sku,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
        ]);

        AuditTrail::log(
            Auth::user(),
            'Product',
            $product->id,
            'create',
            null,
            $product->toArray()
        );

        session()->flash('success', 'Produk berhasil ditambahkan');

        return $this->redirect(route('admin.products.index'), navigate: true);
    }
}