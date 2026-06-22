<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Create extends Component
{
    public $name = '';
    public $contact = '';
    public $address = '';
    public $email = '';
    public $phone = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'contact' => 'nullable|string|max:100',
        'address' => 'nullable|string',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
    ];

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 255 karakter',
        'email.email' => 'Format email tidak valid',
        'phone.max' => 'Telepon tidak boleh lebih dari 20 karakter',
    ];

    public function render()
    {
        return view('livewire.supplier.create');
    }

    public function store()
    {
        $this->validate();

        $supplier = Supplier::create([
            'name' => $this->name,
            'contact' => $this->contact,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        AuditTrail::log(
            Auth::user(),
            'Supplier',
            $supplier->id,
            'create',
            null,
            $supplier->toArray()
        );

        session()->flash('success', 'Supplier berhasil ditambahkan');

        return $this->redirect(route('admin.suppliers.index'), navigate: true);
    }
}