<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Uruchamia seedera bazy danych.
     */
    public function run(): void
    {
        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                [
                    'name' => 'admin',
                ],
                [
                    'name' => 'client',
                ],
                [
                    'name' => 'seller',
                ],
            ]);
        }
    }
}