<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Record Stock Movement') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-8 text-2xl">
                        Record New Stock Movement
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('stock-movements.store') }}">
                        @csrf

                        <!-- Product -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="product_id" value="{{ __('Product') }}" />
                            <select id="product_id" name="product_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="product_id" class="mt-2" />
                        </div>

                        <!-- Type -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="type" value="{{ __('Movement Type') }}" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>In (Restock)</option>
                                <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>Out (Sale Return, Damage)</option>
                                <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            </select>
                            <x-input-error for="type" class="mt-2" />
                        </div>

                        <!-- Quantity -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="quantity" value="{{ __('Quantity') }}" />
                            <x-input id="quantity" type="number" class="mt-1 block w-full" name="quantity" :value="old('quantity')" required min="1" />
                            <x-input-error for="quantity" class="mt-2" />
                        </div>

                        <!-- Reason -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="reason" value="{{ __('Reason (Optional)') }}" />
                            <textarea id="reason" name="reason" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                            <x-input-error for="reason" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ms-4">
                                {{ __('Record Movement') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
