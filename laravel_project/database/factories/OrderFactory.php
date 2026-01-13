<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(['pending', 'paid', 'shipped', 'completed']), //
            'total_price' => 0, // To policzymy dynamicznie w Seederze po dodaniu produktów
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
