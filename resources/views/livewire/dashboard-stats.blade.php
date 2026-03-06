<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 transition-all duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-md relative overflow-hidden group">
    <div class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 bg-orange-50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
    <a href="{{ route($targetRoute) }}" class="block relative z-10">
        <div class="flex items-center">
            <div class="p-4 {{ 
                $type === 'total-sales' ? 'bg-orange-100 text-orange-600' : (
                $type === 'total-products' ? 'bg-indigo-100 text-indigo-600' : (
                $type === 'total-customers' ? 'bg-emerald-100 text-emerald-600' : 
                'bg-amber-100 text-amber-600'))
            }} rounded-2xl group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
            </div>
            <div class="ml-5">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $title }}</div>
                <div class="text-3xl font-extrabold text-gray-900">{{ $statValue }}</div>
            </div>
        </div>
    </a>
</div>