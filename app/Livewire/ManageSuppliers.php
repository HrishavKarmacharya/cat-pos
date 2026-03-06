<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class ManageSuppliers extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $showModal = false;
    public $supplierId;
    public $name, $contact_person, $email, $phone, $address;

    protected $rules = [
        'name' => 'required|string|max:255',
        'contact_person' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create()
    {
        $this->reset(['supplierId', 'name', 'contact_person', 'email', 'phone', 'address']);
        $this->showModal = true;
    }

    public function edit(Supplier $supplier)
    {
        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->contact_person = $supplier->contact_person;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Supplier::updateOrCreate(
            ['id' => $this->supplierId],
            [
                'name' => $this->name,
                'contact_person' => $this->contact_person,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
            ]
        );

        $this->showModal = false;
        $this->reset(['supplierId', 'name', 'contact_person', 'email', 'phone', 'address']);
        session()->flash('message', $this->supplierId ? 'Supplier updated successfully.' : 'Supplier created successfully.');
    }

    public function delete($id)
    {
        try {
            Supplier::find($id)->delete();
            session()->flash('message', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete supplier. They may be linked to existing purchases.');
        }
    }

    public function render()
    {
        $suppliers = Supplier::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('contact_person', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.manage-suppliers', [
            'suppliers' => $suppliers
        ])->layout('layouts.app');
    }
}
