<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCategories extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $showModal = false;
    public $categoryId;
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
        $this->reset(['categoryId', 'name', 'description']);
        $this->showModal = true;
    }

    public function edit(Category $category)
    {
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'name' => $this->name,
                'description' => $this->description,
            ]
        );

        $this->showModal = false;
        $this->reset(['categoryId', 'name', 'description']);
        session()->flash('message', $this->categoryId ? 'Category updated successfully.' : 'Category created successfully.');
    }

    public function delete($id)
    {
        try {
            Category::find($id)->delete();
            session()->flash('message', 'Category deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete. It may be in use.');
        }
    }

    public function render()
    {
        $categories = Category::where('name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.manage-categories', [
            'categories' => $categories
        ])->layout('layouts.app');
    }
}
