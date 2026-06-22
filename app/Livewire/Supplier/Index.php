<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Index extends Component
{
    public $search = '';

    protected $queryString = ['search'];

    public function render()
    {
        $suppliers = Supplier::query()
            ->withCount('products')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.supplier.index', compact('suppliers'));
    }

    public function delete($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $oldValues = $supplier->toArray();

        AuditTrail::log(
            Auth::user(),
            'Supplier',
            $supplier->id,
            'delete',
            $oldValues,
            null
        );

        $supplier->delete();

        session()->flash('success', 'Supplier berhasil dihapus');
    }
}