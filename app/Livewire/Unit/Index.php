<?php

namespace App\Livewire\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Index extends Component
{
    public $search = '';

    protected $queryString = ['search'];

    public function render()
    {
        $units = Unit::query()
            ->withCount('products')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('abbreviation', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.unit.index', compact('units'));
    }

    public function delete($id)
    {
        $unit = Unit::findOrFail($id);
        $productCount = $unit->products()->count();

        if ($productCount > 0) {
            session()->flash('error', "Satuan tidak dapat dihapus karena masih digunakan oleh {$productCount} barang");
            return;
        }

        $oldValues = $unit->toArray();

        AuditTrail::log(
            Auth::user(),
            'Unit',
            $unit->id,
            'delete',
            $oldValues,
            null
        );

        $unit->delete();

        session()->flash('success', 'Satuan berhasil dihapus');
    }
}