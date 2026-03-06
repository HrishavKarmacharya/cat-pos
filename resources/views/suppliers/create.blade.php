<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="mt-8 text-2xl">
                        Create Supplier
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('suppliers.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="name" value="{{ __('Supplier Name') }}" />
                            <x-input id="name" type="text" class="mt-1 block w-full" name="name" :value="old('name')" required autofocus />
                            <x-input-error for="name" class="mt-2" />
                        </div>

                        <!-- Contact Person -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="contact_person" value="{{ __('Contact Person') }}" />
                            <x-input id="contact_person" type="text" class="mt-1 block w-full" name="contact_person" :value="old('contact_person')" />
                            <x-input-error for="contact_person" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="email" value="{{ __('Email') }}" />
                            <x-input id="email" type="email" class="mt-1 block w-full" name="email" :value="old('email')" />
                            <x-input-error for="email" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="phone" value="{{ __('Phone') }}" />
                            <x-input id="phone" type="text" class="mt-1 block w-full" name="phone" :value="old('phone')" />
                            <x-input-error for="phone" class="mt-2" />
                        </div>

                        <!-- Address -->
                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <x-label for="address" value="{{ __('Address') }}" />
                            <textarea id="address" name="address" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address') }}</textarea>
                            <x-input-error for="address" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ms-4">
                                {{ __('Create Supplier') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
