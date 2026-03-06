<div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-2xl font-black text-gray-900 tracking-tight">Activity Feed</h3>
        <a href="{{ route('sales.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View All &rarr;</a>
    </div>

    @if($recentSales->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Items</th>
                        @if(auth()->user()->role === 'admin')
                            <th class="px-2 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @foreach($recentSales as $sale)
                        <tr class="group hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-2 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-600 mr-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, H:i') }}</div>
                                        <div class="text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $sale->payment_method ?? 'CASH' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-5 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-700">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
                            </td>
                            <td class="px-2 py-5 whitespace-nowrap text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-700">
                                    {{ $sale->saleItems->sum('quantity') }} Units
                                </span>
                            </td>
                            @if(auth()->user()->role === 'admin')
                                <td class="px-2 py-5 whitespace-nowrap text-right">
                                    <div class="text-sm font-black text-gray-900">Rs. {{ number_format($sale->final_amount, 2) }}</div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-10 text-gray-500 italic">
            <p>No recent sales activity found.</p>
        </div>
    @endif
</div>
