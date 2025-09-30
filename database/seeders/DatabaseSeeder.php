<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@token-pl.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create broker user
        User::factory()->create([
            'name' => 'Broker User',
            'email' => 'broker@token-pl.com',
            'role' => 'broker',
            'is_active' => true,
        ]);

        // Create test client
        User::factory()->create([
            'name' => 'Test Client',
            'email' => 'client@token-pl.com',
            'role' => 'client',
            'is_active' => true,
        ]);

        // Run seeders
        $this->call([
            TokenSeeder::class,
            TokenPackageSeeder::class,
            BankSeeder::class,
        ]);
    }
}
