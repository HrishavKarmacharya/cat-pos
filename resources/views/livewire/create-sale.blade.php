<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Record New Sale') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-8 text-2xl">
                        Record New Sale
                    </div>
                </div>

                <div class="p-6">
                    <form wire:submit.prevent="save">
                        @csrf

                        <!-- Customer -->
                        <div class="flex items-center space-x-2">
                            <div class="flex-grow">
                                <x-label for="customer_id" value="{{ __('Customer') }}" />
                                <select id="customer_id" wire:model="customer_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Customer (Optional)</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-button type="button" wire:click="openCustomerModal" class="mt-6">
                                {{ __('Add Customer') }}
                            </x-button>
                        </div>
                        <x-input-error for="customer_id" class="mt-2" />


                        <!-- Products (Items) -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label value="{{ __('Products') }}" />

                            <div class="flex justify-end mb-2">
                                <button type="button" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium" wire:click="addNewSaleItem">
                                    + Add Product Row
                                </button>
                            </div>

                            <!-- Sale Items Table -->
                            <div id="sale-items" class="mt-4">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                            <th scope="col" class="relative px-6 py-3">
                                                <span class="sr-only">Remove</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($saleItems as $index => $item)
                                        <tr wire:key="sale-item-{{ $index }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <select 
                                                    wire:model="saleItems.{{$index}}.product_id"
                                                    wire:change="updateProductInRow({{$index}})"
                                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                >
                                                    <option value="">Select Product (Optional)</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}">
                                                            {{ $product->name }}
                                                            @if($product->sku)
                                                                (SKU: {{ $product->sku }})
                                                            @endif
                                                            - Rs. {{ number_format($product->price, 2) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-input type="number" wire:model="saleItems.{{$index}}.quantity" class="w-24" min="1" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-input type="number" step="0.01" wire:model.lazy="saleItems.{{$index}}.unit_price" class="w-24" min="0" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-input type="number" step="0.01" wire:model.lazy="saleItems.{{$index}}.discount" class="w-24" min="0" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                Rs. {{ number_format($item['quantity'] * ($item['unit_price'] - $item['discount']), 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button" class="text-red-600 hover:text-red-900" wire:click="removeSaleItem({{$index}})">Remove</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <x-input-error for="saleItems" class="mt-2" />
                            </div>
                        </div>


                        <!-- Totals -->
                        <div class="col-span-6 sm:col-span-4 mt-6 pt-4 border-t">
                            <div class="flex justify-end items-center space-x-4">
                                <span class="text-gray-700">Subtotal:</span>
                                <span class="font-bold text-lg">Rs. {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-end items-center space-x-4 mt-2">
                                <div>
                                    <x-label for="discount_amount" value="{{ __('Sale Discount Amount') }}" />
                                    <x-input id="discount_amount" type="number" step="0.01" class="mt-1 block w-full" wire:model.lazy="discount_amount" min="0" />
                                </div>
                            </div>
                            <div class="flex justify-end items-center space-x-4 mt-2">
                                <span class="text-gray-700">Total:</span>
                                <span class="font-bold text-xl text-green-600">Rs. {{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <!-- Payment -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="payment_method" value="{{ __('Payment Method') }}" />
                            <x-input id="payment_method" type="text" class="mt-1 block w-full" wire:model="payment_method" required />
                            <x-input-error for="payment_method" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="payment_status" value="{{ __('Payment Status') }}" />
                            <select id="payment_status" wire:model="payment_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                            <x-input-error for="payment_status" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ms-4">
                                {{ __('Record Sale') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Add Customer Modal -->
    @if($showCustomerModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Add New Customer
                    </h3>
                    <div class="mt-4">
                        <div class="mt-4">
                            <x-label for="new_customer_name" value="{{ __('Name') }}" />
                            <x-input id="new_customer_name" type="text" class="mt-1 block w-full" wire:model.defer="new_customer_name" />
                            <x-input-error for="new_customer_name" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-label for="new_customer_email" value="{{ __('Email') }}" />
                            <x-input id="new_customer_email" type="email" class="mt-1 block w-full" wire:model.defer="new_customer_email" />
                            <x-input-error for="new_customer_email" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-label for="new_customer_phone" value="{{ __('Phone') }}" />
                            <x-input id="new_customer_phone" type="text" class="mt-1 block w-full" wire:model.defer="new_customer_phone" />
                            <x-input-error for="new_customer_phone" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-label for="new_customer_address" value="{{ __('Address') }}" />
                            <x-input id="new_customer_address" type="text" class="mt-1 block w-full" wire:model.defer="new_customer_address" />
                            <x-input-error for="new_customer_address" class="mt-2" />
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="saveNewCustomer" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" wire:click="closeCustomerModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>