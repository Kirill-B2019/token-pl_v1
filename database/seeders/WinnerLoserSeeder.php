<?php

namespace Database\Seeders;

use App\Models\Token;
use App\Models\User;
use App\Models\WinnerLoser;
use Illuminate\Database\Seeder;

class WinnerLoserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = User::where('role', 'client')->take(3)->get();
        $token = Token::first();

        if ($clients->isEmpty() || !$token) {
            return;
        }

        // |KB Примеры выплат победителям и проигравшим
        foreach ($clients as $index => $client) {
            WinnerLoser::updateOrCreate(
                [
                    'user_id' => $client->id,
                    'type' => 'winner',
                    'token_id' => $token->id,
                ],
                [
                    'amount' => 150 + ($index * 50),
                    'token_amount' => 0.25 + ($index * 0.1),
                    'status' => $index === 0 ? 'processed' : 'pending',
                    'processed_at' => $index === 0 ? now()->subDay() : null,
                    'metadata' => [
                        'reason' => 'Еженедельный розыгрыш',
                        'batch' => 'seed-winners',
                    ],
                ]
            );

            WinnerLoser::updateOrCreate(
                [
                    'user_id' => $client->id,
                    'type' => 'loser',
                    'token_id' => $token->id,
                ],
                [
                    'amount' => 50 + ($index * 25),
                    'token_amount' => 0.1 + ($index * 0.05),
                    'status' => 'pending',
                    'metadata' => [
                        'reason' => 'Коррекция портфеля',
                        'batch' => 'seed-losers',
                    ],
                ]
            );
        }
    }
}


