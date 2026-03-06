<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;

class DashboardRecentSales extends Component
{
    public function render()
    {
        $recentSales = Sale::with(['customer', 'saleItems.product'])
            ->latest('sale_date')
            ->take(5)
            ->get();

        return view('livewire.dashboard-recent-sales', [
            'recentSales' => $recentSales
        ]);
    }
}
