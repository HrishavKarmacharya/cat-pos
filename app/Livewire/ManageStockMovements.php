<?php

namespace App\Livewire;

use App\Models\StockMovement;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ManageStockMovements extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';
    public $sortField = 'date';
    public $sortDirection = 'desc';

    // Modal
    public $showModal = false;
    public $productId, $type, $quantity, $reason;

    protected $rules = [
        'productId' => 'required|exists:products,id',
        'type' => 'required|in:in,out',
        'quantity' => 'required|integer|min:1',
        'reason' => 'required|string|max:255',
    ];

    public function updatedSearch() { $this->resetPage(); }
    public function updatedTypeFilter() { $this->resetPage(); }

    public function create()
    {
        $this->reset(['productId', 'type', 'quantity', 'reason']);
        $this->showModal = true;
    }

    public function save()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'You do not have permission to adjust stock manually.');
            return;
        }

        $this->validate();

        $product = Product::find($this->productId);

        if ($this->type === 'in') {
            $product->increaseStock($this->quantity, $this->reason);
        } else {
            // Check stock
            if ($product->stock_quantity < $this->quantity) {
                $this->addError('quantity', 'Insufficient stock.');
                return;
            }
            $product->reduceStock($this->quantity, $this->reason);
        }

        $this->showModal = false;
        session()->flash('message', 'Stock adjusted successfully.');
    }

    public function render()
    {
        $query = StockMovement::with(['product', 'user'])
            ->where(function($q) {
                $q->whereHas('product', function($p) {
                    $p->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                })
                ->orWhere('reason', 'like', '%' . $this->search . '%');
            });

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        $movements = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        $products = Product::orderBy('name')->get(); // For Modal dropdown

        return view('livewire.manage-stock-movements', [
            'movements' => $movements,
            'products' => $products
        ])->layout('layouts.app');
    }
}
