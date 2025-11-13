<?php

namespace Database\Seeders;

use App\Models\Token;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = User::where('role', 'client')->take(3)->get();
        $tokens = Token::all();

        if ($clients->isEmpty() || $tokens->isEmpty()) {
            return;
        }

        // |KB Примеры транзакций для разных статусов и типов
        foreach ($clients as $index => $client) {
            $token = $tokens[$index % $tokens->count()];
            $amount = 0.5 + $index * 0.25;
            $price = (float) $token->current_price;
            $total = $amount * $price;
            $fee = $total * 0.01;

            Transaction::updateOrCreate(
                ['transaction_id' => 'TXN_SEED_COMPLETED_' . ($index + 1)],
                [
                    'user_id' => $client->id,
                    'token_id' => $token->id,
                    'type' => 'buy',
                    'status' => 'completed',
                    'amount' => $amount,
                    'price' => $price,
                    'total_amount' => $total,
                    'fee' => $fee,
                    'payment_method' => 'bank_transfer',
                    'payment_reference' => 'BANK-' . Str::padLeft((string) ($index + 1), 5, '0'),
                    'metadata' => [
                        'note' => 'Сделка заполнена через сидер',
                    ],
                    'processed_at' => now()->subDays(3 - $index),
                ]
            );

            Transaction::updateOrCreate(
                ['transaction_id' => 'TXN_SEED_PENDING_' . ($index + 1)],
                [
                    'user_id' => $client->id,
                    'token_id' => $token->id,
                    'type' => 'sell',
                    'status' => 'pending',
                    'amount' => $amount / 2,
                    'price' => $price * 1.05,
                    'total_amount' => ($amount / 2) * $price * 1.05,
                    'fee' => $fee / 2,
                    'payment_method' => 'bank_transfer',
                    'metadata' => [
                        'note' => 'Ожидает подтверждения банка',
                    ],
                ]
            );

            Transaction::updateOrCreate(
                ['transaction_id' => 'TXN_SEED_REFUND_' . ($index + 1)],
                [
                    'user_id' => $client->id,
                    'token_id' => $token->id,
                    'type' => 'refund',
                    'status' => 'processing',
                    'amount' => 0.1,
                    'price' => $price,
                    'total_amount' => 0.1 * $price,
                    'fee' => 0,
                    'payment_method' => 'bank_transfer',
                    'metadata' => [
                        'note' => 'Возврат в обработке',
                    ],
                ]
            );
        }
    }
}


