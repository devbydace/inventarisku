<?php

namespace App\Livewire\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Create extends Component
{
    public $name = '';
    public $abbreviation = '';

    protected $rules = [
        'name' => 'required|max:50',
        'abbreviation' => 'required|max:10|unique:units,abbreviation',
    ];

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 50 karakter',
        'abbreviation.required' => 'Singkatan harus diisi',
        'abbreviation.max' => 'Singkatan tidak boleh lebih dari 10 karakter',
        'abbreviation.unique' => 'Satuan dengan singkatan ini sudah ada',
    ];

    public function render()
    {
        return view('livewire.unit.create');
    }

    public function store()
    {
        $this->validate();

        $unit = Unit::create([
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
        ]);

        AuditTrail::log(
            Auth::user(),
            'Unit',
            $unit->id,
            'create',
            null,
            $unit->toArray()
        );

        session()->flash('success', 'Satuan berhasil ditambahkan');

        return $this->redirect(route('admin.units.index'), navigate: true);
    }
}