<?php

namespace Database\Seeders;

use App\Models\Token;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Seeder;

class UserBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = User::where('role', 'client')->get();
        $tokens = Token::all();

        if ($clients->isEmpty() || $tokens->isEmpty()) {
            return;
        }

        // |KB Балансы пользователей с примерами покупки/продажи токенов
        foreach ($clients as $index => $client) {
            foreach ($tokens as $tokenIndex => $token) {
                $balance = 2 + $index * 0.5 + $tokenIndex * 0.25;
                $locked = $tokenIndex === 0 ? 0.5 : 0.25;

                UserBalance::updateOrCreate(
                    [
                        'user_id' => $client->id,
                        'token_id' => $token->id,
                    ],
                    [
                        'balance' => $balance,
                        'locked_balance' => $locked,
                        'total_purchased' => $balance + $locked,
                        'total_sold' => $locked / 2,
                    ]
                );
            }
        }
    }
}


