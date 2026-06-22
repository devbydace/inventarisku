<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
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
    public $supplier_ids = [];
    public $buy_price = '';
    public $sell_price = '';
    public $min_stock = 0;
    public $productId;

    public $categories = [];
    public $units = [];
    public $suppliers = [];

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 255 karakter',
        'sku.required' => 'SKU harus diisi',
        'sku.max' => 'SKU tidak boleh lebih dari 100 karakter',
        'sku.unique' => 'SKU sudah digunakan',
        'category_id.required' => 'Kategori harus dipilih',
        'unit_id.required' => 'Satuan harus dipilih',
        'supplier_ids.required' => 'Supplier harus dipilih',
        'supplier_ids.min' => 'Pilih minimal 1 supplier',
        'buy_price.required' => 'Harga beli harus diisi',
        'buy_price.numeric' => 'Harga beli harus berupa angka',
        'buy_price.min' => 'Harga beli tidak boleh negatif',
        'sell_price.required' => 'Harga jual harus diisi',
        'sell_price.numeric' => 'Harga jual harus berupa angka',
        'sell_price.min' => 'Harga jual tidak boleh negatif',
        'min_stock.required' => 'Stok minimum harus diisi',
        'min_stock.integer' => 'Stok minimum harus berupa angka',
        'min_stock.min' => 'Stok minimum tidak boleh negatif',
    ];

    public function mount($id)
    {
        $this->product = Product::findOrFail($id);
        $this->productId = $this->product->id;
        $this->name = $this->product->name;
        $this->sku = $this->product->sku;
        $this->category_id = $this->product->category_id;
        $this->unit_id = $this->product->unit_id;
        $this->supplier_ids = $this->product->suppliers->pluck('id')->toArray();
        $this->buy_price = $this->product->buy_price;
        $this->sell_price = $this->product->sell_price;
        $this->min_stock = $this->product->min_stock;

        $this->categories = Category::where('is_active', true)->orderBy('name')->get();
        $this->units = Unit::where('is_active', true)->orderBy('name')->get();
        $this->suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $this->productId,
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'supplier_ids' => 'required|array|min:1',
            'supplier_ids.*' => 'exists:suppliers,id',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
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
            'buy_price' => $this->buy_price,
            'sell_price' => $this->sell_price,
            'min_stock' => $this->min_stock,
        ]);

        // Sync suppliers (many-to-many)
        $this->product->suppliers()->sync($this->supplier_ids);

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