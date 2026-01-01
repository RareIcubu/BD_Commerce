<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('products')->count() === 0) {
            DB::table('products')->insert([
                [
                    'name' => 'Laptop X100',
                    'description' => 'Laptop gamingowy',
                    'price' => 4999.99,
                    'stock_quantity' => 15,
                    'created_at' => now(), 'updated_at' => now()
                ],
                [
                    'name' => 'Smartwatch S25 Pro',
                    'description' => 'Sport i monitorowanie zdrowia',
                    'price' => 799.00,
                    'stock_quantity' => 50,
                    'created_at' => now(), 'updated_at' => now()
                ],
                [
                    'name' => 'Kamera GoPro 4K',
                    'description' => 'Kamera sportowa o rozdzielczości 4K',
                    'price' => 1250.50,
                    'stock_quantity' => 5,
                    'created_at' => now(), 'updated_at' => now()
                ],
            ]);
        }
    }
}
