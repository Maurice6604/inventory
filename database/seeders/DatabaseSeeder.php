<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@inventory.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Staff User
        User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@inventory.local',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
    }
}
