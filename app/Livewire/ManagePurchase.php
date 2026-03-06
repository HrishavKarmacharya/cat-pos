<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ManagePurchase extends Component
{
    use AuthorizesRequests;

    public ?Purchase $purchase = null;

    // Form Fields
    public $supplier_id = '';
    public $invoice_number = '';
    public $purchase_date = '';
    public $status = 'received'; // pending, ordered, received

    // Items
    public $purchaseItems = []; // [['product_id', 'quantity', 'cost', 'subtotal']]
    
    // Totals
    public $total_amount = 0;

    // Search
    public $productSearchTerm = '';
    public $searchResults = [];

    public function mount(Purchase $purchase = null, $prefill_product = null)
    {
        $this->purchase_date = now()->format('Y-m-d');

        if ($prefill_product) {
            $this->addProduct($prefill_product);
        }

        if ($purchase) {
            $this->purchase = $purchase;
            $this->supplier_id = $purchase->supplier_id;
            $this->invoice_number = $purchase->invoice_number;
            $this->purchase_date = $purchase->purchase_date->format('Y-m-d');
            $this->status = $purchase->status;

            foreach ($purchase->purchaseItems as $item) {
                $this->purchaseItems[] = [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'cost' => $item->cost,
                    'subtotal' => $item->subtotal
                ];
            }
            $this->calculateTotals();
        }
    }

    public function render()
    {
        if (strlen($this->productSearchTerm) >= 1) {
            $this->searchResults = Product::where('name', 'like', '%' . $this->productSearchTerm . '%')
                ->orWhere('sku', 'like', '%' . $this->productSearchTerm . '%')
                ->limit(10)
                ->get();
        } else {
            $this->searchResults = [];
        }

        $suppliers = Supplier::orderBy('name')->get();

        return view('livewire.manage-purchase', [
            'suppliers' => $suppliers,
        ])->layout('layouts.app');
    }

    // --- Actions ---

    public function addProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        // Check if exists, maybe accumulate or just alert? Let's just add new line for flexibility or accumulate.
        // For purchases, distinct lines might have different costs? Unlikely in one invoice. Let's accumulate.
        foreach ($this->purchaseItems as $key => $item) {
            if ($item['product_id'] == $productId) {
                // If accumulating, we keep the existing cost unless user changes it.
                $this->purchaseItems[$key]['quantity']++;
                $this->calculateTotals();
                $this->productSearchTerm = '';
                $this->searchResults = [];
                return;
            }
        }

        $this->purchaseItems[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 1,
            'cost' => $product->cost_price ?? 0, // Default to current cost price
            'subtotal' => $product->cost_price ?? 0,
        ];

        $this->productSearchTerm = '';
        $this->searchResults = [];
        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->purchaseItems[$index]);
        $this->purchaseItems = array_values($this->purchaseItems);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->total_amount = 0;
        foreach ($this->purchaseItems as $index => $item) {
            $sub = (float)($item['quantity'] ?? 0) * (float)($item['cost'] ?? 0);
            $this->purchaseItems[$index]['subtotal'] = $sub;
            $this->total_amount += $sub;
        }
    }

    public function updated($name)
    {
        if (str_starts_with($name, 'purchaseItems')) {
            $this->calculateTotals();
        }
    }

    public function save()
    {
        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'status' => 'required|in:pending,ordered,received,canceled',
            'purchaseItems' => 'required|array|min:1',
            'purchaseItems.*.product_id' => 'required|exists:products,id',
            'purchaseItems.*.quantity' => 'required|integer|min:1',
            'purchaseItems.*.cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () {
                // Determine if we need to revert old stock (only if editing an existing 'received' order)
                if ($this->purchase) {
                     if ($this->purchase->status === 'received') {
                         foreach ($this->purchase->purchaseItems as $item) {
                             $item->product->decrement('stock_quantity', $item->quantity);
                             // Clean up old stock movements?
                             // The logic matches previous controller: revert manually if status changes.
                             // Here we are doing a full overwrite, so safest is to revert everything if it was received, then re-apply.
                         }
                     }
                     $this->purchase->purchaseItems()->delete();
                }

                $purchase = $this->purchase ?? new Purchase();
                $purchase->supplier_id = $this->supplier_id;
                $purchase->user_id = auth()->id();
                $purchase->invoice_number = $this->invoice_number;
                $purchase->purchase_date = $this->purchase_date;
                $purchase->status = $this->status;
                $purchase->total_amount = $this->total_amount;
                $purchase->save();

                foreach ($this->purchaseItems as $itemData) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'cost' => $itemData['cost'],
                        'subtotal' => $itemData['subtotal'],
                    ]);

                    // Update Stock ONLY if status is 'received'
                    if ($purchase->status === 'received') {
                        $product = Product::find($itemData['product_id']);
                        // Update product cost price to latest?
                        // Optional business logic: $product->update(['cost_price' => $itemData['cost']]); 
                        // Let's keep it simple for now, or maybe update it as it's useful.
                        $product->update(['cost_price' => $itemData['cost']]);

                        $product->increaseStock(
                            $itemData['quantity'], 
                            'Purchase (Invoice: ' . $purchase->invoice_number . ')',
                            $purchase->id // Not used by method schema yet, but good intent
                        );
                    }
                }
            });

            session()->flash('success', 'Purchase saved successfully.');
            return redirect()->route('purchases.index');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error saving: ' . $e->getMessage(), type: 'error');
        }
    }
}
