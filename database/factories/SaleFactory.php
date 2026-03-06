<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'sale_date' => $this->faker->dateTimeThisMonth(),
            'total_amount' => $this->faker->randomFloat(2, 100, 1000),
            'discount_amount' => $this->faker->randomFloat(2, 0, 100),
            'final_amount' => function (array $attributes) {
                return $attributes['total_amount'] - $attributes['discount_amount'];
            },
            'payment_method' => $this->faker->randomElement(['Cash', 'Credit Card']),
            'payment_status' => $this->faker->randomElement(['pending', 'completed', 'refunded']),
        ];
    }

    public function hasSaleItems(int $count = 1, array $attributes = [])
    {
        return $this->afterCreating(function (Sale $sale) use ($count, $attributes) {
            SaleItem::factory()->count($count)->create(array_merge(['sale_id' => $sale->id], $attributes));
        });
    }
}
