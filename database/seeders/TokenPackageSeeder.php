<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TokenPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\TokenPackage::create([
            'name' => 'Стартовый пакет',
            'description' => 'Идеальный пакет для начинающих',
            'token_amount' => 0.01,
            'price' => 500.00,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        \App\Models\TokenPackage::create([
            'name' => 'Базовый пакет',
            'description' => 'Популярный выбор среди пользователей',
            'token_amount' => 0.05,
            'price' => 2400.00,
            'discount_percentage' => 5,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        \App\Models\TokenPackage::create([
            'name' => 'Премиум пакет',
            'description' => 'Для серьезных инвесторов',
            'token_amount' => 0.1,
            'price' => 4500.00,
            'discount_percentage' => 10,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        \App\Models\TokenPackage::create([
            'name' => 'VIP пакет',
            'description' => 'Максимальная выгода',
            'token_amount' => 0.5,
            'price' => 20000.00,
            'discount_percentage' => 20,
            'is_active' => true,
            'sort_order' => 4,
        ]);
    }
}
