<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use App\Services\KhaltiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KhaltiPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user and customer for testing
        $user = User::factory()->create();
        $this->actingAs($user);
        
        config(['services.khalti.public_key' => 'test_public_key']);
        config(['services.khalti.secret_key' => 'test_secret_key']);
        config(['services.khalti.base_url' => 'https://a.khalti.com/api/v2']);
        $this->withoutExceptionHandling();
    }

    public function test_khalti_initiation_saves_payment_record()
    {
        $customer = Customer::factory()->create();
        $sale = Sale::factory()->create([
            'customer_id' => $customer->id,
            'final_amount' => 100.00,
            'invoice_number' => 'INV-1001',
            'transaction_uuid' => 'test-uuid-123'
        ]);

        Http::fake([
            'https://a.khalti.com/api/v2/epayment/initiate/' => Http::response([
                'pidx' => 'test_pidx',
                'payment_url' => 'https://test.khalti.com/pay/test_pidx'
            ], 200)
        ]);

        $khaltiService = new KhaltiService();
        $response = $khaltiService->initiatePayment($sale);

        $this->assertNotNull($response);
        $this->assertEquals('test_pidx', $response['pidx']);

        $this->assertDatabaseHas('payments', [
            'order_id' => 'test-uuid-123',
            'pidx' => 'test_pidx',
            'status' => 'PENDING',
            'amount' => 100.00
        ]);
    }

    public function test_khalti_callback_verifies_and_updates_status()
    {
        $sale = Sale::factory()->create(['final_amount' => 100.00]);
        
        DB::table('payments')->insert([
            'order_id' => 'test-order-id',
            'sale_id' => $sale->id,
            'amount' => 100.00,
            'status' => 'PENDING',
            'pidx' => 'test_pidx',
            'gateway' => 'khalti',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Http::fake([
            'https://a.khalti.com/api/v2/epayment/lookup/' => Http::response([
                'pidx' => 'test_pidx',
                'total_amount' => 10000, // 100 Rs = 10000 Paisa
                'status' => 'Completed',
                'transaction_id' => 'KHALTI_TRANS_123'
            ], 200)
        ]);

        $response = $this->get(route('khalti.return', [
            'pidx' => 'test_pidx',
            'status' => 'Completed',
            'purchase_order_id' => 'test-order-id'
        ]));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('payments', [
            'pidx' => 'test_pidx',
            'status' => 'PAID',
        ]);

        $this->assertEquals('paid', $sale->fresh()->payment_status);
        $this->assertEquals('completed', $sale->fresh()->status);
    }

    public function test_khalti_callback_fails_on_amount_mismatch()
    {
        $sale = Sale::factory()->create(['final_amount' => 100.00]);
        
        DB::table('payments')->insert([
            'order_id' => 'test-order-id',
            'sale_id' => $sale->id,
            'amount' => 100.00,
            'status' => 'PENDING',
            'pidx' => 'test_pidx',
            'gateway' => 'khalti',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Http::fake([
            'https://a.khalti.com/api/v2/epayment/lookup/' => Http::response([
                'pidx' => 'test_pidx',
                'total_amount' => 5000, // Wrong amount (50 Rs)
                'status' => 'Completed',
                'transaction_id' => 'KHALTI_TRANS_123'
            ], 200)
        ]);

        $response = $this->get(route('khalti.return', [
            'pidx' => 'test_pidx',
            'status' => 'Completed'
        ]));

        $this->assertDatabaseHas('payments', [
            'pidx' => 'test_pidx',
            'status' => 'FAILED'
        ]);

        $this->assertEquals('pending', $sale->fresh()->payment_status); // Or failed depending on implementation
    }

    public function test_khalti_checkout_verification()
    {
        $sale = Sale::factory()->create([
            'final_amount' => 100.00,
            'invoice_number' => 'INV-2026-CHKO',
            'transaction_uuid' => (string) str()->uuid() . '-test'
        ]);
        
        Http::fake([
            'https://khalti.com/api/v2/payment/verify/' => Http::response([
                'idx' => 'test_checkout_idx',
                'amount' => 10000,
                'token' => 'test_token'
            ], 200)
        ]);

        $response = $this->post(route('khalti.verify'), [
            'token' => 'test_token',
            'amount' => 10000,
            'sale_id' => $sale->id
        ]);

        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('payments', [
            'sale_id' => $sale->id,
            'pidx' => 'test_checkout_idx',
            'status' => 'PAID',
        ]);

        $this->assertEquals('paid', $sale->fresh()->payment_status);
        $this->assertEquals('completed', $sale->fresh()->status);
    }
}
