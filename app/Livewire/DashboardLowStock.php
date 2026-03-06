<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class DashboardLowStock extends Component
{
    public $limit = 5;
    public $threshold = 10;

    public function render()
    {
        // Fetch low stock items
        $lowStockItems = Product::where('stock_quantity', '<=', $this->threshold)
            ->orderBy('stock_quantity', 'asc')
            ->take($this->limit)
            ->get();

        return view('livewire.dashboard-low-stock', [
            'lowStockItems' => $lowStockItems
        ]);
    }
}
