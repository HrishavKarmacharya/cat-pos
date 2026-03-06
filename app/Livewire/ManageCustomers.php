<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCustomers extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $showModal = false;
    public $customerId;
    public $name, $email, $phone, $address;

    protected $rules = [
        'name' => 'required|string|max:255',
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
        $this->reset(['customerId', 'name', 'email', 'phone', 'address']);
        $this->showModal = true;
    }

    public function edit(Customer $customer)
    {
        if ($customer->is_system) {
            session()->flash('error', 'System records cannot be edited.');
            return;
        }

        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Customer::updateOrCreate(
            ['id' => $this->customerId],
            [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
            ]
        );

        $this->showModal = false;
        $this->reset(['customerId', 'name', 'email', 'phone', 'address']);
        session()->flash('message', $this->customerId ? 'Customer updated successfully.' : 'Customer created successfully.');
    }

    public function delete($id)
    {
        $customer = Customer::find($id);

        if ($customer && $customer->is_system) {
            session()->flash('error', 'System records cannot be deleted.');
            return;
        }

        $customer->delete();
        session()->flash('message', 'Customer deleted successfully.');
    }

    public function render()
    {
        $customers = Customer::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.manage-customers', [
            'customers' => $customers
        ])->layout('layouts.app');
    }
}
