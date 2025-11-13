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
        // |KB Базовые пользователи с явными паролями для тестирования кабинетов
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@cardfly.online',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('Admin#123'),
        ]);

        User::factory()->create([
            'name' => 'Broker User',
            'email' => 'broker@cardfly.online',
            'role' => 'broker',
            'is_active' => true,
            'password' => Hash::make('Broker#123'),
        ]);

        User::factory()->create([
            'name' => 'Client User',
            'email' => 'client@cardfly.online',
            'role' => 'client',
            'is_active' => true,
            'password' => Hash::make('Client#123'),
        ]);

        // |KB Дополнительные клиенты для сценариев массового тестирования
        User::factory()
            ->count(5)
            ->create([
                'role' => 'client',
                'is_active' => true,
                'password' => Hash::make('Client#123'),
            ]);

        // |KB Заполняем справочники и бизнес-таблицы
        $this->call([
            TokenSeeder::class,
            TokenPackageSeeder::class,
            BankSeeder::class,
            BrokerSeeder::class,
            UserGroupSeeder::class,
            TransactionSeeder::class,
            UserBalanceSeeder::class,
            WinnerLoserSeeder::class,
            AuditLogSeeder::class,
            TronWalletSeeder::class,
        ]);
    }
}
