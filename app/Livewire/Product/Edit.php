<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Edit extends Component
{
    public $product;
    public $name = '';
    public $sku = '';
    public $category_id = '';
    public $unit_id = '';
    public $productId;

    public $categories = [];
    public $units = [];

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 255 karakter',
        'sku.required' => 'SKU harus diisi',
        'sku.max' => 'SKU tidak boleh lebih dari 100 karakter',
        'sku.unique' => 'SKU sudah digunakan',
        'category_id.required' => 'Kategori harus dipilih',
        'unit_id.required' => 'Satuan harus dipilih',
    ];

    public function mount($id)
    {
        $this->product = Product::findOrFail($id);
        $this->productId = $this->product->id;
        $this->name = $this->product->name;
        $this->sku = $this->product->sku;
        $this->category_id = $this->product->category_id;
        $this->unit_id = $this->product->unit_id;

        $this->categories = Category::where('is_active', true)->orderBy('name')->get();
        $this->units = Unit::where('is_active', true)->orderBy('name')->get();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $this->productId,
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
        ];
    }

    public function render()
    {
        return view('livewire.product.edit');
    }

    public function update()
    {
        $this->validate();

        $oldValues = $this->product->toArray();

        $this->product->update([
            'name' => $this->name,
            'sku' => $this->sku,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
        ]);

        $newValues = $this->product->fresh()->toArray();

        AuditTrail::log(
            Auth::user(),
            'Product',
            $this->product->id,
            'update',
            $oldValues,
            $newValues
        );

        session()->flash('success', 'Produk berhasil diperbarui');

        return $this->redirect(route('admin.products.index'), navigate: true);
    }
}