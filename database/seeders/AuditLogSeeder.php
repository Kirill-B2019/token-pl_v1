<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $clients = User::where('role', 'client')->take(2)->get();

        if (!$admin) {
            return;
        }

        // |KB Аудит-логи для демонстрации истории действий
        AuditLog::updateOrCreate(
            [
                'event' => 'user_login',
                'entity_type' => 'User',
                'entity_id' => $admin->id,
                'user_id' => $admin->id,
            ],
            [
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder/1.0',
                'old_values' => null,
                'new_values' => ['message' => 'Администратор вошел в систему'],
                'metadata' => ['context' => 'seed'],
            ]
        );

        foreach ($clients as $client) {
            AuditLog::updateOrCreate(
                [
                    'event' => 'transaction_completed',
                    'entity_type' => 'Transaction',
                    'entity_id' => $client->id,
                    'user_id' => $client->id,
                ],
                [
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder/1.0',
                    'old_values' => ['status' => 'pending'],
                    'new_values' => ['status' => 'completed'],
                    'metadata' => [
                        'note' => 'Событие создано для демонстрации истории',
                    ],
                ]
            );
        }
    }
}


