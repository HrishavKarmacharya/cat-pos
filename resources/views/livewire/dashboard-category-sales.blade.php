<div class="bg-white p-6 h-full">
    <h3 class="text-xl font-black text-gray-900 mb-8 tracking-tight">Category Sales</h3>
    
    @if($categorySales->count() > 0)
        <div class="space-y-4">
            @foreach($categorySales as $sale)
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-600">{{ $sale->category_name }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ $sale->total_units }} units</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $maxUnits = $categorySales->first()->total_units;
                            $percentage = ($sale->total_units / $maxUnits) * 100;
                        @endphp
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-40 text-gray-500 italic">
            <p>No sales data recorded yet.</p>
        </div>
    @endif
</div>
