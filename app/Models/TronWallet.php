<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class TronWallet extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'private_key',
        'public_key',
        'mnemonic',
        'is_active',
        'balance_usdt',
        'balance_trx',
        'last_sync_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'balance_usdt' => 'decimal:6',
            'balance_trx' => 'decimal:6',
            'last_sync_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get encrypted private key
     */
    public function getPrivateKeyAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted private key
     */
    public function setPrivateKeyAttribute($value)
    {
        $this->attributes['private_key'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get encrypted mnemonic
     */
    public function getMnemonicAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted mnemonic
     */
    public function setMnemonicAttribute($value)
    {
        $this->attributes['mnemonic'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get user that owns this wallet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if wallet has sufficient TRX balance for transactions
     */
    public function hasEnoughTrx(float $amount): bool
    {
        return $this->balance_trx >= $amount;
    }

    /**
     * Check if wallet has sufficient USDT balance
     */
    public function hasEnoughUsdt(float $amount): bool
    {
        return $this->balance_usdt >= $amount;
    }

    /**
     * Update wallet balance
     */
    public function updateBalance(float $trxBalance, float $usdtBalance): bool
    {
        $this->balance_trx = $trxBalance;
        $this->balance_usdt = $usdtBalance;
        $this->last_sync_at = now();
        
        return $this->save();
    }

    /**
     * Mark wallet as active
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Mark wallet as inactive
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Get wallet address in base58 format
     */
    public function getAddressAttribute($value): string
    {
        return $value;
    }

    /**
     * Get wallet balance in TRX
     */
    public function getTrxBalanceAttribute(): float
    {
        return (float) $this->balance_trx;
    }

    /**
     * Get wallet balance in USDT
     */
    public function getUsdtBalanceAttribute(): float
    {
        return (float) $this->balance_usdt;
    }

    /**
     * Get total balance in USD equivalent
     */
    public function getTotalBalanceUsdAttribute(): float
    {
        // Assuming 1 USDT = 1 USD and TRX price from metadata
        $trxPrice = $this->metadata['trx_price_usd'] ?? 0.1;
        return $this->balance_usdt + ($this->balance_trx * $trxPrice);
    }

    /**
     * Scope for active wallets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for wallets with balance
     */
    public function scopeWithBalance($query)
    {
        return $query->where(function ($q) {
            $q->where('balance_trx', '>', 0)
              ->orWhere('balance_usdt', '>', 0);
        });
    }

    /**
     * Generate wallet address from private key
     */
    public static function generateAddressFromPrivateKey(string $privateKey): string
    {
        // This would use TronWeb library to generate address
        // For now, return a placeholder
        return 'T' . substr(hash('sha256', $privateKey), 0, 33);
    }

    /**
     * Validate TRON address format
     */
    public static function isValidAddress(string $address): bool
    {
        // TRON addresses start with 'T' and are 34 characters long
        return preg_match('/^T[A-Za-z1-9]{33}$/', $address);
    }

    /**
     * Get wallet QR code data
     */
    public function getQrCodeData(): array
    {
        return [
            'address' => $this->address,
            'type' => 'TRON',
            'label' => 'CardFly Wallet',
            'amount' => null,
        ];
    }
}



