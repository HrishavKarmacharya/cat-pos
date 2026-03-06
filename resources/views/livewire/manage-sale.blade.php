<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $sale ? __('Edit Sale') : __('New Sale') }}
        </h2>
    </x-slot>

    @if(!$showPreview)
    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 h-[calc(100vh-64px)]">
        <div class="flex flex-col lg:flex-row gap-6 h-full">
            
            <!-- LEFT PANEL: Product Selection -->
            <div class="w-full lg:w-2/3 h-full">
                <div class="bg-white shadow-lg rounded-xl flex flex-col h-full border border-gray-100 overflow-hidden">


                    <!-- Header: Search -->
                    <div class="p-4 border-b border-gray-100 bg-gray-50">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input wire:model.live.debounce.250ms="productSearchTerm" type="text" 
                                class="block w-full pl-10 pr-4 py-2.5 border-none ring-1 ring-gray-200 rounded-lg bg-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:bg-white text-sm transition-all" 
                                placeholder="Search products by name, SKU, or scan barcode..." autofocus />


                        </div>
                    </div>

                    <!-- Content: Product Grid -->
                    <div class="flex-grow overflow-y-auto p-4 bg-gray-50/50">
                        @if(count($searchResults) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach($searchResults as $product)
                                    <div wire:click="addProduct({{ $product->id }})" 
                                        class="cursor-pointer bg-white group rounded-xl border border-gray-200 p-3 hover:border-indigo-500 hover:ring-1 hover:ring-indigo-500 hover:shadow-md transition-all duration-200 flex flex-col justify-between h-full relative overflow-hidden">
                                        
                                        <!-- Stock Indicator -->
                                        <div class="absolute top-2 right-2">
                                            @if($product->stock_quantity <= 5)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                                    {{ $product->stock_quantity }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">
                                                    {{ $product->stock_quantity }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-2 text-center">
                                            @if($product->image_path)
                                                <img src="{{ Storage::url($product->image_path) }}" class="h-16 w-16 mx-auto object-contain rounded-md mb-2" alt="Product Image">
                                            @else
                                                <div class="h-16 w-16 mx-auto bg-indigo-50 rounded-full flex items-center justify-center mb-2">
                                                    <span class="text-indigo-300 font-bold text-xl">{{ substr($product->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            
                                            <div class="font-semibold text-gray-800 text-sm leading-tight line-clamp-2 group-hover:text-indigo-600">
                                                {{ $product->name }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">{{ $product->sku }}</div>
                                        </div>

                                        <div class="mt-3 pt-3 border-t border-gray-50 text-center">
                                            <div class="font-bold text-indigo-600">
                                                Rs. {{ number_format($product->price, 0) }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(strlen($productSearchTerm) > 0)
                             <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p>No products found.</p>
                             </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                                <svg class="w-16 h-16 mb-4 text-indigo-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <p class="text-lg font-medium text-gray-500">Scan barcode or type to search</p>
                                <p class="text-sm mt-1">Products will appear here</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: Cart & Payment -->
            <div class="w-full lg:w-1/3 h-full">
                <div class="bg-white shadow-lg rounded-xl flex flex-col h-full border border-gray-100 overflow-hidden">


                    
                    <!-- Header: Customer -->
                    <div class="p-4 border-b border-gray-100 bg-gray-50">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Customer</label>
                        <div class="relative">
                            <select wire:model="customer_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 sm:text-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Content: Cart Items -->
                    <div class="flex-grow overflow-y-auto p-0 bg-white">
                        @forelse($saleItems as $index => $item)
                            <div class="flex items-center p-3 border-b border-gray-50 hover:bg-gray-50 transition-colors group">
                                <div class="flex-1 min-w-0 mr-3">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</h4>
                                    <div class="text-xs text-gray-500">
                                        Rs. {{ number_format($item['unit_price'], 2) }} 
                                        <span class="mx-1">&times;</span> 
                                        {{ $item['quantity'] }}
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-1 mr-3">
                                    <button wire:click="updateQuantity({{ $index }}, -1)" class="w-7 h-7 rounded-full bg-gray-100 text-gray-600 hover:bg-indigo-100 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                    </button>
                                    <span class="text-sm font-semibold w-6 text-center text-gray-700">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity({{ $index }}, 1)" class="w-7 h-7 rounded-full bg-gray-100 text-gray-600 hover:bg-indigo-100 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                </div>

                                <div class="text-right min-w-[3.5rem]">
                                    <div class="text-sm font-bold text-gray-800">Rs. {{ number_format($item['line_total'], 0) }}</div>
                                    <button wire:click="removeSaleItem({{ $index }})" class="text-[10px] text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity uppercase font-semibold">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                                <svg class="w-12 h-12 mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-sm">Cart is empty</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Footer: Totals & Actions -->
                    <div class="p-4 bg-gray-50 border-t border-gray-200 shadow-md z-10">
                        <div class="space-y-3 mb-4">
                            <!-- Subtotal -->
                            <div class="flex justify-between text-gray-600 text-sm">
                                <span>Subtotal</span>
                                <span>Rs. {{ number_format($subtotal, 2) }}</span>
                            </div>
                            
                            <!-- Discount Input (Fixed Amount) -->
                            <div class="flex justify-between items-center text-gray-600 text-sm">
                                <span>Discount</span>
                                <div class="flex items-center w-32 border border-gray-300 rounded-md bg-white overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                                    <span class="pl-3 pr-1 text-gray-400">Rs.</span>
                                    <input wire:model.live.debounce.500ms="discount_amount" type="number" min="0" class="w-full py-1.5 px-2 text-right border-none focus:ring-0 text-sm" placeholder="0">
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="flex justify-between items-center pt-3 border-t border-dashed border-gray-300">
                                <span class="text-lg font-bold text-gray-800">Total</span>
                                <span class="text-2xl font-extrabold text-indigo-600">Rs. {{ number_format($total, 2) }}</span>
                            </div>


                        </div>

                        <!-- Simplified Payment Details -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Status</label>
                                <select wire:model="payment_status" class="block w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Method</label>
                                <select wire:model="payment_method" class="block w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="cash">Cash</option>

                                    <option value="khalti">Online (Khalti)</option>

                                </select>
                            </div>
                        </div>


                        </div>
                        
                        <!-- Checkout Button -->
                        <button type="button" wire:click="finalizeSale" wire:loading.attr="disabled" 
                            class="w-full relative flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-base font-bold text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                            <span wire:loading.remove>
                                Complete Sale
                            </span>
                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                        
                        <div class="mt-2 text-center">
                            <x-input-error for="saleItems" class="text-xs" />
                            <x-input-error for="discount_amount" class="text-xs" />
                            <x-input-error for="payment_method" class="text-xs" />
                            <x-input-error for="general" class="text-xs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- BILL PREVIEW -->
    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100">
            <div class="bg-indigo-700 px-8 py-6 text-white flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold uppercase tracking-wider">Bill Preview</h2>
                    <p class="text-indigo-100 text-sm mt-1">Please confirm the details before finalized</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-indigo-200 uppercase font-semibold">Date</p>
                    <p class="font-bold">{{ date('M d, Y') }}</p>
                </div>
            </div>

            <div class="p-8">
                <!-- Seller & Customer Info -->
                <div class="grid grid-cols-2 gap-8 mb-8 pb-8 border-b border-gray-100">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Seller</h3>
                        <p class="font-bold text-gray-800">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-gray-500">Catmando Shoppe Craft</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Customer</h3>
                        @php $customer = \App\Models\Customer::find($customer_id); @endphp
                        <p class="font-bold text-gray-800">{{ $customer->name ?? 'Walk-in Customer' }}</p>
                        @if($customer && $customer->phone)
                            <p class="text-sm text-gray-500">{{ $customer->phone }}</p>
                        @endif
                    </div>
                </div>

                <!-- Items Table -->
                <table class="w-full mb-8">
                    <thead>
                        <tr class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">
                            <th class="py-3">Product</th>
                            <th class="py-3 text-center">Qty</th>
                            <th class="py-3 text-right">Price</th>
                            <th class="py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($saleItems as $item)
                        <tr>
                            <td class="py-4">
                                <div class="font-semibold text-gray-800">{{ $item['name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $item['sku'] }}</div>
                            </td>
                            <td class="py-4 text-center text-gray-600 font-medium">{{ $item['quantity'] }}</td>
                            <td class="py-4 text-right text-gray-600">Rs. {{ number_format($item['unit_price'], 2) }}</td>
                            <td class="py-4 text-right font-bold text-gray-800">Rs. {{ number_format($item['line_total'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Summary -->
                <div class="w-full max-w-xs ml-auto space-y-3">
                    <div class="flex justify-between text-gray-600">
                        <span class="font-medium">Subtotal</span>
                        <span>Rs. {{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if($discount_total > 0)
                    <div class="flex justify-between text-red-500">
                        <span class="font-medium">Discount</span>
                        <span>- Rs. {{ number_format($discount_total, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-gray-600">
                        <span class="font-medium">Tax (0%)</span>
                        <span>Rs. 0.00</span>
                    </div>
                    <div class="pt-3 border-t-2 border-indigo-50 flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800">Grand Total</span>
                        <span class="text-2xl font-black text-indigo-700">Rs. {{ number_format($total, 2) }}</span>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex gap-3 items-center">
                        <div class="px-3 py-1 {{ $payment_method === 'khalti' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500' }} rounded-full text-[10px] font-bold uppercase">
                            Method: {{ $payment_method === 'khalti' ? 'Khalti' : 'Cash' }}
                        </div>
                        <div class="px-3 py-1 {{ $payment_method !== 'cash' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }} rounded-full text-[10px] font-bold uppercase">
                            Status: {{ $payment_method !== 'cash' ? 'Pending' : 'Paid' }}
                        </div>


                    </div>
                </div>

                @if($payment_method === 'khalti')
                    <div class="mt-8 p-6 bg-indigo-50 rounded-2xl border border-indigo-100 flex flex-col items-center">
                        <h4 class="text-sm font-bold text-indigo-900 mb-4 uppercase tracking-wider">Online Payment via Khalti</h4>
                        <p class="text-sm text-indigo-600 font-medium text-center">
                            You will be redirected to the Khalti Sandbox gateway to complete the payment of 
                            <span class="font-bold">Rs. {{ number_format($total, 2) }}</span>
                        </p>
                    </div>
                @endif



            </div>

            <!-- Preview Actions -->
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex gap-4">
                <button wire:click="cancelPreview" 
                    class="flex-1 px-6 py-3 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                    Back to Edit
                </button>
                <button wire:click="finalizeSale" wire:loading.attr="disabled"
                    class="flex-[2] px-6 py-3 bg-indigo-600 rounded-xl text-sm font-bold text-white hover:bg-indigo-700 transition-all shadow-lg hover:shadow-xl flex justify-center items-center gap-2">
                    <span wire:loading.remove>Confirm & Save Sale</span>
                    <span wire:loading class="flex items-center">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Finalizing...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif



    <!-- KHALTI VERIFICATION MODAL -->
    @if($isVerifyingKhalti)
    <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-purple-600 px-6 py-4 text-white flex justify-between items-center" 
                    @if($isVerifyingKhalti) wire:poll.5s="verifyKhaltiPayment" @endif>
                    <h3 class="text-xl font-bold">Khalti Verification</h3>
                    <div class="bg-purple-500 px-2 py-1 rounded text-xs font-mono">{{ $khalti_pidx }}</div>
                </div>

                <div class="p-8">
                    <div class="text-center mb-6">
                        <p class="text-gray-500 text-sm uppercase font-bold tracking-widest mb-1">Amount to Pay</p>
                        <h4 class="text-4xl font-black text-gray-900">Rs. {{ number_format($total, 2) }}</h4>
                    </div>

                    <div class="bg-purple-50 border border-purple-100 rounded-xl p-6 mb-6 flex flex-col items-center text-center">
                        <div class="bg-white p-3 rounded-xl shadow-md mb-4 border border-purple-100">
                            @if($qrCodeUrl)
                                <img src="{{ $qrCodeUrl }}" alt="Khalti QR" class="w-72 h-72">
                            @else
                                <div class="w-72 h-72 flex items-center justify-center bg-gray-100 rounded-xl">
                                    <svg class="w-12 h-12 text-purple-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 11v1m5-10v1m-10 0v1m12 5H3m19 0H4"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-lg font-bold text-purple-900">Scan & Pay with Khalti</p>
                            <p class="text-sm text-purple-700 mt-2 max-w-xs">Ask the customer to scan the QR code using their Khalti app to complete the payment.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button wire:click="verifyKhaltiPayment" wire:loading.attr="disabled"
                            class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold shadow-lg transition-all flex justify-center items-center gap-2">
                            <span wire:loading.remove wire:target="verifyKhaltiPayment">Check Payment Status</span>
                            <span wire:loading wire:target="verifyKhaltiPayment" class="flex items-center">
                                <svg class="animate-spin h-4 w-4 text-white mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Checking...
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-center">
                    <button wire:click="cancelVerification" class="text-sm font-bold text-gray-400 hover:text-red-500 transition-colors">
                        Cancel & Save as Pending
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

