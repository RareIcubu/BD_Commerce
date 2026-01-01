<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Zmieniamy 'id' na 'role_id' zgodnie z Twoją dokumentacją
        $roles = [
            ['role_id' => 1, 'name' => 'client'],
            ['role_id' => 2, 'name' => 'seller'],
            ['role_id' => 3, 'name' => 'admin'],
        ];

        DB::table('roles')->insertOrIgnore($roles);
    }
}
