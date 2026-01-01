<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('tags')->count() === 0) {
            DB::table('tags')->insert([
                ['name' => 'smartfon'],
                ['name' => 'dom'],
                ['name' => 'hokej'],
                ['name' => 'fashion'],
                ['name' => 'okazja'],
                ['name' => 'nowość'],
                ['name' => 'black friday'],
            ]);
        }
    }
}
