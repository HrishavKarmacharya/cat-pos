<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Khalti Payment Service
 * 
 * Handles payment initiation and verification via Khalti API.
 * Follows Laravel 12 / PHP 8.2 benchmarks for typed properties and strictness.
 */
class KhaltiService
{
    protected readonly string $publicKey;
    protected readonly string $secretKey;
    protected readonly string $baseUrl;

    /**
     * @throws InvalidArgumentException if configuration is missing
     */
    public function __construct()
    {
        $this->publicKey = config('services.khalti.public_key') ?? '';
        $this->secretKey = config('services.khalti.secret_key') ?? '';
        $this->baseUrl = config('services.khalti.base_url') ?? 'https://a.khalti.com/api/v2';
    }

    /**
     * Initiate payment with Khalti
     *
     * @param Sale $sale
     * @return array|null
     */
    public function initiatePayment($sale): ?array
    {
        try {
            // Check for missing credentials
            if (empty($this->secretKey)) {
                Log::error('Khalti secret key is missing in configuration.');
                return null;
            }

            // Khalti expects amount in paisa (Rs. 1 = 100 Paisa)
            $amountPaisa = (int)round($sale->final_amount * 100);
            $orderId = $sale->transaction_uuid ?? $sale->invoice_number;

            // Handle guest or walk-in customers gracefully
            $customerName = $sale->customer?->name ?? 'Walk-in Customer';
            $customerEmail = $sale->customer?->email ?? 'customer@example.com';
            $customerPhone = $sale->customer?->phone ?? '9800000000';

            // Log initiation for transparency
            Log::info("Initiating Khalti Payment for Sale ID: {$sale->id}", [
                'amount' => $sale->final_amount,
                'amount_paisa' => $amountPaisa,
                'order_id' => $orderId,
                'base_url' => $this->baseUrl
            ]);

            // Ensure payment record exists or update it
            DB::table('payments')->updateOrInsert(
                ['order_id' => $orderId],
                [
                    'sale_id' => $sale->id,
                    'gateway' => 'khalti',
                    'amount' => $sale->final_amount,
                    'status' => 'PENDING',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $payload = [
                'return_url' => route('khalti.return'),
                'website_url' => config('app.url'),
                'amount' => $amountPaisa,
                'purchase_order_id' => $orderId,
                'purchase_order_name' => 'Order #' . $sale->invoice_number,
                'customer_info' => [
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                ],
            ];

            Log::debug('Khalti Initiation Payload:', $payload);

            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/epayment/initiate/', $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Khalti Initiation Successful', ['pidx' => $data['pidx']]);
                
                // Store pidx for future verification
                DB::table('payments')->updateOrInsert(
                    ['order_id' => $orderId],
                    [
                        'sale_id' => $sale->id,
                        'gateway' => 'khalti',
                        'pidx' => $data['pidx'],
                        'khalti_pidx' => $data['pidx'],
                        'amount' => $sale->final_amount,
                        'total_amount' => $sale->final_amount,
                        'status' => 'PENDING',
                        'updated_at' => now(),
                    ]
                );

                return $data;
            }

            Log::error('Khalti Initiation Failed', [
                'status' => $response->status(),
                'response' => $response->body(), // Better to log raw body if json fails
                'payload' => $payload
            ]);

            return null;
        } catch (Exception $e) {
            Log::critical('Khalti Service Exception [initiatePayment]: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Verify payment status with Khalti (Lookup)
     *
     * @param string $pidx
     * @return array|null
     */
    public function verifyPayment(string $pidx): ?array
    {
        if (empty($pidx)) {
            Log::warning('Khalti verifyPayment called with empty pidx');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/epayment/lookup/', [
                'pidx' => $pidx,
            ]);

            if ($response->successful()) {
                Log::info('Khalti Verification Response', $response->json());
                return $response->json();
            }

            Log::error('Khalti Verification Failed', [
                'pidx' => $pidx,
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return null;
        } catch (Exception $e) {
            Log::critical('Khalti Service Exception [verifyPayment]: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify checkout token from Khalti Checkout JS widget
     * 
     * @param string $token
     * @param int $amountPaisa
     * @return array|null
     */
    public function verifyCheckoutToken(string $token, int $amountPaisa): ?array
    {
        try {
            Log::info("Verifying Khalti Checkout Token", ['token' => $token, 'amount' => $amountPaisa]);

            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://khalti.com/api/v2/payment/verify/', [
                'token' => $token,
                'amount' => $amountPaisa,
            ]);

            if ($response->successful()) {
                Log::info('Khalti Checkout Token Verification Successful', $response->json());
                return $response->json();
            }

            Log::error('Khalti Checkout Token Verification Failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return null;
        } catch (Exception $e) {
            Log::critical('Khalti Service Exception [verifyCheckoutToken]: ' . $e->getMessage());
            return null;
        }
    }
}
