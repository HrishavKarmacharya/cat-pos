<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stock Movements') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <!-- Top Controls -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div class="w-full md:w-1/2 flex gap-4">
                        <x-input wire:model.live.debounce.300ms="search" type="search" placeholder="Search product or reason..." class="w-full" />
                        <select wire:model.live="typeFilter" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">All Types</option>
                            <option value="in">In (Stock Added)</option>
                            <option value="out">Out (Stock Reduced)</option>
                        </select>
                    </div>
                    @if(auth()->user()->role === 'admin')
                        <button wire:click="create" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition">
                            + Manual Adjustment
                        </button>
                    @endif
                </div>

                @if (session()->has('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @if (session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif

                <!-- Filter Tabs -->
                <div class="mb-6 flex space-x-4 border-b">
                    <button wire:click="$set('typeFilter', '')" class="pb-2 px-1 text-sm font-semibold {{ $typeFilter === '' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                        All Records
                    </button>
                    <button wire:click="$set('typeFilter', 'in')" class="pb-2 px-1 text-sm font-semibold {{ $typeFilter === 'in' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-500 hover:text-gray-700' }}">
                        Stock In
                    </button>
                    <button wire:click="$set('typeFilter', 'out')" class="pb-2 px-1 text-sm font-semibold {{ $typeFilter === 'out' ? 'border-b-2 border-red-600 text-red-600' : 'text-gray-500 hover:text-gray-700' }}">
                        Stock Out
                    </button>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason / Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($movements as $movement)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="font-medium">{{ $movement->date->format('Y-m-d') }}</div>
                                        <div class="text-xs text-gray-400">{{ $movement->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ $movement->product->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $movement->product->sku ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($movement->type === 'in')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-green-100 text-green-800">
                                                +{{ $movement->quantity }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-red-100 text-red-800">
                                                -{{ $movement->quantity }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="flex flex-col">
                                            @if($movement->sale_id)
                                                <span class="text-xs font-bold text-blue-600 mb-0.5">Sale #{{ $movement->sale_id }}</span>
                                            @endif
                                            <span>{{ $movement->reason }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $movement->user->name ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                        No stock movements recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Manual Stock Adjustment
                    </h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <x-label for="productId" value="Product" />
                            <select wire:model="productId" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select Product...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ $p->stock_quantity }})</option>
                                @endforeach
                            </select>
                            <x-input-error for="productId" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="type" value="Adjustment Type" />
                            <select wire:model="type" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select Type...</option>
                                <option value="in">Add Stock (In)</option>
                                <option value="out">Remove Stock (Out)</option>
                            </select>
                            <x-input-error for="type" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="quantity" value="Quantity" />
                            <x-input id="quantity" type="number" min="1" class="mt-1 block w-full" wire:model="quantity" />
                            <x-input-error for="quantity" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="reason" value="Reason / Note" />
                            <x-input id="reason" type="text" placeholder="e.g. Audit correction, Damaged goods" class="mt-1 block w-full" wire:model="reason" />
                            <x-input-error for="reason" class="mt-2" />
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Submit Adjustment
                    </button>
                    <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
