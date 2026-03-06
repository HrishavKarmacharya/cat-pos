<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;

class PaymentController extends Controller
{
    /**
     * STEP 1: Initiate Khalti Payment
     * This method is called to send the user to Khalti.
     * Route: /khalti/initiate/{sale_id}
     */
    public function initiateKhalti($sale_id)
    {
        // 1. Find the Sale
        $sale = Sale::findOrFail($sale_id);

        // 2. Amount must be in PAISA (Rs 1 = 100 Paisa)
        // This is a common requirement for payment gateways in Nepal.
        $amountPaisa = (int) round($sale->final_amount * 100);

        // 3. Prepare Data (Follows Student Project Requirements)
        $payload = [
            "return_url" => route('khalti.return'),
            "website_url" => config('app.url'),
            "amount" => $amountPaisa,
            "purchase_order_id" => $sale->id,
            "purchase_order_name" => "Order #" . $sale->invoice_number,
        ];

        // 4. API Request using Laravel's Http Client
        // We use the Secret Key from config/services.php
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.khalti.secret_key'),
            'Content-Type' => 'application/json',
        ])->post(config('services.khalti.base_url') . '/epayment/initiate/', $payload);

        // 5. Handle Response
        if ($response->successful()) {
            $data = $response->json();

            // Create Payment Record (Status: Pending)
            DB::table('payments')->insert([
                'sale_id' => $sale->id,
                'pidx' => $data['pidx'],
                'amount' => $sale->final_amount,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Redirect user to Khalti payment page
            return redirect($data['payment_url']);
        }

        // Handle failure with descriptive message
        return redirect()->back()->with('error', 'Khalti Error: ' . $response->body());
    }

    /**
     * STEP 2: Handle Khalti Return (Verification)
     * Khalti redirects back here with 'pidx' and 'status'.
     */
    public function khaltiReturn(Request $request)
    {
        $pidx = $request->pidx;
        $status = $request->status; // "Completed", "User canceled", etc.

        // Find the payment record
        $payment = DB::table('payments')->where('pidx', $pidx)->first();

        if (!$payment) {
            return redirect()->route('sales.index')->with('error', 'Payment not found in our database.');
        }

        // If not completed by user, mark as failed
        if ($status !== 'Completed') {
            DB::table('payments')->where('pidx', $pidx)->update(['status' => 'failed']);
            return redirect()->route('sales.show', $payment->sale_id)->with('error', 'Payment was not completed.');
        }

        // ⚠️ BACKEND VERIFICATION (Security Best Practice)
        // We call Khalti lookup API to confirm the payment is actually successful.
        $verifyResponse = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.khalti.secret_key'),
        ])->post(config('services.khalti.base_url') . '/epayment/lookup/', [
            'pidx' => $pidx
        ]);

        if ($verifyResponse->successful()) {
            $data = $verifyResponse->json();

            // Check if status is truly "Completed" in Khalti's server
            if ($data['status'] === 'Completed') {
                
                // 1. Mark Payment as Completed
                DB::table('payments')->where('pidx', $pidx)->update([
                    'status' => 'completed',
                    'transaction_id' => $data['transaction_id'],
                    'updated_at' => now()
                ]);

                // 2. Mark Sale as Paid
                Sale::where('id', $payment->sale_id)->update([
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'payment_method' => 'khalti', // Securely ensure the method is recorded as Khalti
                    'paid_at' => now()
                ]);

                return redirect()->route('sales.show', $payment->sale_id)->with('flash.banner', 'Payment Successful!');
            }
        }

        return redirect()->route('sales.show', $payment->sale_id)->with('error', 'Payment verification failed.');
    }
}
