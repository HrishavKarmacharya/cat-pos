<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $purchase ? __('Edit Purchase') : __('Record Purchase') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-6">
            
            <!-- LEFT PANEL: Details & Items -->
            <div class="w-full lg:w-3/4 flex flex-col gap-6">
                
                <!-- 1. Header Details -->
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Invoice Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label value="Supplier" />
                            <select wire:model="supplier_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="supplier_id" class="mt-1" />
                        </div>
                        <div>
                            <x-label value="Invoice Number" />
                            <x-input type="text" wire:model="invoice_number" class="mt-1 block w-full" placeholder="e.g. INV-2023-001" />
                            <x-input-error for="invoice_number" class="mt-1" />
                        </div>
                        <div>
                            <x-label value="Date" />
                            <x-input type="date" wire:model="purchase_date" class="mt-1 block w-full" />
                            <x-input-error for="purchase_date" class="mt-1" />
                        </div>
                        <div>
                            <x-label value="Status" />
                            <select wire:model="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="received">Received (Adds to Stock)</option>
                                <option value="pending">Pending (Draft)</option>
                                <option value="ordered">Ordered</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Stock is only updated when status is "Received".</p>
                        </div>
                    </div>
                </div>

                <!-- 2. Products -->
                <div class="bg-white p-6 shadow sm:rounded-lg min-h-[400px]">
                     <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Items Attempt</h3>
                     
                     <!-- Search -->
                     <div class="mb-4 relative">
                        <x-input type="text" wire:model.live.debounce.300ms="productSearchTerm" class="w-full" placeholder="Search product to add..." />
                        @if(count($searchResults) > 0)
                            <div class="absolute z-10 w-full bg-white shadow-lg border rounded-md mt-1 max-h-60 overflow-y-auto">
                                @foreach($searchResults as $product)
                                    <div wire:click="addProduct({{ $product->id }})" class="p-3 hover:bg-indigo-50 cursor-pointer flex justify-between border-b last:border-0">
                                        <span class="font-medium">{{ $product->name }}</span>
                                        <span class="text-gray-500 text-sm">Cost: Rs. {{ number_format($product->cost_price, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                     </div>

                     <!-- Table -->
                     <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cost (Rs.)</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total (Rs.)</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($purchaseItems as $index => $item)
                                <tr>
                                    <td class="px-3 py-4 text-sm text-gray-900">{{ $item['name'] }}</td>
                                    <td class="px-3 py-4">
                                        <x-input type="number" wire:model.live.debounce.500ms="purchaseItems.{{ $index }}.quantity" class="w-20" min="1" />
                                    </td>
                                    <td class="px-3 py-4">
                                        <x-input type="number" wire:model.live.debounce.500ms="purchaseItems.{{ $index }}.cost" class="w-24" min="0" step="0.01" />
                                    </td>
                                    <td class="px-3 py-4 text-sm font-bold text-gray-900">
                                        Rs. {{ number_format($item['subtotal'], 2) }}
                                    </td>
                                    <td class="px-3 py-4 text-right">
                                        <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8 text-center text-gray-400">
                                        No items added yet. Search above to add.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                     </table>
                     <x-input-error for="purchaseItems" class="mt-2" />
                </div>
            </div>

            <!-- RIGHT PANEL: Summary -->
            <div class="w-full lg:w-1/4">
                <div class="bg-white p-6 shadow sm:rounded-lg sticky top-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Summary</h3>
                    
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600">Total Items</span>
                        <span class="font-bold">{{ count($purchaseItems) }}</span>
                    </div>

                    <div class="flex justify-between items-center text-xl font-bold text-gray-900 border-t pt-4 mb-6">
                        <span>Total Pay</span>
                        <span>Rs. {{ number_format($total_amount, 2) }}</span>
                    </div>

                    <button wire:click="save" wire:loading.attr="disabled" class="w-full justify-center inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                        <span wire:loading.remove>Save Purchase</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
