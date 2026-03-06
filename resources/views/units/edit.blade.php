<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Unit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-8 text-2xl">
                        Edit Unit
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('units.update', $unit->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="name" value="{{ __('Unit Name') }}" />
                            <x-input id="name" type="text" class="mt-1 block w-full" name="name" :value="old('name', $unit->name)" required autofocus />
                            <x-input-error for="name" class="mt-2" />
                        </div>

                        <!-- Abbreviation -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="abbreviation" value="{{ __('Abbreviation') }}" />
                            <x-input id="abbreviation" type="text" class="mt-1 block w-full" name="abbreviation" :value="old('abbreviation', $unit->abbreviation)" required />
                            <x-input-error for="abbreviation" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ms-4">
                                {{ __('Update Unit') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
