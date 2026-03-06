<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class EsewaPosPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
    }

    /** @test */
    public function it_can_initiate_esewa_payment_flow_in_pos()
    {
        // Arrange
        $product = Product::factory()->create(['price' => 100, 'stock_quantity' => 10]);
        $customer = Customer::factory()->create();

        // Act & Assert
        Livewire::test(\App\Livewire\ManageSale::class)
            ->set('customer_id', $customer->id)
            ->call('addProduct', $product->id)
            ->set('payment_method', 'esewa')
            ->call('finalizeSale')
            ->assertHasNoErrors()
            ->assertSet('isVerifyingEsewa', true)
            ->assertSet('pending_sale_id', function ($id) {
                return !is_null($id);
            });

        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
            'payment_method' => 'esewa',
            'payment_status' => 'pending',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_can_verify_esewa_payment_automatically()
    {
        // Arrange
        $sale = Sale::factory()->create([
            'final_amount' => 100.00,
            'transaction_uuid' => 'test-uuid-123',
            'payment_status' => 'pending',
            'payment_method' => 'esewa'
        ]);

        // Mock eSewa verification API
        Http::fake([
            config('services.esewa.verify_url') . '*' => Http::response([
                'status' => 'COMPLETE',
                'p_id' => 'test-uuid-123',
                'ref_id' => 'ESEWA-REF-001',
                'total_amount' => '100.00'
            ], 200)
        ]);

        // Act & Assert
        Livewire::test(\App\Livewire\ManageSale::class)
            ->set('pending_sale_id', $sale->id)
            ->set('pending_transaction_uuid', $sale->transaction_uuid)
            ->call('verifyEsewaPayment')
            ->assertRedirect(route('sales.show', $sale->id));

        $sale->refresh();
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals('ESEWA-REF-001', $sale->esewa_ref_id);
    }

    /** @test */
    public function it_can_complete_esewa_payment_manually()
    {
        // Arrange
        $sale = Sale::factory()->create([
            'final_amount' => 100.00,
            'transaction_uuid' => 'test-uuid-123',
            'payment_status' => 'pending',
            'payment_method' => 'esewa'
        ]);

        // Act & Assert
        Livewire::test(\App\Livewire\ManageSale::class)
            ->set('pending_sale_id', $sale->id)
            ->set('esewa_ref_id', 'MANUAL-REF-999')
            ->call('completeSaleManually')
            ->assertRedirect(route('sales.show', $sale->id));

        $sale->refresh();
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals('MANUAL-REF-999', $sale->esewa_ref_id);
    }
}
