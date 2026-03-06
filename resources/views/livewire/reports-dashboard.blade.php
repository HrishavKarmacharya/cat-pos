<div class="bg-white shadow-lg rounded-lg min-h-screen">
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Business Reports</h2>
            
            <!-- Date Filter -->
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-gray-100 rounded-md p-1">
                    <input type="date" wire:model.live="startDate" class="bg-transparent border-none text-sm focus:ring-0">
                    <span class="text-gray-500 px-2">to</span>
                    <input type="date" wire:model.live="endDate" class="bg-transparent border-none text-sm focus:ring-0">
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex space-x-6 mt-6 border-b border-gray-100">
            @foreach(['overview' => 'Overview', 'sales' => 'Sales', 'purchases' => 'Purchases', 'inventory' => 'Inventory'] as $key => $label)
                <button 
                    wire:click="setTab('{{ $key }}')"
                    class="pb-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab === $key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="p-6 bg-gray-50 min-h-[500px]">

        <!-- OVERVIEW TAB -->
        @if($activeTab === 'overview')
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Revenue -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="text-gray-500 text-sm font-medium mb-1">Total Revenue</div>
                    <div class="text-2xl font-bold text-gray-900">Rs. {{ number_format($this->stats['revenue'], 2) }}</div>
                    <div class="text-xs text-green-600 mt-1">{{ $this->stats['sales_count'] }} transactions</div>
                </div>

                <!-- COGS -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="text-gray-500 text-sm font-medium mb-1">Cost of Goods Sold</div>
                    <div class="text-2xl font-bold text-gray-900">Rs. {{ number_format($this->stats['cogs'], 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">Est. cost of sold items</div>
                </div>

                <!-- Gross Profit -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="text-gray-500 text-sm font-medium mb-1">Gross Profit</div>
                    <div class="text-2xl font-bold {{ $this->stats['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rs. {{ number_format($this->stats['gross_profit'], 2) }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">Revenue - COGS</div>
                </div>

                <!-- Net Cash Flow -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="text-gray-500 text-sm font-medium mb-1">Net Cash Flow</div>
                    <div class="text-2xl font-bold {{ $this->stats['net_cash_flow'] >= 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                        Rs. {{ number_format($this->stats['net_cash_flow'], 2) }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">Revenue - Purchases (Cash basis)</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chart Area -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4">Sales Trend</h3>
                    @livewire('sales-chart', ['startDate' => $startDate, 'endDate' => $endDate], key($startDate . $endDate)) 
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4">Top Selling Products</h3>
                    <div class="space-y-4">
                        @foreach($this->topProducts as $item)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 line-clamp-1">{{ $item->product->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->total_qty }} sold</div>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-gray-700">Rs. {{ number_format($item->total_sales, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Sales Table -->
                <div class="lg:col-span-3 bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Recent Transactions</h3>
                        <button wire:click="downloadReport('sales')" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-md transition">Download CSV</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 font-medium">
                                <tr>
                                    <th class="px-6 py-3">ID</th>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Customer</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($this->recentSales as $sale)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-medium text-gray-900">#{{ $sale->id }}</td>
                                        <td class="px-6 py-3 text-gray-500">{{ $sale->sale_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-3 text-gray-900">{{ $sale->customer->name ?? 'Guest' }}</td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $sale->payment_status == 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ ucfirst($sale->payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right font-bold text-gray-900">Rs. {{ number_format($sale->final_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No recent sales found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- INVENTORY TAB -->
        @if($activeTab === 'inventory')
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Inventory Status</h3>
                <button wire:click="downloadReport('inventory')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-md transition">Download Report</button>
            </div>

            <!-- Low Stock Alert -->
            <div class="bg-red-50 border border-red-100 rounded-lg p-4 mb-6">
                <h4 class="text-red-800 font-bold mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Low Stock Alert (Below {{ $lowStockThreshold }})
                </h4>
                @if($this->lowStockItems->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->lowStockItems as $item)
                            <div class="bg-white p-3 rounded shadow-sm flex justify-between items-center">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-500">SKU: {{ $item->sku }}</div>
                                </div>
                                <div class="text-red-600 font-bold text-lg">{{ $item->stock_quantity }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-green-700 text-sm">All stock levels are healthy.</p>
                @endif
            </div>
        @endif

        <!-- PLACEHOLDERS FOR OTHER TABS -->
        @if($activeTab === 'purchases' || $activeTab === 'sales')
            <div class="text-center py-20">
                <div class="text-gray-400 mb-4">Detailed {{ ucfirst($activeTab) }} reporting coming soon.</div>
                <button wire:click="downloadReport('{{ $activeTab }}')" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md">Download CSV Export</button>
            </div>
        @endif

    </div>
</div>
