<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use Livewire\Component;

class DashboardStats extends Component
{
    public $type;
    public $title;
    public $statValue;
    public $targetRoute; // New property

    public function mount($type)
    {
        $this->type = $type;
        $this->statValue = $this->getStatValue();

        switch ($this->type) {
            case 'total-sales':
                $this->title = 'Total Sales';
                $this->targetRoute = 'sales.index';
                break;
            case 'total-products':
                $this->title = 'Total Products';
                $this->targetRoute = 'products.index';
                break;
            case 'total-customers':
                $this->title = 'Total Customers';
                $this->targetRoute = 'customers.index';
                break;
            case 'total-suppliers':
                $this->title = 'Total Suppliers';
                $this->targetRoute = 'suppliers.index';
                break;
            default:
                $this->title = 'Unknown Stat';
                $this->targetRoute = '#'; // Fallback
        }
    }

    public function getStatValue()
    {
        switch ($this->type) {
            case 'total-sales':
                return Sale::count();
            case 'total-products':
                return Product::count();
            case 'total-customers':
                return Customer::count();
            case 'total-suppliers':
                return Supplier::count();
            default:
                return 0;
        }
    }

    public function render()
    {
        return view('livewire.dashboard-stats', [
            'title' => $this->title,
            'statValue' => $this->statValue,
            'targetRoute' => $this->targetRoute,
        ]);
    }
}
