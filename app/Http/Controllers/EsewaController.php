<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\EsewaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EsewaController extends Controller
{
    protected EsewaService $esewaService;

    public function __construct(EsewaService $esewaService)
    {
        $this->esewaService = $esewaService;
    }

    /**
     * Initiate payment - either redirect to eSewa or show QR code
     */
    public function initiatePayment($saleId, Request $request)
    {
        $sale = Sale::findOrFail($saleId);

        if ($sale->payment_status === 'paid') {
            return redirect()->route('sales.show', $sale->id)
                ->with('error', 'This sale has already been paid.');
        }

        // Ensure we have a transaction UUID
        if (!$sale->transaction_uuid) {
            $sale->update(['transaction_uuid' => (string) str()->uuid() . '-' . $sale->id]);
        }

        // Check if QR is requested
        if ($request->has('qr')) {
            return $this->showQr($sale->id);
        }

        $paymentData = $this->esewaService->preparePaymentData($sale);
        $gatewayUrl = $this->esewaService->getGatewayUrl();

        Log::info('Initiating eSewa Form Payment:', [
            'sale_id' => $sale->id,
            'transaction_uuid' => $sale->transaction_uuid,
            'amount' => $sale->final_amount
        ]);

        return view('payments.esewa_redirect', compact('paymentData', 'gatewayUrl'));
    }

    /**
     * Show QR code for the transaction
     */
    public function showQr($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        
        $qrPayload = $this->esewaService->generateQrPayload($sale);
        $paymentData = $this->esewaService->preparePaymentData($sale);

        Log::info('Generating eSewa QR Payment:', [
            'sale_id' => $sale->id,
            'transaction_uuid' => $sale->transaction_uuid,
            'amount' => $sale->final_amount
        ]);

        return view('payments.qr_payment', compact('sale', 'qrPayload', 'paymentData'));
    }

    /**
     * Verify payment status (AJAX/Polling)
     */
    public function checkStatus($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        
        // If already marked as paid, return success
        if ($sale->payment_status === 'paid') {
            return response()->json(['status' => 'paid']);
        }

        // Verify with eSewa API
        $verificationResult = $this->esewaService->verifyTransaction(
            $sale->transaction_uuid, 
            number_format($sale->final_amount, 2, '.', '')
        );

        if ($verificationResult && isset($verificationResult['status']) && $verificationResult['status'] === 'COMPLETE') {
            $sale->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'completed'
            ]);
            return response()->json(['status' => 'paid']);
        }

        return response()->json(['status' => 'pending']);
    }

    /**
     * Handle success callback from eSewa
     */
    public function paymentSuccess(Request $request)
    {
        // 1. Validate request parameters
        if (!$request->has('data')) {
            Log::error('eSewa callback: Missing data parameter');
            return $this->respondError('Missing response data.', 400);
        }

        $encodedData = $request->query('data');
        
        Log::info('eSewa Success Callback Received:', [
            'raw_query' => $request->all()
        ]);

        // 2. Decode base64 data
        $decodedData = json_decode(base64_decode($encodedData), true);
        
        if (!$decodedData || !isset($decodedData['transaction_uuid']) || !isset($decodedData['total_amount'])) {
            Log::error('eSewa callback: Invalid or malformed JSON data', ['encoded_data' => $encodedData]);
            return $this->respondError('Invalid response format.', 422);
        }

        Log::info('eSewa Decoded Response:', $decodedData);

        $transactionUuid = $decodedData['transaction_uuid'];
        $totalAmount = str_replace(',', '', $decodedData['total_amount']);
        $refId = $decodedData['ref_id'] ?? null;

        // 3. Find the payment record & PREVENT REPLAY ATTACKS
        $payment = \Illuminate\Support\Facades\DB::table('payments')->where('transaction_uuid', $transactionUuid)->first();

        if (!$payment) {
            Log::error('eSewa callback: Payment UUID not found in database', ['transaction_uuid' => $transactionUuid]);
            return $this->respondError('Transaction not found.', 404);
        }

        // Prevent duplicate updates
        if ($payment->status === 'PAID') {
            Log::warning('eSewa callback: Transaction already processed as PAID', ['transaction_uuid' => $transactionUuid]);
            return $this->respondSuccess('Transaction already processed.', [
                'transaction_uuid' => $transactionUuid,
                'status' => 'PAID'
            ]);
        }

        // 4. SERVER-SIDE VERIFICATION
        Log::info('eSewa server-side verification starting', ['transaction_uuid' => $transactionUuid]);
        $verificationResult = $this->esewaService->verifyTransaction($transactionUuid, $totalAmount);

        if ($verificationResult && isset($verificationResult['status']) && $verificationResult['status'] === 'COMPLETE') {
            
            // 5. VALIDATE AMOUNT MATCHES
            $expectedAmount = (float) number_format((float)$payment->total_amount, 2, '.', '');
            $receivedAmount = (float) number_format((float)$totalAmount, 2, '.', '');

            if (abs($expectedAmount - $receivedAmount) > 0.01) {
                Log::error('eSewa amount mismatch', [
                    'expected' => $expectedAmount,
                    'received' => $receivedAmount,
                    'transaction_uuid' => $transactionUuid
                ]);
                return $this->respondError('Amount mismatch verification failed.', 422);
            }

            // 6. UPDATE DATABASE (Update Payment Table first)
            \Illuminate\Support\Facades\DB::table('payments')->where('transaction_uuid', $transactionUuid)->update([
                'status' => 'PAID',
                'esewa_reference' => $refId,
                'raw_response' => json_encode($decodedData),
                'paid_at' => now(),
                'updated_at' => now()
            ]);

            // Update Sale Table
            if ($payment->sale_id) {
                $sale = Sale::find($payment->sale_id);
                if ($sale) {
                    $sale->update([
                        'payment_status' => 'paid',
                        'esewa_ref_id' => $refId,
                        'paid_at' => now(),
                        'status' => 'completed'
                    ]);
                    Log::info('Sale marked as PAID', ['sale_id' => $sale->id]);
                }
            }

            return $this->respondSuccess('Payment verified successfully.', [
                'transaction_uuid' => $transactionUuid,
                'status' => 'PAID',
                'ref_id' => $refId
            ]);
        }

        // Mark as FAILED if verification fails
        \Illuminate\Support\Facades\DB::table('payments')->where('transaction_uuid', $transactionUuid)->update([
            'status' => 'FAILED',
            'raw_response' => json_encode($decodedData),
            'updated_at' => now()
        ]);

        // Update Sale Table to failed
        if ($payment->sale_id) {
            $sale = Sale::find($payment->sale_id);
            if ($sale) {
                $sale->update(['payment_status' => 'failed']);
            }
        }

        return $this->respondError('Payment verification from eSewa failed.', 422);
    }

    /**
     * Helper for consistent responses (JSON or Redirect)
     */
    private function respondSuccess($message, $data = [])
    {
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(array_merge(['message' => $message, 'success' => true], $data));
        }

        $saleId = $data['sale_id'] ?? null;
        if (!$saleId && isset($data['transaction_uuid'])) {
            $payment = \Illuminate\Support\Facades\DB::table('payments')->where('transaction_uuid', $data['transaction_uuid'])->first();
            $saleId = $payment->sale_id ?? null;
        }

        return redirect()->route($saleId ? 'sales.show' : 'sales.index', $saleId ?? [])
            ->with('success', $message);
    }

    private function respondError($message, $code = 400)
    {
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => $message, 'success' => false], $code);
        }

        return redirect()->route('sales.index')->with('error', $message);
    }

    /**
     * Handle failure callback from eSewa
     */
    public function paymentFailure(Request $request)
    {
        $encodedData = $request->query('data');
        
        Log::warning('eSewa Failure Callback Received:', $request->all());

        if ($encodedData) {
            $decodedData = json_decode(base64_decode($encodedData), true);
            if ($decodedData && isset($decodedData['transaction_uuid'])) {
                \Illuminate\Support\Facades\DB::table('payments')->where('transaction_uuid', $decodedData['transaction_uuid'])->update([
                    'status' => 'FAILED',
                    'updated_at' => now()
                ]);
            }
        }

        return redirect()->route('sales.index')->with('error', 'Payment was cancelled or failed.');
    }
}
