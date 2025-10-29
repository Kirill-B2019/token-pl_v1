<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TronWallet;
use Illuminate\Database\Seeder;

class TronWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create TRON wallets for existing users
        $users = User::where('role', 'client')->get();
        
        foreach ($users as $user) {
            // Check if user already has a wallet
            $existingWallet = TronWallet::where('user_id', $user->id)->first();
            
            if (!$existingWallet) {
                TronWallet::create([
                    'user_id' => $user->id,
                    'address' => 'T' . substr(hash('sha256', $user->email . time()), 0, 33),
                    'private_key' => bin2hex(random_bytes(32)),
                    'public_key' => hash('sha256', $user->email . 'public'),
                    'mnemonic' => $this->generateMnemonic(),
                    'is_active' => true,
                    'balance_usdt' => 0,
                    'balance_trx' => 0,
                    'metadata' => [
                        'created_via' => 'seeder',
                        'network' => 'mainnet',
                        'version' => '1.0',
                        'test_wallet' => true,
                    ],
                ]);
            }
        }
    }

    /**
     * Generate a simple mnemonic phrase
     */
    private function generateMnemonic(): string
    {
        $words = [
            'abandon', 'ability', 'able', 'about', 'above', 'absent', 'absorb', 'abstract',
            'absurd', 'abuse', 'access', 'accident', 'account', 'accuse', 'achieve', 'acid',
            'acoustic', 'acquire', 'across', 'act', 'action', 'actor', 'actress', 'actual',
            'adapt', 'add', 'addict', 'address', 'adjust', 'admit', 'adult', 'advance'
        ];
        
        $mnemonic = [];
        for ($i = 0; $i < 12; $i++) {
            $mnemonic[] = $words[array_rand($words)];
        }
        
        return implode(' ', $mnemonic);
    }
}



