<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\SaleItem;
use Livewire\Component;

class DashboardCategorySales extends Component
{
    public function render()
    {
        $categorySales = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, SUM(sale_items.quantity) as total_units')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_units')
            ->take(5)
            ->get();

        return view('livewire.dashboard-category-sales', [
            'categorySales' => $categorySales
        ]);
    }
}
