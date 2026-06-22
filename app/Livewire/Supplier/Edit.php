<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Edit extends Component
{
    public $supplier;
    public $name = '';
    public $contact = '';
    public $address = '';
    public $email = '';
    public $phone = '';
    public $supplierId;

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 255 karakter',
        'email.email' => 'Format email tidak valid',
        'phone.max' => 'Telepon tidak boleh lebih dari 20 karakter',
    ];

    public function mount($id)
    {
        $this->supplier = Supplier::findOrFail($id);
        $this->supplierId = $this->supplier->id;
        $this->name = $this->supplier->name;
        $this->contact = $this->supplier->contact;
        $this->address = $this->supplier->address;
        $this->email = $this->supplier->email;
        $this->phone = $this->supplier->phone;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function render()
    {
        return view('livewire.supplier.edit');
    }

    public function update()
    {
        $this->validate();

        $oldValues = $this->supplier->toArray();

        $this->supplier->update([
            'name' => $this->name,
            'contact' => $this->contact,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $newValues = $this->supplier->fresh()->toArray();

        AuditTrail::log(
            Auth::user(),
            'Supplier',
            $this->supplier->id,
            'update',
            $oldValues,
            $newValues
        );

        session()->flash('success', 'Supplier berhasil diperbarui');

        return $this->redirect(route('admin.suppliers.index'), navigate: true);
    }
}