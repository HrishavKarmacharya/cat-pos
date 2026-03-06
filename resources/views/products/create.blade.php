<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-8 text-2xl">
                        Create Product
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="name" value="{{ __('Product Name') }}" />
                            <x-input id="name" type="text" class="mt-1 block w-full" name="name" :value="old('name')" required autofocus />
                            <x-input-error for="name" class="mt-2" />
                        </div>

                        <!-- SKU -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="sku" value="{{ __('SKU (Stock Keeping Unit)') }}" />
                            <x-input id="sku" type="text" class="mt-1 block w-full" name="sku" :value="old('sku')" />
                            <x-input-error for="sku" class="mt-2" />
                        </div>

                        <!-- Price -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="price" value="{{ __('Selling Price (Rs.)') }}" />
                            <x-input id="price" type="number" step="0.01" class="mt-1 block w-full" name="price" :value="old('price')" required />
                            <x-input-error for="price" class="mt-2" />
                        </div>

                        <!-- Cost Price -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="cost_price" value="{{ __('Cost Price (Rs.)') }}" />
                            <x-input id="cost_price" type="number" step="0.01" class="mt-1 block w-full" name="cost_price" :value="old('cost_price')" />
                            <x-input-error for="cost_price" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="category_id" value="{{ __('Category') }}" />
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="category_id" class="mt-2" />
                        </div>

                        <!-- Brand -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="brand_id" value="{{ __('Brand') }}" />
                            <select id="brand_id" name="brand_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select Brand</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="brand_id" class="mt-2" />
                        </div>

                        <!-- Unit -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="unit_id" value="{{ __('Unit') }}" />
                            <select id="unit_id" name="unit_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select Unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->abbreviation }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="unit_id" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="description" value="{{ __('Description') }}" />
                            <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error for="description" class="mt-2" />
                        </div>

                        <!-- Image -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="image" value="{{ __('Product Image') }}" />
                            <x-input id="image" type="file" class="mt-1 block w-full" name="image" />
                            <x-input-error for="image" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ms-4">
                                {{ __('Create Product') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
