<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Bank::create([
            'name' => 'Сбербанк',
            'code' => 'SBER',
            'api_endpoint' => 'https://api.sberbank.ru/payments',
            'merchant_id' => 'MERCHANT_SBER_001',
            'api_key' => 'sber_api_key_12345',
            'api_secret' => 'sber_secret_67890',
            'commission_rate' => 0.025,
            'is_active' => true,
            'settings' => [
                'currency' => 'RUB',
                'timeout' => 30,
                'retry_attempts' => 3,
            ],
        ]);

        \App\Models\Bank::create([
            'name' => 'ВТБ',
            'code' => 'VTB',
            'api_endpoint' => 'https://api.vtb.ru/payments',
            'merchant_id' => 'MERCHANT_VTB_001',
            'api_key' => 'vtb_api_key_12345',
            'api_secret' => 'vtb_secret_67890',
            'commission_rate' => 0.03,
            'is_active' => true,
            'settings' => [
                'currency' => 'RUB',
                'timeout' => 30,
                'retry_attempts' => 3,
            ],
        ]);

        \App\Models\Bank::create([
            'name' => 'Альфа-Банк',
            'code' => 'ALFA',
            'api_endpoint' => 'https://api.alfabank.ru/payments',
            'merchant_id' => 'MERCHANT_ALFA_001',
            'api_key' => 'alfa_api_key_12345',
            'api_secret' => 'alfa_secret_67890',
            'commission_rate' => 0.028,
            'is_active' => true,
            'settings' => [
                'currency' => 'RUB',
                'timeout' => 30,
                'retry_attempts' => 3,
            ],
        ]);
    }
}
