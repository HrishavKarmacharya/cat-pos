<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;


class ManageSale extends Component
{
    use AuthorizesRequests;

    public ?Sale $sale = null;
    public bool $showPreview = false;
    public string $generatedInvoiceNumber = '';
    public string $qrCodeUrl = '';


    // Form Fields
    public $customer_id = '';
    public $discount_amount = 0;
    public $payment_method = 'cash';
    public $payment_status = 'paid';

    // eSewa & Khalti Verification

    public bool $isVerifyingKhalti = false;

    public string $khalti_pidx = '';
    public ?int $pending_sale_id = null;
    public ?string $pending_transaction_uuid = null;


    
    // Sale Items
    public $saleItems = [];

    // Totals
    public $subtotal = 0;
    public $discount_total = 0;
    public $total = 0;

    // Search & UI
    public $productSearchTerm = '';
    public $searchResults = [];
    public $showNewCustomerModal = false;

    // New Customer Form
    public $newCustomerName = '';
    public $newCustomerEmail = '';
    public $newCustomerPhone = '';
    public $newCustomerAddress = '';

    public function mount(Sale $sale = null)
    {
        if ($sale) {
            $this->sale = $sale;
            $this->customer_id = $sale->customer_id;
            $this->discount_amount = $sale->discount_amount;
            $this->payment_method = $sale->payment_method;
            $this->payment_status = $sale->payment_status;

            foreach ($sale->saleItems as $item) {
                // Check if product still exists
                $productName = $item->product ? $item->product->name : 'Unknown Product';
                $productPrice = $item->unit_price;
                $currentStock = $item->product ? $item->product->stock_quantity + $item->quantity : 0; 

                $this->saleItems[] = [
                    'product_id' => $item->product_id,
                    'name' => $productName,
                    'sku' => $item->product->sku ?? '',
                    'quantity' => $item->quantity,
                    'unit_price' => $productPrice,
                    'max_stock' => $currentStock, 
                    'line_total' => $item->subtotal
                ];
            }
            $this->calculateTotals();
        } else {
            // Default to Guest Walk-in for new sales
            $this->customer_id = Customer::getGuestId() ?? '';
        }
    }

