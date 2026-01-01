<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            // To zostanie nadpisane w Seederze, ale warto mieć default
            'seller_id' => User::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true), //
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000), // Cena od 10.00 do 1000.00
            'front_image' => 'https://placehold.co/600x400', // Placeholder obrazka
            'is_active' => true, // Ważne dla zapytań SQL z dokumentacji
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'modified_at' => now(),
        ];
    }
}
