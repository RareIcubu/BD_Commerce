<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Potrzebne do transakcji dla szybkości

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Wyłączamy sprawdzanie kluczy obcych na chwilę, żeby szybciej czyścić bazę
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Product::truncate();
        Order::truncate();
        // ... (ewentualnie inne tabele)
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->call([RoleSeeder::class]);

        // 1. Dane statyczne
        $categories = Category::factory()->count(10)->create();
        $tags = Tag::factory()->count(20)->create();

        // 2. Użytkownicy Specjalni (do testów manualnych)
        $client = User::factory()->create([
            'email' => 'klient@example.com',
            'role_id' => 1, // Klient
        ]);

        $seller = User::factory()->create([
            'email' => 'sprzedawca@example.com',
            'role_id' => 2, // Sprzedawca
        ]);

        $password = \Illuminate\Support\Facades\Hash::make('password');
        $users = User::factory()->count(1000)->create([
            'role_id' => 1,
            'password' => $password,
        ]);

        echo "Wygenerowano 1000 użytkowników.\n";

        // 4. Produkty (50 sztuk od naszego sprzedawcy)
        $products = Product::factory()
            ->count(50)
            ->state(fn () => [
                'seller_id' => $seller->id,
                'category_id' => $categories->random()->category_id,
            ])
            ->create();

        foreach ($products as $product) {
            $product->tags()->attach($tags->random(rand(1, 3))->modelKeys());
        }

        // 5. Zamówienia (Symulacja: tylko 200 z 1000 użytkowników coś kupiło)
        // Bierzemy 200 losowych userów z naszej puli 1000
        $activeShoppers = $users->random(200);

        // Dodajemy też zamówienia dla naszego testowego klienta
        $activeShoppers->push($client);

        foreach ($activeShoppers as $shopper) {
            Order::factory()
                ->count(rand(1, 3)) // Każdy kupił 1-3 razy
                ->create([
                    'user_id' => $shopper->getKey()
                ])
                ->each(function ($order) use ($products) {
                    $selectedProducts = $products->random(rand(1, 5));
                    $total = 0;
                    foreach ($selectedProducts as $product) {
                        $qty = rand(1, 2);
                        $price = $product->price;
                        $order->products()->attach($product->getKey(), [
                            'quantity' => $qty,
                            'price_when_purchased' => $price
                        ]);
                        $total += $qty * $price;
                    }
                    $order->update(['price_total' => $total]);
                });
        }

        echo "Wygenerowano zamówienia dla 200 użytkowników.\n";
    }
}