    public function updatedDiscountAmount()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->saleItems as $item) {
            $this->subtotal += $item['line_total'];
        }

        // Ensure discount doesn't exceed subtotal
        if ($this->discount_amount > $this->subtotal) {
            $this->discount_amount = $this->subtotal;
        }

        $this->discount_total = (float) $this->discount_amount;
        $this->total = $this->subtotal - $this->discount_total;
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        // Check if already in cart
        $existingIndex = null;
        foreach ($this->saleItems as $index => $item) {
            if ($item['product_id'] == $productId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Increment existing
            $this->updateQuantity($existingIndex, 1);
        } else {
            // Add new
            if ($product->stock_quantity < 1) {
                $this->dispatch('banner-message', message: 'Product is out of stock.', style: 'danger');
                return;
            }

            $this->saleItems[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => 1,
                'unit_price' => $product->price,
                'max_stock' => $product->stock_quantity, // Current available
                'line_total' => $product->price
            ];
            
            // Clear search
            $this->productSearchTerm = '';
            $this->calculateTotals();
        }
    }

    public function updateQuantity($index, $change)
    {
        if (!isset($this->saleItems[$index])) return;

        $item = $this->saleItems[$index];
        $newQuantity = $item['quantity'] + $change;

        // Remove if quantity becomes 0 or less
        if ($newQuantity <= 0) {
            $this->removeSaleItem($index);
            return;
        }

        // Check stock limit (for new items)
        // Note: For existing sales being edited, max_stock logic might need adjustment, 
        // but for now we rely on the mounted max_stock which includes the current item quantity if editing.
        if ($newQuantity > $item['max_stock']) {
            $this->dispatch('banner-message', message: 'Requested quantity exceeds available stock.', style: 'danger');
            return;
        }

        $this->saleItems[$index]['quantity'] = $newQuantity;
        $this->saleItems[$index]['line_total'] = $newQuantity * $item['unit_price'];
        
        $this->calculateTotals();
    }

    public function removeSaleItem($index)
    {
        if (isset($this->saleItems[$index])) {
            unset($this->saleItems[$index]);
            $this->saleItems = array_values($this->saleItems); // Re-index array
            $this->calculateTotals();
        }
    }

    public function showBillPreview()
    {
        $this->validate([
            'customer_id' => 'required',
            'saleItems' => 'required|array|min:1',
            'payment_method' => 'required'
        ]);

        if (count($this->saleItems) === 0) {
            $this->dispatch('banner-message', message: 'Cart is empty.', style: 'danger');
            return;
        }

        $this->showPreview = true;
    }

    public function cancelPreview()
    {
        $this->showPreview = false;
    }



    public function handleKhaltiSuccess($saleId)
    {
        $this->dispatch('banner-message', message: 'Payment verified! Sale completed.', style: 'success');
        return redirect()->route('sales.show', $saleId);
    }

    public function render()
    {
        if (strlen($this->productSearchTerm) >= 1) {
            $this->searchResults = Product::where('name', 'like', '%' . $this->productSearchTerm . '%')
                ->orWhere('sku', 'like', '%' . $this->productSearchTerm . '%')
                ->limit(10) // Limit results for performance
                ->get();
        } else {
            $this->searchResults = [];
        }

        $customers = Customer::orderBy('name')->get();


        return view('livewire.manage-sale', [
            'customers' => $customers,
        ])->layout('layouts.app');
    }

    // --- POS Actions ---
    
    // ... (rest of the file until finalizeSale validation)

    public function finalizeSale()
    {
        // Security: Prevent zero-amount sales (Khalti requirement)
        if ($this->total <= 0) {
            $this->dispatch('banner-message', message: 'Cart amount must be greater than 0.', style: 'danger');
            return;
        }

        $this->validate([
            'payment_method' => 'required|in:cash,khalti',
        ]);

        try {
            $result = DB::transaction(function () {
                Log::debug('finalizeSale: Inside transaction');

                // If editing, roll back original stock changes first
                if ($this->sale) {
                    foreach ($this->sale->saleItems as $item) {
                        $item->product->increment('stock_quantity', $item->quantity);
                        StockMovement::where('sale_id', $this->sale->id)
                            ->where('product_id', $item->product_id)
                            ->delete();
                    }
                    $this->sale->saleItems()->delete();
                }

                $sale = $this->sale ?? new Sale();
                
                // Generate Invoice Number if new
                if (!$sale->exists) {
                    $year = date('Y');
                    $lastSale = Sale::whereYear('sale_date', $year)->orderBy('id', 'desc')->first();
                    $nextNumber = $lastSale ? ((int) substr($lastSale->invoice_number, -4)) + 1 : 1;
                    $sale->invoice_number = 'INV-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                }

                $sale->customer_id = $this->customer_id;
                $sale->user_id = auth()->id();
                $sale->subtotal = $this->subtotal;
                $sale->tax_amount = 0; // Can be enhanced later if needed
                $sale->total_amount = $this->subtotal; // Base amount before discount
                $sale->discount_amount = (float) ($this->discount_total ?? 0);
                $sale->final_amount = $this->total;
                $sale->payment_method = $this->payment_method;
                $sale->payment_status = 'pending'; // Default to pending as per requirements
                
                if ($this->payment_method === 'khalti') {
                    $sale->transaction_uuid = (string) str()->uuid() . '-' . time();
                    $sale->status = 'pending';
                    $sale->payment_status = 'pending';

                } else {
                    $sale->payment_status = 'paid';
                    $sale->status = 'completed';
                }

                $sale->sale_date = now();
                $sale->save();


                foreach ($this->saleItems as $itemData) {
                    $product = Product::find($itemData['product_id']);
                    $product->reduceStock($itemData['quantity'], 'Sale #' . $sale->id, $sale->id);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount_amount' => 0,
                        'subtotal' => $itemData['line_total'],
                    ]);
                }

                return $sale;
            });

            Log::debug('finalizeSale: Transaction committed', ['sale_id' => $result->id]);

            session()->flash('flash.banner', 'Sale saved successfully.');
            session()->flash('flash.bannerStyle', 'success');
            


                if ($result->payment_method === 'khalti') {
                    // STEP 1: Redirect user to our PaymentController initiation method
                    return redirect()->route('khalti.initiate', $result->id);
                }
            
            session()->flash('flash.banner', 'Sale saved successfully.');
            session()->flash('flash.bannerStyle', 'success');
            return redirect()->route('sales.show', $result->id);


        } catch (\Throwable $e) {
            Log::error('finalizeSale: Exception caught', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Show error to user
            $this->dispatch('banner-message', 
                message: 'Error saving sale: ' . $e->getMessage(), 
                style: 'danger'
            );
            
            // Also add validation error
            $this->addError('general', 'Error: ' . $e->getMessage());
        }
    }

    public function cancelVerification()
    {
        $this->isVerifyingKhalti = false;
        session()->flash('flash.banner', 'Sale remains pending.');
        session()->flash('flash.bannerStyle', 'success');
        return redirect()->route('sales.index');
    }
}

