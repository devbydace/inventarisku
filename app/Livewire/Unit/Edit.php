<?php

namespace App\Livewire\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Edit extends Component
{
    public $unit;
    public $name = '';
    public $abbreviation = '';
    public $unitId;

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 50 karakter',
        'abbreviation.required' => 'Singkatan harus diisi',
        'abbreviation.max' => 'Singkatan tidak boleh lebih dari 10 karakter',
        'abbreviation.unique' => 'Satuan dengan singkatan ini sudah ada',
    ];

    public function mount($id)
    {
        $this->unit = Unit::findOrFail($id);
        $this->unitId = $this->unit->id;
        $this->name = $this->unit->name;
        $this->abbreviation = $this->unit->abbreviation;
    }

    public function rules()
    {
        return [
            'name' => 'required|max:50',
            'abbreviation' => 'required|max:10|unique:units,abbreviation,' . $this->unitId,
        ];
    }

    public function render()
    {
        return view('livewire.unit.edit');
    }

    public function update()
    {
        $this->validate();

        $oldValues = $this->unit->toArray();

        $this->unit->update([
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
        ]);

        $newValues = $this->unit->fresh()->toArray();

        AuditTrail::log(
            Auth::user(),
            'Unit',
            $this->unit->id,
            'update',
            $oldValues,
            $newValues
        );

        session()->flash('success', 'Satuan berhasil diperbarui');

        return $this->redirect(route('admin.units.index'), navigate: true);
    }
}