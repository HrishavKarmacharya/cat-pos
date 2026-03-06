<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockMovements = StockMovement::with('product', 'user')->latest()->paginate(15);

        return view('stock-movements.index', compact('stockMovements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();

        return view('stock-movements.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment', // Updated enum values
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255', // Renamed from note
        ]);

        StockMovement::create([
            'product_id' => $data['product_id'],
            'user_id' => Auth::id(), // Record who made the movement
            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'date' => now(), // Record current timestamp
            'reason' => $data['reason'],
        ]);

        return redirect()->route('stock-movements.index')->with('success', 'Stock movement recorded successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockMovement $stockMovement)
    {
        // For audit purposes, direct deletion of stock movements is generally not recommended.
        // Instead, a 'reversal' or 'correction' movement should be created.
        // However, if forced to implement, this is how it would look:
        $stockMovement->delete();

        return redirect()->back()->with('success', 'Stock movement deleted successfully (for audit adjustments).');
    }

    // show, edit, update methods are intentionally omitted for stock movements
    // as they are typically treated as an immutable ledger.
}
