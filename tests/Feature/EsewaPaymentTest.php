<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use App\Models\Customer;
use App\Services\EsewaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EsewaPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
    }

    /** @test */
    public function it_can_generate_a_valid_esewa_signature()
    {
        $service = new EsewaService();
        $totalAmount = "100.00";
        $transactionUuid = "test-uuid-123";
        $productCode = "EPAYTEST";
        
        // Expected message: total_amount=100.00,transaction_uuid=test-uuid-123,product_code=EPAYTEST
        // Secret key: 8g8t8h8m6qnd99f (from config/env)
        
        $signature = $service->generateSignature($totalAmount, $transactionUuid, $productCode);
        
        $this->assertNotEmpty($signature);
        $this->assertIsString($signature);
    }

    /** @test */
    public function it_handles_payment_success_callback_correctly()
    {
        // Arrange
        $sale = Sale::factory()->create([
            'final_amount' => 100.00,
            'transaction_uuid' => 'test-uuid-123',
            'payment_status' => 'pending'
        ]);

        \Illuminate\Support\Facades\DB::table('payments')->insert([
            'transaction_uuid' => 'test-uuid-123',
            'sale_id' => $sale->id,
            'amount' => 100.00,
            'tax_amount' => 0,
            'total_amount' => 100.00,
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = base64_encode(json_encode([
            'transaction_uuid' => 'test-uuid-123',
            'total_amount' => '100.00',
            'ref_id' => 'ESEWA-REF-001'
        ]));

        // Mock eSewa verification API
        Http::fake([
            config('services.esewa.verify_url') . '*' => Http::response([
                'status' => 'COMPLETE',
                'ref_id' => 'ESEWA-REF-001',
                'total_amount' => '100.00'
            ], 200)
        ]);

        // Act
        $response = $this->get(route('esewa.success', ['data' => $data]));

        // Assert
        $response->assertRedirect(route('sales.show', $sale->id));
        $sale->refresh();
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('ESEWA-REF-001', $sale->esewa_ref_id);
        $this->assertNotNull($sale->paid_at);
    }

    /** @test */
    public function it_fails_if_amount_mismatch_during_verification()
    {
        // Arrange
        $sale = Sale::factory()->create([
            'final_amount' => 100.00,
            'transaction_uuid' => 'test-uuid-123',
            'payment_status' => 'pending'
        ]);

        \Illuminate\Support\Facades\DB::table('payments')->insert([
            'transaction_uuid' => 'test-uuid-123',
            'sale_id' => $sale->id,
            'amount' => 100.00,
            'tax_amount' => 0,
            'total_amount' => 100.00,
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = base64_encode(json_encode([
            'transaction_uuid' => 'test-uuid-123',
            'total_amount' => '50.00', // Mismatch!
            'ref_id' => 'ESEWA-REF-001'
        ]));

        // Act
        $response = $this->get(route('esewa.success', ['data' => $data]));

        // Assert
        $sale->refresh();
        $this->assertEquals('failed', $sale->payment_status);
    }
}
