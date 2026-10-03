<?php

namespace Database\Factories;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commission>
 */
class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'from_user_id' => null,
            'type' => fake()->randomElement(['direct', 'indirect', 'leadership', 'consumer']),
            'amount' => fake()->randomFloat(2, 10, 500),
            'percentage' => fake()->randomFloat(2, 3, 45),
            'description' => fake()->sentence(),
            'status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
