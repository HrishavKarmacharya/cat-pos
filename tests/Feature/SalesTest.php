<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user and authenticate
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

    }

    /** @test */
    public function a_sale_can_be_created()
    {
        $this->markTestSkipped('Sale creation is handled by Livewire component ManageSale.');
    }

    /** @test */
    public function it_prevents_creating_a_sale_with_insufficient_stock()
    {
        $this->markTestSkipped('Stock validation is handled by Livewire component ManageSale.');
    }
    
    /** @test */
    public function a_sale_can_be_viewed()
    {
        // Arrange
        $sale = Sale::factory()->create([
            'invoice_number' => 'INV-2026-0001',
            'sale_date' => now(),
        ]);

        // Act
        $response = $this->get(route('sales.show', $sale));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('sales.invoice');
        $response->assertSee($sale->invoice_number);
    }

    /** @test */
    public function a_sale_can_be_updated()
    {
        $this->markTestSkipped('Sale updating is handled by Livewire component ManageSale.');
    }

    /** @test */
    public function a_sale_can_be_deleted_and_stock_is_restored()
    {

        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $sale = Sale::factory()->create();
        $sale->saleItems()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 100,
            'subtotal' => 300,
        ]);
        
        // Manually adjust stock for the test setup
        $product->decrement('stock_quantity', 3);
        $this->assertEquals(7, $product->fresh()->stock_quantity);

        // Act
        $response = $this->get(route('sales.index'));
        $token = session()->token();

        $response = $this->withHeaders([
            'X-CSRF-TOKEN' => $token,
        ])->delete(route('sales.destroy', $sale));

        // Assert
        $response->assertRedirect(route('sales.index'));
        $response->assertSessionHas('success', 'Sale deleted successfully!');
        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
        $this->assertDatabaseMissing('sale_items', ['sale_id' => $sale->id]);
        $this->assertEquals(10, $product->fresh()->stock_quantity);
    }
}