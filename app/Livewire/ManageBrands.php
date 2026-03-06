<?php

namespace App\Livewire;

use App\Models\Brand;
use Livewire\Component;
use Livewire\WithPagination;

class ManageBrands extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $showModal = false;
    public $brandId;
    public $name, $description;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
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
        $this->reset(['brandId', 'name', 'description']);
        $this->showModal = true;
    }

    public function edit(Brand $brand)
    {
        $this->brandId = $brand->id;
        $this->name = $brand->name;
        $this->description = $brand->description;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Brand::updateOrCreate(
            ['id' => $this->brandId],
            [
                'name' => $this->name,
                'description' => $this->description,
            ]
        );

        $this->showModal = false;
        $this->reset(['brandId', 'name', 'description']);
        session()->flash('message', $this->brandId ? 'Brand updated successfully.' : 'Brand created successfully.');
    }

    public function delete($id)
    {
        try {
            Brand::find($id)->delete();
            session()->flash('message', 'Brand deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete. It may be in use.');
        }
    }

    public function render()
    {
        $brands = Brand::where('name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.manage-brands', [
            'brands' => $brands
        ])->layout('layouts.app');
    }
}
