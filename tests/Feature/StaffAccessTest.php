<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\ManageProducts;
use App\Livewire\ManageStockMovements;
use App\Livewire\DashboardStats;

class StaffAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $staff;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function staff_can_access_products_and_stock_indices()
    {
        $this->actingAs($this->staff);

        $this->get(route('products.index'))->assertStatus(200);
        $this->get(route('stock-movements.index'))->assertStatus(200);
    }

    /** @test */
    public function staff_cannot_access_admin_only_routes()
    {
        $this->actingAs($this->staff);

        $this->get(route('users.index'))->assertStatus(403);
        $this->get(route('products.create'))->assertStatus(403);
        $this->get(route('suppliers.index'))->assertStatus(403);
        $this->get(route('reports.index'))->assertStatus(403);
    }

    /** @test */
    public function staff_view_products_is_read_only()
    {
        $this->actingAs($this->staff);
        $product = Product::factory()->create();

        Livewire::test(ManageProducts::class)
            ->call('delete', $product->id)
            ->assertSee('You do not have permission to delete products.');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /** @test */
    public function staff_view_stock_movements_is_read_only()
    {
        $this->actingAs($this->staff);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        Livewire::test(ManageStockMovements::class)
            ->set('productId', $product->id)
            ->set('type', 'in')
            ->set('quantity', 5)
            ->set('reason', 'Test')
            ->call('save')
            ->assertSee('You do not have permission to adjust stock manually.');

        $this->assertEquals(10, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function dashboard_stats_show_units_for_staff_and_revenue_for_admin()
    {
        $sale = Sale::factory()->create(['final_amount' => 1000]);
        SaleItem::factory()->create(['sale_id' => $sale->id, 'quantity' => 5]);

        // Admin view
        $this->actingAs($this->admin);
        Livewire::test(DashboardStats::class, ['type' => 'total-sales'])
            ->assertSet('statValue', 'Rs. 1,000.00');

        // Staff view
        $this->actingAs($this->staff);
        Livewire::test(DashboardStats::class, ['type' => 'total-sales'])
            ->assertSet('statValue', 5)
            ->assertSet('title', 'Units Sold');
    }

    /** @test */
    public function dashboard_stats_hides_suppliers_for_staff()
    {
        \App\Models\Supplier::create(['name' => 'Supplier 1', 'email' => 's1@test.com', 'address' => 'Test', 'phone' => '123']);

        // Admin view
        $this->actingAs($this->admin);
        Livewire::test(DashboardStats::class, ['type' => 'total-suppliers'])
            ->assertSet('statValue', 1);

        // Staff view
        $this->actingAs($this->staff);
        Livewire::test(DashboardStats::class, ['type' => 'total-suppliers'])
            ->assertSet('statValue', null);
    }
}
