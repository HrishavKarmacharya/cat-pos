<?php

namespace App\Livewire;

use App\Exceptions\MissingLayoutException;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CreateSale extends Component
{
    use AuthorizesRequests;

    public $customer_id = '';
    public $discount_amount = 0;
    public $payment_method = 'cash';
    public $payment_status = 'completed';
    public $saleItems = [];

    public $subtotal = 0;
    public $total = 0;

    public $showCustomerModal = false;
    public $new_customer_name = '';
    public $new_customer_email = '';
    public $new_customer_phone = '';
    public $new_customer_address = '';

    protected $rules = [
        'customer_id' => 'nullable|exists:customers,id',
        'discount_amount' => 'required|numeric|min:0',
        'payment_method' => 'required|string',
        'saleItems' => 'required|array|min:1',
        'saleItems.*.product_id' => 'required|exists:products,id',
        'saleItems.*.quantity' => 'required|integer|min:1',
        'saleItems.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        // Add an initial empty item to the cart
        // if (empty($this->saleItems)) {
        //     $this->saleItems[] = ['product_id' => '', 'quantity' => 1, 'unit_price' => 0, 'discount' => 0];
        // }
    }

    public function render()
    {
        $layoutName = 'layouts.app';

        if (!view()->exists($layoutName)) {
            throw new MissingLayoutException($layoutName);
        }

        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('livewire.create-sale', [
            'customers' => $customers,
            'products' => $products,
        ])->layout($layoutName);
    }


    public function removeSaleItem($index)
    {
        unset($this->saleItems[$index]);
        $this->saleItems = array_values($this->saleItems);
        $this->updatedSaleItems();
    }

    public function addNewSaleItem()
    {
        $this->saleItems[] = ['product_id' => '', 'name' => '', 'quantity' => 1, 'unit_price' => 0, 'discount' => 0, 'unit_price_set' => false];
    }

    public function updateProductInRow($index)
    {
        if (isset($this->saleItems[$index]['product_id']) && $this->saleItems[$index]['product_id']) {
            $product = Product::find($this->saleItems[$index]['product_id']);
            if ($product) {
                $this->saleItems[$index]['name'] = $product->name;
                $this->saleItems[$index]['unit_price'] = $product->price;
                $this->saleItems[$index]['unit_price_set'] = true;
                $this->updatedSaleItems();
            }
        } else {
            // Clear product details if no product selected
            $this->saleItems[$index]['name'] = '';
            $this->saleItems[$index]['unit_price'] = 0;
            $this->saleItems[$index]['unit_price_set'] = false;
            $this->updatedSaleItems();
        }
    }

    public function updated($name, $value)
    {
        if (str_starts_with($name, 'saleItems.')) {
            // Check if it's a product_id change
            if (str_contains($name, '.product_id')) {
                // Extract index from name like "saleItems.0.product_id"
                preg_match('/saleItems\.(\d+)\.product_id/', $name, $matches);
                if (isset($matches[1])) {
                    $this->updateProductInRow((int)$matches[1]);
                    return;
                }
            }
            $this->updatedSaleItems();
        } elseif ($name === 'discount_amount') {
            $this->updatedSaleItems();
        }
    }

    public function updatedSaleItems()
    {
        $this->subtotal = 0;
        foreach ($this->saleItems as $index => $item) {
            if ($item['product_id']) {
                $product = Product::find($item['product_id']);
                if ($product && !isset($this->saleItems[$index]['unit_price_set'])) {
                    $this->saleItems[$index]['unit_price'] = $product->price;
                    $this->saleItems[$index]['unit_price_set'] = true;
                }
            }
            $this->subtotal += $item['quantity'] * ($item['unit_price'] - $item['discount']);
        }

        $this->total = $this->subtotal - $this->discount_amount;
    }


    public function openCustomerModal()
    {
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->reset(['new_customer_name', 'new_customer_email', 'new_customer_phone', 'new_customer_address']);
    }

    public function saveNewCustomer()
    {
        $this->validate([
            'new_customer_name' => 'required|string|max:255',
            'new_customer_email' => 'nullable|email|unique:customers,email',
            'new_customer_phone' => 'nullable|string|max:255',
            'new_customer_address' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create([
            'name' => $this->new_customer_name,
            'email' => $this->new_customer_email,
            'phone' => $this->new_customer_phone,
            'address' => $this->new_customer_address,
        ]);

        $this->customer_id = $customer->id;
        $this->closeCustomerModal();
        $this->dispatch('show-notification', message: 'Customer added successfully.', type: 'success');
    }

    public function save()
    {
        Log::info('Save method called.');
        $validatedData = $this->validate();
        Log::info('Validation successful.', $validatedData);

        try {
            DB::transaction(function () {
                $sale = Sale::create([
                    'customer_id' => $this->customer_id ?: null,
                    'user_id' => auth()->id(),
                    'total_amount' => $this->subtotal,
                    'discount_amount' => $this->discount_amount,
                    'final_amount' => $this->total,
                    'payment_method' => $this->payment_method,
                    'payment_status' => $this->payment_status,
                    'sale_date' => now(),
                    'status' => 'completed',
                ]);
                Log::info('Sale created successfully.', ['sale_id' => $sale->id]);

                foreach ($this->saleItems as $item) {
                    Log::info('Processing sale item.', $item);
                    $product = Product::find($item['product_id']);
                    if (!$product) {
                        throw new \Exception("Product with ID {$item['product_id']} not found.");
                    }
                    $subtotal = $item['quantity'] * ($item['unit_price'] - $item['discount']);
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount_amount' => $item['discount'],
                        'subtotal' => $subtotal,
                    ]);
                    Log::info('SaleItem created.');

                    $product->decrement('stock_quantity', $item['quantity']);
                    Log::info('Stock decremented.');

                    StockMovement::createStockMovement([
                        'product_id' => $item['product_id'],
                        'user_id' => auth()->id(),
                        'type' => 'out',
                        'quantity' => $item['quantity'],
                        'date' => now(),
                        'sale_id' => $sale->id,
                        'reason' => 'Sale',
                    ]);
                    Log::info('StockMovement created.');
                }
            });

            Log::info('Transaction committed.');
            session()->flash('success', 'Sale recorded successfully.');
            return redirect()->route('sales.index');

        } catch (\Exception $e) {
            Log::error('Error recording sale: ' . $e->getMessage());
            $this->dispatch('show-notification', message: 'Error recording sale: ' . $e->getMessage(), type: 'error');
        }
    }
}

