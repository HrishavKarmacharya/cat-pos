<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;

class KhaltiController extends Controller
{
    /**
     * STEP 1: Initiate Payment
     * This method is called when the user clicks "Pay with Khalti".
     */
    public function initiateKhalti($sale_id)
    {
        // 1. Find the Sale record
        $sale = Sale::findOrFail($sale_id);

        // 2. Convert Amount to Paisa
        // Khalti API requires amount in Paisa (Rs 1 = 100 Paisa).
        // Example: Rs 100 becomes 10000 paisa.
        $amountPaisa = (int) round($sale->final_amount * 100);

        // 3. Prepare Data for Khalti API
        // We send the return URL so Khalti knows where to send the user back after payment.
        $payload = [
            "return_url" => route('khalti.return'), // Defined in web.php
            "website_url" => config('app.url'),
            "amount" => $amountPaisa,
            "purchase_order_id" => $sale->id,
            "purchase_order_name" => "Sale #" . $sale->invoice_number,
        ];

        // 4. Call Khalti Initiate API (Server-to-Server)
        // We use Laravel's Http client to send a POST request to Khalti.
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.khalti.secret_key'),
            'Content-Type' => 'application/json',
        ])->post(config('services.khalti.base_url') . '/epayment/initiate/', $payload);

        // 5. Handle the Response
        if ($response->successful()) {
            $data = $response->json();

            // Store the 'pidx' (Payment ID) in our database.
            // We mark the status as 'pending' initially.
            DB::table('payments')->insert([
                'sale_id' => $sale->id,
                'pidx' => $data['pidx'],
                'amount' => $sale->final_amount, // Store in Rupees for our reference
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Redirect the user to Khalti's payment page
            return redirect($data['payment_url']);
        }

        // If something goes wrong (e.g., bad internet, invalid key)
        return redirect()->back()->with('error', 'Khalti Initiation Failed: ' . $response->body());
    }

    /**
     * STEP 2: Verify Payment
     * This method is called by Khalti (via Redirect) after the user pays.
     */
    public function khaltiReturn(Request $request)
    {
        // 1. Get Data from URL parameters
        $pidx = $request->pidx;
        $status = $request->status; // e.g., "Completed", "User canceled"
        
        // 2. Find our local payment record
        $payment = DB::table('payments')->where('pidx', $pidx)->first();

        if (!$payment) {
            return redirect()->route('sales.index')->with('error', 'Payment record not found.');
        }

        // 3. Immediate Check: Did the user cancel?
        if ($status !== 'Completed') {
            // Update status to failed
            DB::table('payments')->where('pidx', $pidx)->update(['status' => 'failed']);
            return redirect()->route('sales.show', $payment->sale_id)->with('error', 'Payment failed or canceled.');
        }

        // 4. SERVER-SIDE VERIFICATION (Crucial Step!)
        // Never trust the redirect alone. Someone could type the URL manually.
        // We ask Khalti: "Is this pidx actually paid?"
        $verifyResponse = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.khalti.secret_key'),
        ])->post(config('services.khalti.base_url') . '/epayment/lookup/', [
            'pidx' => $pidx
        ]);

        if ($verifyResponse->successful()) {
            $data = $verifyResponse->json();

            // 5. Final Status Check
            if (($data['status'] ?? '') === 'Completed') {
                
                // Security Check: Verify amount matches (convert paise to rupees or vice versa)
                $amountPaisa = (int)$data['total_amount'];
                $expectedAmountPaisa = (int)round($payment->amount * 100);
                
                if (abs($amountPaisa - $expectedAmountPaisa) > 0) {
                    Log::error('Khalti amount mismatch', [
                        'expected' => $expectedAmountPaisa,
                        'received' => $amountPaisa,
                        'pidx' => $pidx
                    ]);
                    DB::table('payments')->where('pidx', $pidx)->update(['status' => 'FAILED']);
                    return redirect()->route('sales.show', $payment->sale_id)->with('error', 'Payment verification failed: Amount mismatch.');
                }

                // A. Update Payment Record
                DB::table('payments')->where('pidx', $pidx)->update([
                    'status' => 'PAID',
                    'transaction_id' => $data['transaction_id'], // Save Khalti's transaction ID
                    'updated_at' => now()
                ]);

                // B. Mark Sale as Paid
                Sale::where('id', $payment->sale_id)->update([
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'paid_at' => now()
                ]);

                // Success!
                return redirect()->route('sales.show', $payment->sale_id)->with('flash.banner', 'Payment Successful!');
            }
        }

        // verification failed
        DB::table('payments')->where('pidx', $pidx)->update(['status' => 'FAILED']);
        return redirect()->route('sales.show', $payment->sale_id)->with('error', 'Payment verification failed.');
    }

    public function verifyCheckout(Request $request)
    {
        $token = $request->input('token');
        $amountPaisa = (int)$request->input('amount');
        $saleId = $request->input('sale_id');

        if (!$token || !$amountPaisa || !$saleId) {
            return response()->json(['success' => false, 'message' => 'Invalid parameters.'], 400);
        }

        $sale = Sale::find($saleId);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        // Service Injection
        $khaltiService = app(\App\Services\KhaltiService::class);
        $verificationResult = $khaltiService->verifyCheckoutToken($token, $amountPaisa);

        if ($verificationResult && isset($verificationResult['idx'])) {
            
            // VALIDATE AMOUNT
            $expectedAmountPaisa = (int)round($sale->final_amount * 100);
            if (abs($expectedAmountPaisa - $amountPaisa) > 0) {
                return response()->json(['success' => false, 'message' => 'Security check failed: Amount mismatch.'], 400);
            }

            // UPDATE DATABASE
            DB::beginTransaction();
            try {
                DB::table('payments')->updateOrInsert(
                    ['sale_id' => $saleId, 'gateway' => 'khalti'],
                    [
                        'pidx' => $verificationResult['idx'],
                        'khalti_pidx' => $verificationResult['idx'], 
                        'khalti_transaction_id' => $verificationResult['idx'],
                        'transaction_id' => $verificationResult['idx'],
                        'order_id' => $sale->transaction_uuid ?? $sale->invoice_number,
                        'status' => 'PAID',
                        'amount' => $sale->final_amount,
                        'total_amount' => $sale->total_amount,
                        'raw_response' => json_encode($verificationResult),
                        'paid_at' => now(),
                        'updated_at' => now()
                    ]
                );

                $sale->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => 'completed'
                ]);

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Payment verified successfully.']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Database error.'], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Verification failed.'], 400);
    }
}
