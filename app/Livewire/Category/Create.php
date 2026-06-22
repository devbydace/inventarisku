<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Create extends Component
{
    public $name = '';

    protected $rules = [
        'name' => 'required|max:100|unique:categories,name',
    ];

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 100 karakter',
        'name.unique' => 'Kategori dengan nama ini sudah ada',
    ];

    public function render()
    {
        return view('livewire.category.create');
    }

    public function store()
    {
        $this->validate();

        $category = Category::create([
            'name' => $this->name,
        ]);

        AuditTrail::log(
            Auth::user(),
            'Category',
            $category->id,
            'create',
            null,
            $category->toArray()
        );

        session()->flash('success', 'Kategori berhasil ditambahkan');

        return $this->redirect(route('admin.categories.index'), navigate: true);
    }
}