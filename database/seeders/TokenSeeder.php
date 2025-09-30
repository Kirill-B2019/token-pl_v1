<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Token::create([
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'current_price' => 50000.00,
            'total_supply' => 21000000,
            'available_supply' => 1000000,
            'is_active' => true,
            'metadata' => [
                'description' => 'Bitcoin - первая и самая популярная криптовалюта',
                'website' => 'https://bitcoin.org',
            ],
        ]);

        \App\Models\Token::create([
            'symbol' => 'ETH',
            'name' => 'Ethereum',
            'current_price' => 3000.00,
            'total_supply' => 120000000,
            'available_supply' => 5000000,
            'is_active' => true,
            'metadata' => [
                'description' => 'Ethereum - платформа для смарт-контрактов',
                'website' => 'https://ethereum.org',
            ],
        ]);

        \App\Models\Token::create([
            'symbol' => 'USDT',
            'name' => 'Tether USD',
            'current_price' => 1.00,
            'total_supply' => 1000000000,
            'available_supply' => 800000000,
            'is_active' => true,
            'metadata' => [
                'description' => 'Tether - стейблкоин привязанный к доллару США',
                'website' => 'https://tether.to',
            ],
        ]);
    }
}
