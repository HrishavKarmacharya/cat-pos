<?php

namespace App\Livewire;

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
    public $targetRoute;
    public $icon;

    public function mount($type)
    {
        $this->type = $type;
        $this->statValue = $this->getStat();
        $this->title = $this->getTitle();
        $this->targetRoute = $this->getTargetRoute();
        $this->icon = $this->getIcon();
    }

    public function getStat()
    {
        $isStaff = auth()->user()->role === 'staff';

        switch ($this->type) {
            case 'total-sales':
                if ($isStaff) {
                    // Staff sees Units Sold (Total)
                    return \App\Models\SaleItem::sum('quantity');
                }
                return 'Rs. '.number_format(Sale::sum('final_amount'), 2);
            case 'units-sold-today':
                return \App\Models\SaleItem::whereHas('sale', function($q) {
                    $q->whereDate('sale_date', now());
                })->sum('quantity');
            case 'low-stock-count':
                return Product::where('stock_quantity', '<=', 10)->count(); // Using 10 as default threshold
            case 'total-products':
                return Product::count();
            case 'total-customers':
                return Customer::count();
            case 'total-suppliers':
                if ($isStaff) return 0; // Should not be called for staff
                return Supplier::count();
            default:
                return 'N/A';
        }
    }

    public function getTitle()
    {
        if (auth()->user()->role === 'staff' && $this->type === 'total-sales') {
            return 'Units Sold';
        }
        if ($this->type === 'units-sold-today') return 'Sold Today';
        if ($this->type === 'low-stock-count') return 'Low Stock';
        return ucwords(str_replace('-', ' ', $this->type));
    }

    public function getTargetRoute()
    {
        if ($this->type === 'units-sold-today') return 'sales.index';
        if ($this->type === 'low-stock-count') return 'products.index';
        
        $parts = explode('-', $this->type);
        $resource = end($parts);
        return $resource . '.index';
    }

    public function getIcon()
    {
        switch ($this->type) {
            case 'total-sales':
                return 'M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM4 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm16 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z';
            case 'units-sold-today':
                return 'M13 10V3L4 14h7v7l9-11h-7z';
            case 'low-stock-count':
                return 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z';
            case 'total-products':
                return 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 5h.01M12 12h.01M12 15h.01M9 12h.01M9 15h.01';
            case 'total-customers':
                return 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 006-6v-1a6 6 0 00-9-5.197';
            case 'total-suppliers':
                return 'M8 5a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2H10a2 2 0 01-2-2V5zm-2 2a4 4 0 014-4h4a4 4 0 014 4v2a4 4 0 01-4 4H10a4 4 0 01-4-4V7zm6 8a4 4 0 110-8 4 4 0 010 8zm-6 4a2 2 0 100-4 2 2 0 000 4zm12 0a2 2 0 100-4 2 2 0 000 4z';
            default:
                return '';
        }
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
