<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $sales = Sale::with('customer', 'user', 'saleItems.product')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $totalRevenue = $sales->sum('final_amount'); // Use final_amount for revenue

        $salesByDay = Sale::selectRaw('DATE(sale_date) as date, SUM(final_amount) as total')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = SaleItem::selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('sale_date', [$startDate, $endDate]);
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_sales')
            ->take(5)
            ->get();

        return view('reports.index', compact('sales', 'totalRevenue', 'salesByDay', 'topProducts', 'startDate', 'endDate'));
    }

    public function inventoryReport()
    {
        $products = Product::with('category', 'brand', 'unit')->get()->map(function ($product) {
            $product->current_stock = StockMovement::where('product_id', $product->id)
                ->sum(DB::raw('CASE WHEN type = "in" THEN quantity ELSE -quantity END'));

            return $product;
        });

        return view('reports.inventory', compact('products'));
    }

    public function purchaseReport()
    {
        $purchases = Purchase::with('supplier', 'user', 'purchaseItems.product')->latest()->get();

        return view('reports.purchases', compact('purchases'));
    }
}
