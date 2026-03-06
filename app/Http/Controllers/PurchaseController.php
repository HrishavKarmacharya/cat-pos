<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with('supplier', 'user', 'purchaseItems.product')->latest()->get();

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:255|unique:purchases,invoice_number',
            'purchase_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,ordered,received,canceled',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => Auth::id(),
                'invoice_number' => $data['invoice_number'],
                'purchase_date' => $data['purchase_date'],
                'total_amount' => $data['total_amount'],
                'status' => $data['status'],
            ]);

            foreach ($data['products'] as $itemData) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'cost' => $itemData['cost'],
                    'subtotal' => $itemData['quantity'] * $itemData['cost'],
                ]);

                // Record stock movement if status is 'received'
                if ($purchase->status === 'received') {
                    StockMovement::create([
                        'product_id' => $itemData['product_id'],
                        'user_id' => Auth::id(),
                        'type' => 'in',
                        'quantity' => $itemData['quantity'],
                        'date' => $purchase->purchase_date,
                        'reason' => 'Purchase (Invoice: '.$purchase->invoice_number.')',
                    ]);
                }
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'user', 'purchaseItems.product');

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $purchase->load('purchaseItems');

        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:255|unique:purchases,invoice_number,'.$purchase->id,
            'purchase_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,ordered,received,canceled',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $purchase) {
            // Revert previous stock movements if status changed from received
            if ($purchase->status === 'received' && $data['status'] !== 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    // Find and delete the corresponding 'in' stock movement
                    StockMovement::where('product_id', $item->product_id)
                        ->where('quantity', $item->quantity)
                        ->where('type', 'in')
                        ->where('reason', 'like', 'Purchase (Invoice: '.$purchase->invoice_number.')')
                        ->delete();
                }
            }

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'user_id' => Auth::id(), // Assuming the user updating is the one recording
                'invoice_number' => $data['invoice_number'],
                'purchase_date' => $data['purchase_date'],
                'total_amount' => $data['total_amount'],
                'status' => $data['status'],
            ]);

            // Delete old purchase items and create new ones
            $purchase->purchaseItems()->delete();
            foreach ($data['products'] as $itemData) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'cost' => $itemData['cost'],
                    'subtotal' => $itemData['quantity'] * $itemData['cost'],
                ]);

                // Record new stock movement if status is 'received' and wasn't before
                if ($purchase->status === 'received' && $data['status'] === 'received') {
                    StockMovement::create([
                        'product_id' => $itemData['product_id'],
                        'user_id' => Auth::id(),
                        'type' => 'in',
                        'quantity' => $itemData['quantity'],
                        'date' => $purchase->purchase_date,
                        'reason' => 'Purchase (Invoice: '.$purchase->invoice_number.')',
                    ]);
                }
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // Revert stock movements if purchase was 'received'
            if ($purchase->status === 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    StockMovement::where('product_id', $item->product_id)
                        ->where('quantity', $item->quantity)
                        ->where('type', 'in')
                        ->where('reason', 'like', 'Purchase (Invoice: '.$purchase->invoice_number.')')
                        ->delete();
                }
            }
            $purchase->delete();
        });

        return redirect()->back()->with('success', 'Purchase deleted successfully!');
    }
}
