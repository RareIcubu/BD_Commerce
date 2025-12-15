<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        // Pobieramy losowe ID roli, ale w Seederze nadpiszemy to dla konkretnych przypadków
        return [
            'role_id' => Role::inRandomOrder()->first()->id ?? 1,
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(), //
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), // Domyślne hasło dla wszystkich
            'created_at' => now(),
            'modified_at' => now(),
        ];
    }
}
