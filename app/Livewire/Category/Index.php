<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Index extends Component
{
    public $search = '';

    protected $queryString = ['search'];

    public function render()
    {
        $categories = Category::query()
            ->withCount('products')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.category.index', compact('categories'));
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $productCount = $category->products()->count();

        if ($productCount > 0) {
            session()->flash('error', "Kategori tidak dapat dihapus karena masih digunakan oleh {$productCount} barang");
            return;
        }

        $oldValues = $category->toArray();

        AuditTrail::log(
            Auth::user(),
            'Category',
            $category->id,
            'delete',
            $oldValues,
            null
        );

        $category->delete();

        session()->flash('success', 'Kategori berhasil dihapus');
    }
}