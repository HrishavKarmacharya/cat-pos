<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EsewaService
{
    protected string $merchantCode;
    protected string $secretKey;
    protected string $gatewayUrl;
    protected string $verifyUrl;

    public function __construct()
    {
        $this->merchantCode = config('services.esewa.merchant_code');
        $this->secretKey = config('services.esewa.secret_key');
        $this->gatewayUrl = config('services.esewa.gateway_url');
        $this->verifyUrl = config('services.esewa.verify_url');
    }

    /**
     * Generate signature for eSewa v2 API
     */
    public function generateSignature(string $totalAmount, string $transactionUuid, string $productCode): string
    {
        // Secret key should be treated as a raw string
        $secretKey = $this->secretKey;

        // Exact order: total_amount, transaction_uuid, product_code
        // Note: Field names MUST match exactly as specified in signed_field_names
        // UTF-8 encoding and no extra spaces are critical.
        $dataToSign = "total_amount={$totalAmount},transaction_uuid={$transactionUuid},product_code={$productCode}";
        
        Log::info('eSewa Signature Generation:', [
            'data_to_sign' => $dataToSign,
            'merchant_code' => $productCode,
            'secret_key_used' => substr($secretKey, 0, 3) . '***'
        ]);
        
        $hash = hash_hmac('sha256', $dataToSign, $secretKey, true);
        $signature = base64_encode($hash);
        
        Log::info('eSewa Generated Signature:', ['signature' => $signature]);
        
        return $signature;
    }

    /**
     * Prepare data for eSewa payment form
     */
    public function preparePaymentData($sale): array
    {
        // Ensure decimal formatting is consistent (no extra spaces, UTF-8 etc handled by PHP strings)
        $amount = number_format($sale->final_amount, 2, '.', '');
        $taxAmount = '0.00';
        $totalAmount = number_format($sale->final_amount, 2, '.', '');
        $transactionUuid = $sale->transaction_uuid;
        
        // Product code must match merchant code for UAT
        $productCode = $this->merchantCode;

        // Get customer name
        $customerName = $sale->customer ? $sale->customer->name : 'Walk-in Customer';

        // Initialize payment record in database
        // Note: payments table has: id, sale_id, pidx (unique), amount, status, transaction_id, timestamps
        try {
            // Check if payment record already exists for this sale
            $existingPayment = DB::table('payments')->where('sale_id', $sale->id)->first();
            
            if ($existingPayment) {
                // Update existing record
                DB::table('payments')
                    ->where('sale_id', $sale->id)
                    ->update([
                        'gateway' => 'esewa',
                        'transaction_uuid' => $transactionUuid,
                        'order_id' => $sale->invoice_number,
                        'amount' => $amount,
                        'total_amount' => $totalAmount,
                        'status' => 'pending',
                        'updated_at' => now(),
                    ]);
                Log::info('Updated existing payment record', ['sale_id' => $sale->id]);
            } else {
                // Create new record
                DB::table('payments')->insert([
                    'sale_id' => $sale->id,
                    'gateway' => 'esewa',
                    'pidx' => $transactionUuid, // Use UUID as pidx for eSewa
                    'transaction_uuid' => $transactionUuid,
                    'order_id' => $sale->invoice_number,
                    'amount' => $amount,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info('Created new payment record', ['sale_id' => $sale->id, 'pidx' => $transactionUuid]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating payment record', [
                'error' => $e->getMessage(),
                'sale_id' => $sale->id,
                'pidx' => $transactionUuid
            ]);
        }

        // Handle local vs production URLs
        $successUrl = route('esewa.success');
        $failureUrl = route('esewa.failure');

        // Force HTTPS ONLY if not local
        if (config('app.env') !== 'local' && !str_contains($successUrl, 'localhost') && !str_contains($successUrl, '127.0.0.1')) {
            $successUrl = str_replace('http://', 'https://', $successUrl);
            $failureUrl = str_replace('http://', 'https://', $failureUrl);
        }

        $data = [
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'product_service_charge' => '0.00',
            'product_delivery_charge' => '0.00',
            'product_code' => $productCode,
            'total_amount' => $totalAmount,
            'transaction_uuid' => $transactionUuid,
            'success_url' => $successUrl,
            'failure_url' => $failureUrl,
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];

        // Generate signature based on the EXACT total_amount string prepared above
        $data['signature'] = $this->generateSignature($totalAmount, $transactionUuid, $productCode);

        Log::info('eSewa Prepared Payment Data:', $data);

        return $data;
    }

    /**
     * Generate QR Payload for Merchant QR (Fonepay/eSewa style)
     */
    public function generateQrPayload($sale): string
    {
        // For eSewa specific QR when scanned via App:
        // The app scanner doesn't support raw ePay POST URLs via GET.
        // We point the QR to our local merchant initiation URL.
        // The customer scans, opens browser, and our site auto-submits the POST form.
        
        return route('esewa.pay', $sale->id);
    }

    /**
     * Verify transaction with eSewa
     */
    public function verifyTransaction(string $transactionUuid, string $totalAmount): ?array
    {
        try {
            // eSewa v2 verification usually requires the exact amount sent in the form
            $formattedAmount = number_format((float)$totalAmount, 2, '.', '');
            
            Log::info('eSewa Verifying Transaction:', [
                'url' => $this->verifyUrl,
                'product_code' => $this->merchantCode,
                'total_amount' => $formattedAmount,
                'transaction_uuid' => $transactionUuid
            ]);

            $response = Http::get($this->verifyUrl, [
                'product_code' => $this->merchantCode,
                'total_amount' => $formattedAmount,
                'transaction_uuid' => $transactionUuid,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('eSewa Verification Response:', $data);
                return $data;
            }

            Log::error('eSewa verification failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;
        } catch (Exception $e) {
            Log::error('eSewa verification exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function getGatewayUrl(): string
    {
        return $this->gatewayUrl;
    }
}
