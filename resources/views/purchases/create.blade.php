<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Purchase') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-8 text-2xl">
                        Create Purchase
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('purchases.store') }}">
                        @csrf

                        <!-- Supplier -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="supplier_id" value="{{ __('Supplier') }}" />
                            <select id="supplier_id" name="supplier_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="supplier_id" class="mt-2" />
                        </div>

                        <!-- Invoice Number -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="invoice_number" value="{{ __('Invoice Number') }}" />
                            <x-input id="invoice_number" type="text" class="mt-1 block w-full" name="invoice_number" :value="old('invoice_number')" />
                            <x-input-error for="invoice_number" class="mt-2" />
                        </div>

                        <!-- Purchase Date -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="purchase_date" value="{{ __('Purchase Date') }}" />
                            <x-input id="purchase_date" type="date" class="mt-1 block w-full" name="purchase_date" :value="old('purchase_date')" required />
                            <x-input-error for="purchase_date" class="mt-2" />
                        </div>

                        <!-- Products (Items) -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label value="{{ __('Products') }}" />
                            <div id="purchase-items">
                                <!-- Existing items or dynamic additions -->
                                <div class="flex items-center space-x-2 mt-2">
                                    <select name="products[0][product_id]" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->price }})</option>
                                        @endforeach
                                    </select>
                                    <x-input type="number" name="products[0][quantity]" placeholder="Quantity" class="w-24" min="1" />
                                    <x-input type="number" step="0.01" name="products[0][cost]" placeholder="Cost" class="w-24" min="0" />
                                    <button type="button" class="text-red-600 hover:text-red-900" onclick="this.parentNode.remove()">Remove</button>
                                </div>
                            </div>
                            <button type="button" class="mt-2 text-indigo-600 hover:text-indigo-900" onclick="addPurchaseItem()">Add Product</button>
                        </div>

                        <!-- Total Amount -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="total_amount" value="{{ __('Total Amount') }}" />
                            <x-input id="total_amount" type="number" step="0.01" class="mt-1 block w-full" name="total_amount" :value="old('total_amount')" required />
                            <x-input-error for="total_amount" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="status" value="{{ __('Status') }}" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="ordered" {{ old('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                                <option value="received" {{ old('status') == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="canceled" {{ old('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                            </select>
                            <x-input-error for="status" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ms-4">
                                {{ __('Create Purchase') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    let itemIndex = 1;
    function addPurchaseItem() {
        const container = document.getElementById('purchase-items');
        const newItem = document.createElement('div');
        newItem.classList.add('flex', 'items-center', 'space-x-2', 'mt-2');
        newItem.innerHTML = `
            <select name="products[${itemIndex}][product_id]" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Select Product</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->price }})</option>
                @endforeach
            </select>
            <input type="number" name="products[${itemIndex}][quantity]" placeholder="Quantity" class="w-24 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" min="1" />
            <input type="number" step="0.01" name="products[${itemIndex}][cost]" placeholder="Cost" class="w-24 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" min="0" />
            <button type="button" class="text-red-600 hover:text-red-900" onclick="this.parentNode.remove()">Remove</button>
        `;
        container.appendChild(newItem);
        itemIndex++;
    }
</script>
