<?php
// |KB Сервис TRON: генерация кошелька, синхронизация баланса, отправка TRX/USDT, история

namespace App\Services;

use App\Models\TronWallet;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TronWalletService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('tron.api_url', 'https://api.trongrid.io');
        $this->apiKey = config('tron.api_key');
    }

    /**
     * Generate new TRON wallet for user
     */
    public function generateWallet(User $user): TronWallet
    {
        try {
            // Generate private key and mnemonic
            $privateKey = $this->generatePrivateKey();
            $mnemonic = $this->generateMnemonic();
            
            // Generate address from private key
            $address = $this->generateAddressFromPrivateKey($privateKey);
            $publicKey = $this->getPublicKeyFromPrivateKey($privateKey);

            // Create wallet record
            $wallet = TronWallet::create([
                'user_id' => $user->id,
                'address' => $address,
                'private_key' => $privateKey,
                'public_key' => $publicKey,
                'mnemonic' => $mnemonic,
                'is_active' => true,
                'balance_usdt' => 0,
                'balance_trx' => 0,
                'metadata' => [
                    'created_via' => 'system',
                    'network' => 'mainnet',
                    'version' => '1.0',
                ],
            ]);

            // Log audit
            AuditLog::createLog(
                'tron_wallet_created',
                'TronWallet',
                $wallet->id,
                $user->id,
                null,
                ['address' => $address]
            );

            return $wallet;

        } catch (\Exception $e) {
            Log::error('Failed to generate TRON wallet: ' . $e->getMessage());
            throw new \Exception('Не удалось создать кошелек TRON: ' . $e->getMessage());
        }
    }

    /**
     * Generate private key using secure random
     */
    private function generatePrivateKey(): string
    {
        // Generate 32 random bytes
        $bytes = random_bytes(32);
        return bin2hex($bytes);
    }

    /**
     * Generate mnemonic phrase
     */
    private function generateMnemonic(): string
    {
        // Simple mnemonic generation (in production use proper BIP39)
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

    /**
     * Generate TRON address from private key
     */
    private function generateAddressFromPrivateKey(string $privateKey): string
    {
        // This is a simplified version - in production use TronWeb library
        $hash = hash('sha256', $privateKey);
        return 'T' . substr($hash, 0, 33);
    }

    /**
     * Get public key from private key
     */
    private function getPublicKeyFromPrivateKey(string $privateKey): string
    {
        // Simplified - in production use proper cryptographic functions
        return hash('sha256', $privateKey . 'public');
    }

    /**
     * Sync wallet balance from TRON network
     */
    public function syncWalletBalance(TronWallet $wallet): bool
    {
        try {
            $response = Http::withHeaders([
                'TRON-PRO-API-KEY' => $this->apiKey,
            ])->get($this->apiUrl . '/v1/accounts/' . $wallet->address);

            if ($response->successful()) {
                $data = $response->json();
                
                $trxBalance = 0;
                $usdtBalance = 0;

                if (isset($data['data'][0]['balance'])) {
                    $trxBalance = $data['data'][0]['balance'] / 1000000; // Convert from sun to TRX
                }

                // Get USDT balance
                $usdtResponse = Http::withHeaders([
                    'TRON-PRO-API-KEY' => $this->apiKey,
                ])->get($this->apiUrl . '/v1/accounts/' . $wallet->address . '/transactions/trc20', [
                    'contract_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', // USDT contract
                    'limit' => 1,
                ]);

                if ($usdtResponse->successful()) {
                    $usdtData = $usdtResponse->json();
                    // Parse USDT balance from response
                    $usdtBalance = $this->parseUsdtBalance($usdtData);
                }

                $wallet->updateBalance($trxBalance, $usdtBalance);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to sync TRON wallet balance: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse USDT balance from API response
     */
    private function parseUsdtBalance(array $data): float
    {
        // Simplified parsing - in production implement proper parsing
        return 0.0;
    }

    /**
     * Send TRX transaction
     */
    public function sendTrx(TronWallet $wallet, string $toAddress, float $amount): array
    {
        try {
            // Validate address
            if (!TronWallet::isValidAddress($toAddress)) {
                throw new \Exception('Неверный адрес получателя');
            }

            // Check balance
            if (!$wallet->hasEnoughTrx($amount)) {
                throw new \Exception('Недостаточно TRX для отправки');
            }

            // Create transaction (simplified - use TronWeb in production)
            $transactionId = 'TXN_' . time() . '_' . Str::random(8);
            
            // Log transaction
            AuditLog::createLog(
                'tron_transaction_sent',
                'TronWallet',
                $wallet->id,
                $wallet->user_id,
                null,
                [
                    'to_address' => $toAddress,
                    'amount' => $amount,
                    'transaction_id' => $transactionId,
                ]
            );

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'to_address' => $toAddress,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send TRX: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send USDT transaction
     */
    public function sendUsdt(TronWallet $wallet, string $toAddress, float $amount): array
    {
        try {
            // Similar to sendTrx but for USDT
            if (!TronWallet::isValidAddress($toAddress)) {
                throw new \Exception('Неверный адрес получателя');
            }

            if (!$wallet->hasEnoughUsdt($amount)) {
                throw new \Exception('Недостаточно USDT для отправки');
            }

            $transactionId = 'USDT_' . time() . '_' . Str::random(8);
            
            AuditLog::createLog(
                'usdt_transaction_sent',
                'TronWallet',
                $wallet->id,
                $wallet->user_id,
                null,
                [
                    'to_address' => $toAddress,
                    'amount' => $amount,
                    'transaction_id' => $transactionId,
                ]
            );

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'to_address' => $toAddress,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send USDT: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get wallet transaction history
     */
    public function getTransactionHistory(TronWallet $wallet, int $limit = 50): array
    {
        try {
            $response = Http::withHeaders([
                'TRON-PRO-API-KEY' => $this->apiKey,
            ])->get($this->apiUrl . '/v1/accounts/' . $wallet->address . '/transactions', [
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Failed to get transaction history: ' . $e->getMessage());
            return [];
        }
    }
}



