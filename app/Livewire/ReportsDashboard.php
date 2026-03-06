<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsDashboard extends Component
{
    public $startDate;
    public $endDate;
    public $activeTab = 'overview'; // overview, sales, purchases, inventory

    public $lowStockThreshold = 10;

    protected $queryString = ['startDate', 'endDate', 'activeTab'];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // --- Computed Properties for Efficiency ---

    public function getStatsProperty()
    {
        $sales = Sale::whereBetween('sale_date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])->get();
        $purchases = Purchase::whereBetween('purchase_date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])->get();

        $totalRevenue = $sales->sum('final_amount');
        $totalCost = $purchases->sum('total_amount'); // Simplified: Sum of all purchase invoices in period
        
        // Approximate Profit based on Sales Margin (Sales - Product Cost)
        // This is more accurate than Revenue - Period Purchase Cost (which depends on stock timing)
        // We need to calculate cost of goods sold (COGS) for the specific sold items.
        $cogs = 0;
        foreach($sales as $sale) {
            foreach($sale->saleItems as $item) {
                // Assuming we might store historical cost, otherwise use current product cost
                // If SaleItem doesn't map cost, we assume current product cost. 
                // Ideally SaleItem should have 'unit_cost' at time of sale.
                // For now, let's use the product's current cost_price.
                $cogs += $item->quantity * ($item->product->cost_price ?? 0);
            }
        }
        
        return [
            'revenue' => $totalRevenue,
            'purchases' => $totalCost, // Cash reporting
            'cogs' => $cogs,
            'gross_profit' => $totalRevenue - $cogs,
            'net_cash_flow' => $totalRevenue - $totalCost,
            'sales_count' => $sales->count(),
            'purchases_count' => $purchases->count(),
        ];
    }

    public function getTopProductsProperty()
    {
        return SaleItem::selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->whereHas('sale', function ($q) {
                $q->whereBetween('sale_date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();
    }

    public function getRecentSalesProperty()
    {
        return Sale::with('customer')
            ->whereBetween('sale_date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function getLowStockItemsProperty()
    {
        return Product::where('stock_quantity', '<=', $this->lowStockThreshold)
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.reports-dashboard');
    }

    public function downloadReport($type)
    {
        $filename = "{$type}_report_" . now()->format('Y-m-d_H-i-s') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($type) {
            $file = fopen('php://output', 'w');

            if ($type === 'sales') {
                fputcsv($file, ['ID', 'Date', 'Customer', 'Items', 'Total (Rs.)', 'Status']);
                $sales = Sale::whereBetween('sale_date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])->with('customer', 'saleItems')->get();
                foreach ($sales as $sale) {
                    fputcsv($file, [
                        $sale->id,
                        $sale->sale_date->format('Y-m-d'),
                        $sale->customer->name ?? 'Guest',
                        $sale->saleItems->sum('quantity'),
                        $sale->final_amount,
                        $sale->payment_status,
                    ]);
                }
            } elseif ($type === 'inventory') {
                fputcsv($file, ['ID', 'Name', 'SKU', 'Stock', 'Cost (Rs.)', 'Price (Rs.)']);
                foreach (Product::all() as $p) {
                    fputcsv($file, [$p->id, $p->name, $p->sku, $p->stock_quantity, $p->cost_price, $p->price]);
                }
            }

            fclose($file);
        }, $filename, $headers);
    }
}
