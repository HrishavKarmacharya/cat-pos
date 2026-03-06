<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('user', 'customer', 'saleItems.product');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        $sales = $query->latest()->paginate(10);
        
        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load('user', 'customer', 'saleItems.product');
        return view('sales.invoice', compact('sale'));
    }

    public function downloadPdf(Sale $sale)
    {
        $sale->load('user', 'customer', 'saleItems.product');
        $isPdf = true;
        $pdf = Pdf::loadView('sales.invoice', compact('sale', 'isPdf'));
        
        $filename = 'Invoice_' . $sale->invoice_number . '.pdf';
        return $pdf->download($filename);
    }





    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->saleItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
                }
                StockMovement::where('sale_id', $sale->id)->where('product_id', $item->product_id)->delete();
            }
            $sale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully!');
    }
}
