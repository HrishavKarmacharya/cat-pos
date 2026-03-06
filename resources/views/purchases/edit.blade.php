<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Purchase') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-8 text-2xl">
                        Edit Purchase
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('purchases.update', $purchase->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Supplier -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="supplier_id" value="{{ __('Supplier') }}" />
                            <select id="supplier_id" name="supplier_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="supplier_id" class="mt-2" />
                        </div>

                        <!-- Invoice Number -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="invoice_number" value="{{ __('Invoice Number') }}" />
                            <x-input id="invoice_number" type="text" class="mt-1 block w-full" name="invoice_number" :value="old('invoice_number', $purchase->invoice_number)" />
                            <x-input-error for="invoice_number" class="mt-2" />
                        </div>

                        <!-- Purchase Date -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="purchase_date" value="{{ __('Purchase Date') }}" />
                            <x-input id="purchase_date" type="date" class="mt-1 block w-full" name="purchase_date" :value="old('purchase_date', $purchase->purchase_date->format('Y-m-d'))" required />
                            <x-input-error for="purchase_date" class="mt-2" />
                        </div>

                        <!-- Products (Items) -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label value="{{ __('Products') }}" />
                            <div id="purchase-items">
                                @foreach ($purchase->purchaseItems as $index => $item)
                                    <div class="flex items-center space-x-2 mt-2">
                                        <select name="products[{{ $index }}][product_id]" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" {{ old("products.{$index}.product_id", $item->product_id) == $product->id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->price }})</option>
                                            @endforeach
                                        </select>
                                        <x-input type="number" name="products[{{ $index }}][quantity]" placeholder="Quantity" class="w-24" min="1" value="{{ old("products.{$index}.quantity", $item->quantity) }}" />
                                        <x-input type="number" step="0.01" name="products[{{ $index }}][cost]" placeholder="Cost" class="w-24" min="0" value="{{ old("products.{$index}.cost", $item->cost) }}" />
                                        <button type="button" class="text-red-600 hover:text-red-900" onclick="this.parentNode.remove()">Remove</button>
                                    </div>
                                @endforeach
                                <!-- New item template -->
                                <div class="flex items-center space-x-2 mt-2 hidden" id="new-item-template">
                                    <select class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->price }})</option>
                                        @endforeach
                                    </select>
                                    <x-input type="number" placeholder="Quantity" class="w-24" min="1" />
                                    <x-input type="number" step="0.01" placeholder="Cost" class="w-24" min="0" />
                                    <button type="button" class="text-red-600 hover:text-red-900" onclick="this.parentNode.remove()">Remove</button>
                                </div>
                            </div>
                            <button type="button" class="mt-2 text-indigo-600 hover:text-indigo-900" onclick="addPurchaseItem()">Add Product</button>
                        </div>

                        <!-- Total Amount -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="total_amount" value="{{ __('Total Amount') }}" />
                            <x-input id="total_amount" type="number" step="0.01" class="mt-1 block w-full" name="total_amount" :value="old('total_amount', $purchase->total_amount)" required />
                            <x-input-error for="total_amount" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="status" value="{{ __('Status') }}" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="pending" {{ old('status', $purchase->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="ordered" {{ old('status', $purchase->status) == 'ordered' ? 'selected' : '' }}>Ordered</option>
                                <option value="received" {{ old('status', $purchase->status) == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="canceled" {{ old('status', $purchase->status) == 'canceled' ? 'selected' : '' }}>Canceled</option>
                            </select>
                            <x-input-error for="status" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ms-4">
                                {{ __('Update Purchase') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    let itemIndex = {{ count($purchase->purchaseItems) }};
    function addPurchaseItem() {
        const container = document.getElementById('purchase-items');
        const newItem = document.getElementById('new-item-template').cloneNode(true);
        newItem.removeAttribute('id');
        newItem.classList.remove('hidden');

        // Update names for the new item
        newItem.querySelector('select').name = `products[${itemIndex}][product_id]`;
        newItem.querySelector('input[placeholder="Quantity"]').name = `products[${itemIndex}][quantity]`;
        newItem.querySelector('input[placeholder="Cost"]').name = `products[${itemIndex}][cost]`;

        container.appendChild(newItem);
        itemIndex++;
    }
</script>
