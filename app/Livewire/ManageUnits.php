<?php

namespace App\Livewire;

use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class ManageUnits extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $showModal = false;
    public $unitId;
    public $name, $abbreviation;

    protected $rules = [
        'name' => 'required|string|max:255',
        'abbreviation' => 'required|string|max:10',
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
        $this->reset(['unitId', 'name', 'abbreviation']);
        $this->showModal = true;
    }

    public function edit(Unit $unit)
    {
        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->abbreviation = $unit->abbreviation;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Unit::updateOrCreate(
            ['id' => $this->unitId],
            [
                'name' => $this->name,
                'abbreviation' => $this->abbreviation,
            ]
        );

        $this->showModal = false;
        $this->reset(['unitId', 'name', 'abbreviation']);
        session()->flash('message', $this->unitId ? 'Unit updated successfully.' : 'Unit created successfully.');
    }

    public function delete($id)
    {
        try {
            Unit::find($id)->delete();
            session()->flash('message', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete. It may be in use.');
        }
    }

    public function render()
    {
        $units = Unit::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('abbreviation', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.manage-units', [
            'units' => $units
        ])->layout('layouts.app');
    }
}
