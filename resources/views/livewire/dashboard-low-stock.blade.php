<div class="bg-white p-6 h-full">
    <div class="flex justify-between items-center mb-8 relative z-10">
        <h3 class="text-xl font-black text-gray-900 tracking-tight">Low Stock</h3>
        <span class="text-xs font-semibold px-2 py-1 bg-red-100 text-red-800 rounded-full">Below {{ $threshold }}</span>
    </div>

    @if($lowStockItems->count() > 0)
        <div class="overflow-y-auto max-h-[300px]">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        @if(auth()->user()->role === 'admin')
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($lowStockItems as $item)
                        <tr>
                            <td class="px-3 py-3 text-sm font-medium text-gray-900">
                                {{ $item->name }}
                                <div class="text-xs text-gray-500">{{ $item->sku }}</div>
                            </td>
                            <td class="px-3 py-3 text-center text-sm font-bold text-red-600">
                                {{ $item->stock_quantity }}
                            </td>
                            @if(auth()->user()->role === 'admin')
                                <td class="px-3 py-3 text-right text-sm">
                                    <a href="{{ route('purchases.create', ['prefill_product' => $item->id]) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                        Restock
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(auth()->user()->role === 'admin')
            <div class="mt-4 text-center">
                <a href="{{ route('reports.index', ['activeTab' => 'inventory']) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Full Inventory Report &rarr;</a>
            </div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center h-40 text-gray-500">
            <svg class="w-12 h-12 mb-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p>All stock levels are healthy!</p>
        </div>
    @endif
</div>
