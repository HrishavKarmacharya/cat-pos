<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Simple eSewa Payment Controller
 * Handles eSewa payment for existing sales
 */
class SimpleEsewaController extends Controller
{
    /**
     * Initiate eSewa payment for a sale
     */
    public function initiatePayment($saleId)
    {
        // Find the sale
        $sale = Sale::findOrFail($saleId);
        
        // Check if already paid
        if ($sale->payment_status === 'paid') {
            return redirect()->route('sales.show', $saleId)
                ->with('error', 'This sale has already been paid.');
        }
        
        // Prepare eSewa payment data
        $amount = number_format($sale->final_amount, 2, '.', '');
        
        $paymentData = [
            'amt' => $amount,           // Product amount
            'psc' => '0',               // Service charge
            'pdc' => '0',               // Delivery charge
            'txAmt' => '0',             // Tax amount
            'tAmt' => $amount,          // Total amount
            'pid' => 'SALE-' . $sale->id, // Product/Transaction ID
            'scd' => config('services.esewa.merchant_code'), // Merchant code
            'su' => route('simple.esewa.success'), // Success URL
            'fu' => route('simple.esewa.failure'), // Failure URL
        ];
        
        Log::info('eSewa Payment Initiated', [
            'sale_id' => $saleId,
            'amount' => $amount,
            'pid' => $paymentData['pid']
        ]);
        
        // Return view with auto-submitting form
        return view('esewa.payment-form', [
            'paymentData' => $paymentData,
            'gatewayUrl' => config('services.esewa.gateway_url'),
            'sale' => $sale
        ]);
    }
    
    /**
     * Handle successful payment
     */
    public function handleSuccess(Request $request)
    {
        // eSewa sends these parameters on success
        $refId = $request->query('refId');
        $oid = $request->query('oid'); // This is our pid (SALE-{id})
        $amt = $request->query('amt');
        
        Log::info('eSewa Success Callback', [
            'refId' => $refId,
            'oid' => $oid,
            'amt' => $amt
        ]);
        
        // Extract sale ID from oid (format: SALE-123)
        $saleId = str_replace('SALE-', '', $oid);
        
        // Find and update the sale
        $sale = Sale::find($saleId);
        
        if ($sale) {
            $sale->update([
                'payment_status' => 'paid',
                'esewa_ref_id' => $refId,
                'paid_at' => now()
            ]);
            
            Log::info('Sale marked as paid', ['sale_id' => $saleId, 'ref_id' => $refId]);
            
            return redirect()->route('sales.show', $saleId)
                ->with('success', 'Payment successful! Reference ID: ' . $refId);
        }
        
        return redirect()->route('sales.index')
            ->with('error', 'Sale not found.');
    }
    
    /**
     * Handle failed payment
     */
    public function handleFailure(Request $request)
    {
        $pid = $request->query('pid');
        
        Log::warning('eSewa Payment Failed', ['pid' => $pid]);
        
        // Extract sale ID
        $saleId = str_replace('SALE-', '', $pid);
        
        // Update sale status
        $sale = Sale::find($saleId);
        if ($sale) {
            $sale->update(['payment_status' => 'failed']);
        }
        
        return redirect()->route('sales.show', $saleId)
            ->with('error', 'Payment failed. Please try again.');
    }
}
