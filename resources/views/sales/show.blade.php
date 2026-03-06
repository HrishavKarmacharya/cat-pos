<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sale Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-8 text-2xl">
                        Sale #{{ $sale->id }}
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p><strong>Sale ID:</strong> {{ $sale->id }}</p>
                            <p><strong>Date:</strong> {{ $sale->sale_date->format('Y-m-d H:i') }}</p>
                            <p><strong>Salesperson:</strong> {{ $sale->user->name ?? 'N/A' }}</p>
                            <p><strong>Customer:</strong> {{ $sale->customer->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p><strong>Total Amount:</strong> Rs. {{ number_format($sale->total_amount, 2) }}</p>
                            <p><strong>Discount:</strong> Rs. {{ number_format($sale->discount_amount, 2) }}</p>
                            <p><strong>Final Amount:</strong> Rs. {{ number_format($sale->final_amount, 2) }}</p>
                            <p><strong>Payment Method:</strong> {{ $sale->payment_method === 'khalti' ? 'Online (Khalti)' : ucfirst($sale->payment_method) }}</p>
                            <p><strong>Payment Status:</strong> 
                                @if($sale->payment_status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Paid
                                    </span>
                                @elseif($sale->payment_status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </p>

                            @if($sale->paid_at)
                                <p><strong>Paid At:</strong> {{ $sale->paid_at->format('Y-m-d H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    <h3 class="text-xl font-semibold mt-8 mb-4">Items</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($sale->saleItems as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->product->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Rs. {{ number_format($item->discount_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Rs. {{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-8 flex justify-between items-center">
                        @if($sale->payment_status !== 'paid' && $sale->payment_method === 'khalti')
                            <a href="{{ route('khalti.initiate', $sale->id) }}" 
                               class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Pay with Khalti
                            </a>
                        @else
                            <div></div>
                        @endif

                        <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Back to Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
