<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Scan to Pay - eSewa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <div class="flex flex-col items-center">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-green-600">Merchant QR Payment</h3>
                        <p class="text-gray-600 mt-2">Sale #{{ $sale->id }} - Total: Rs. {{ number_format($sale->final_amount, 2) }}</p>
                    </div>

                    <div class="bg-white p-6 border-4 border-green-500 rounded-2xl shadow-xl mb-8 flex flex-col items-center">
                        {{-- QR Generator: Fallback to external API since composer library installation failed --}}
                        <img id="qr-code" 
                             src="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data={{ urlencode($qrPayload) }}" 
                             alt="eSewa QR Code" 
                             class="w-80 h-80 mb-6"
                        >
                        <div class="text-center">
                            <p class="text-xl font-bold text-green-700">Scan & Pay</p>
                            <p class="text-base text-gray-600 mt-2 max-w-sm">Please ask the customer to scan the shop's eSewa QR code and complete the payment.</p>
                        </div>
                    </div>

                    <div id="status-message" class="text-center py-4 bg-yellow-50 text-yellow-800 px-6 rounded-lg mb-6 flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-yellow-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Waiting for payment...</span>
                    </div>

                    <div id="success-actions" class="hidden text-center mt-6">
                        <div class="bg-green-100 text-green-800 p-6 rounded-lg mb-6">
                            <svg class="h-12 w-12 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="text-xl font-bold">Payment Successful!</p>
                            <p>Transaction ID: {{ $sale->transaction_uuid }}</p>
                        </div>
                        <a href="{{ route('sales.show', $sale->id) }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            View Receipt
                        </a>
                    </div>

                    <div class="mt-8 text-sm text-gray-500 max-w-md text-center">
                        <p>If you have already paid but it still shows "Waiting", please use the button below to verify manually.</p>
                        <button onclick="checkStatus(true)" id="manual-verify-btn" class="mt-4 text-green-600 font-semibold hover:underline">
                            Force Status Check
                        </button>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('sales.show', $sale->id) }}" class="text-gray-500 hover:underline">Cancel and Go Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let checkInterval;
        const saleId = "{{ $sale->id }}";
        const checkUrl = "{{ route('esewa.check-status', $sale->id) }}";

        function checkStatus(isManual = false) {
            if (isManual) {
                const btn = document.getElementById('manual-verify-btn');
                btn.innerHTML = 'Checking...';
                btn.disabled = true;
            }

            fetch(checkUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'paid') {
                        clearInterval(checkInterval);
                        document.getElementById('status-message').classList.add('hidden');
                        document.getElementById('success-actions').classList.remove('hidden');
                        document.getElementById('manual-verify-btn').parentElement.classList.add('hidden');
                    } else if (isManual) {
                        const btn = document.getElementById('manual-verify-btn');
                        btn.innerHTML = 'Force Status Check';
                        btn.disabled = false;
                        alert('Payment not found. Please ensure you have completed the transaction in your eSewa app.');
                    }
                })
                .catch(error => {
                    console.error('Error checking status:', error);
                });
        }

        // Auto check every 5 seconds
        checkInterval = setInterval(checkStatus, 5000);

        // Initial check
        checkStatus();
    </script>
</x-app-layout>
