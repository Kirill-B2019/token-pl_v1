<?php

namespace Database\Seeders;

use App\Models\Broker;
use Illuminate\Database\Seeder;

class BrokerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // |KB Справочник брокеров для имитации интеграции с биржами
        $brokers = [
            [
                'name' => 'Prime Exchange Broker',
                'api_key' => 'BROKER_API_001',
                'api_secret' => 'broker_secret_001',
                'exchange_url' => 'https://prime-exchange.example/api',
                'reserve_balance' => 12500.50000000,
                'min_reserve_threshold' => 5000.00000000,
                'settings' => [
                    'timezone' => 'Europe/Moscow',
                    'max_daily_orders' => 500,
                ],
            ],
            [
                'name' => 'Global Liquidity Partner',
                'api_key' => 'BROKER_API_002',
                'api_secret' => 'broker_secret_002',
                'exchange_url' => 'https://global-liquidity.example/api',
                'reserve_balance' => 25000.00000000,
                'min_reserve_threshold' => 8000.00000000,
                'settings' => [
                    'timezone' => 'Europe/London',
                    'max_daily_orders' => 750,
                ],
            ],
        ];

        foreach ($brokers as $data) {
            Broker::updateOrCreate(
                ['api_key' => $data['api_key']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}


