<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Overview') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Welcome Banner -->
            <div class="bg-white p-8 rounded-lg shadow border border-gray-200">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <img src="{{ asset('images/mainlogo.png') }}" alt="Logo" class="h-16 w-auto">
                    <div class="text-center md:text-left">
                        <h1 class="text-2xl font-bold text-gray-800">
                            Catmando Shoppe Craft Dashboard
                        </h1>
                        <p class="mt-1 text-gray-600">
                            Welcome, {{ auth()->user()->name }}. Monitor your business performance and manage inventory.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Business Snapshot -->
            <div class="space-y-4">
                <h3 class="text-xl font-bold text-gray-800">Business Snapshot</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @livewire('dashboard-stats', ['type' => 'total-sales'])
                    @livewire('dashboard-stats', ['type' => 'total-products'])
                    
                    @if(auth()->user()->role === 'admin')
                        @livewire('dashboard-stats', ['type' => 'total-customers'])
                        @livewire('dashboard-stats', ['type' => 'total-suppliers'])
                    @endif
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chart -->
                <div class="lg:col-span-2 bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Weekly Sales</h3>
                    @livewire('sales-chart')
                </div>

                <!-- Operations -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        @livewire('dashboard-low-stock')
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        @livewire('dashboard-category-sales')
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="space-y-4">
                <h3 class="text-xl font-bold text-gray-800">Management Area</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('users.index') }}" class="block p-4 bg-white rounded-lg border border-teal-200 hover:bg-teal-50 transition shadow-sm">
                            <div class="font-bold text-teal-700">Manage Users</div>
                            <div class="text-xs text-gray-500 mt-1">Staff access and permissions.</div>
                        </a>
                    @endif

                    <a href="{{ route('products.index') }}" class="block p-4 bg-white rounded-lg border border-indigo-200 hover:bg-indigo-50 transition shadow-sm">
                        <div class="font-bold text-indigo-700">{{ auth()->user()->role === 'admin' ? 'Manage Products' : 'View Products' }}</div>
                        <div class="text-xs text-gray-500 mt-1">Check stock and availability.</div>
                    </a>

                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('customers.index') }}" class="block p-4 bg-white rounded-lg border border-green-200 hover:bg-green-50 transition shadow-sm">
                            <div class="font-bold text-green-700">Manage Customers</div>
                            <div class="text-xs text-gray-500 mt-1">Trade history and CRM.</div>
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="block p-4 bg-white rounded-lg border border-yellow-200 hover:bg-yellow-50 transition shadow-sm">
                            <div class="font-bold text-yellow-700">Manage Suppliers</div>
                            <div class="text-xs text-gray-500 mt-1">Sourcing partners.</div>
                        </a>
                    @endif

                    <a href="{{ route('sales.index') }}" class="block p-4 bg-white rounded-lg border border-blue-200 hover:bg-blue-50 transition shadow-sm">
                        <div class="font-bold text-blue-700">{{ auth()->user()->role === 'admin' ? 'Manage Sales' : 'Sales History' }}</div>
                        <div class="text-xs text-gray-500 mt-1">Transaction records.</div>
                    </a>

                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('purchases.index') }}" class="block p-4 bg-white rounded-lg border border-red-200 hover:bg-red-50 transition shadow-sm">
                            <div class="font-bold text-red-700">Manage Purchases</div>
                            <div class="text-xs text-gray-500 mt-1">Stock orders and entries.</div>
                        </a>
                    @endif

                    <a href="{{ route('stock-movements.index') }}" class="block p-4 bg-white rounded-lg border border-purple-200 hover:bg-purple-50 transition shadow-sm">
                        <div class="font-bold text-purple-700">View Inventory</div>
                        <div class="text-xs text-gray-500 mt-1">Track stock flows.</div>
                    </a>

                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('reports.index') }}" class="block p-4 bg-white rounded-lg border border-pink-200 hover:bg-pink-50 transition shadow-sm">
                            <div class="font-bold text-pink-700">View Reports</div>
                            <div class="text-xs text-gray-500 mt-1">Business intelligence.</div>
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>