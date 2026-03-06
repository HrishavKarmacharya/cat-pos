<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class ManageSalesHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $paymentMethodFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedPaymentMethodFilter() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete($id)
    {
        // Add authorization check here if needed
        $sale = Sale::find($id);
        if ($sale) {
            $sale->saleItems()->delete(); // Delete items first
            $sale->delete();
            session()->flash('message', 'Sale record deleted successfully.');
        }
    }

    public function render()
    {
        $query = Sale::with(['user', 'customer'])
            ->withSum('saleItems as total_units', 'quantity')
            ->where(function($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($c) {
                      $c->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('user', function($u) {
                      $u->where('name', 'like', '%' . $this->search . '%');
                  });
            });

        if ($this->statusFilter) {
            $query->where('payment_status', $this->statusFilter);
        }

        if ($this->paymentMethodFilter) {
            $query->where('payment_method', $this->paymentMethodFilter);
        }

        if (auth()->user()->role === 'admin') {
            if ($this->dateFrom) {
                $query->whereDate('sale_date', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $query->whereDate('sale_date', '<=', $this->dateTo);
            }
        }



        $sales = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.manage-sales-history', [
            'sales' => $sales
        ])->layout('layouts.app');
    }
}
