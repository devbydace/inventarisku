<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Edit extends Component
{
    public $category;
    public $name = '';
    public $categoryId;

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'name.max' => 'Nama tidak boleh lebih dari 100 karakter',
        'name.unique' => 'Kategori dengan nama ini sudah ada',
    ];

    public function mount($id)
    {
        $this->category = Category::findOrFail($id);
        $this->categoryId = $this->category->id;
        $this->name = $this->category->name;
    }

    public function rules()
    {
        return [
            'name' => 'required|max:100|unique:categories,name,' . $this->categoryId,
        ];
    }

    public function render()
    {
        return view('livewire.category.edit');
    }

    public function update()
    {
        $this->validate();

        $oldValues = $this->category->toArray();

        $this->category->update([
            'name' => $this->name,
        ]);

        $newValues = $this->category->fresh()->toArray();

        AuditTrail::log(
            Auth::user(),
            'Category',
            $this->category->id,
            'update',
            $oldValues,
            $newValues
        );

        session()->flash('success', 'Kategori berhasil diperbarui');

        return $this->redirect(route('admin.categories.index'), navigate: true);
    }
}