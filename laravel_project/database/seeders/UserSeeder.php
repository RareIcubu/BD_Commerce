<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;           

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    if (DB::table('users')->count() === 0) {
        $admin_role_id = DB::table('roles')->where('name', 'admin')->value('role_id') ?? 1;
        $client_role_id = DB::table('roles')->where('name', 'client')->value('role_id') ?? 2;
        $seller_role_id = DB::table('roles')->where('name', 'seller')->value('role_id') ?? 3;

        DB::table('users')->insert([
            [
                'role_id' => $admin_role_id,
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'name' => 'Jan',
                'surname' => 'Kowalski',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'role_id' => $client_role_id,
                'email' => 'client@example.com',
                'password' => Hash::make('password'),
                'name' => 'Anna',
                'surname' => 'Nowak',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'role_id' => $seller_role_id,
                'email' => 'seller@example.com',
                'password' => Hash::make('password'),
                'name' => 'Piotr',
                'surname' => 'Zieliński',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }
}
}
