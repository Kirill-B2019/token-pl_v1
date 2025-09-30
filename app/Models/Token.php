<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Token extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'current_price',
        'total_supply',
        'available_supply',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'current_price' => 'decimal:8',
            'total_supply' => 'decimal:8',
            'available_supply' => 'decimal:8',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Get transactions for this token
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get user balances for this token
     */
    public function userBalances(): HasMany
    {
        return $this->hasMany(UserBalance::class);
    }

    /**
     * Get winner/loser records for this token
     */
    public function winnerLosers(): HasMany
    {
        return $this->hasMany(WinnerLoser::class);
    }

    /**
     * Check if token has enough supply
     */
    public function hasEnoughSupply(float $amount): bool
    {
        return $this->available_supply >= $amount;
    }

    /**
     * Reduce available supply
     */
    public function reduceSupply(float $amount): bool
    {
        if (!$this->hasEnoughSupply($amount)) {
            return false;
        }

        $this->available_supply -= $amount;
        return $this->save();
    }

    /**
     * Increase available supply
     */
    public function increaseSupply(float $amount): bool
    {
        $this->available_supply += $amount;
        return $this->save();
    }

    /**
     * Scope for active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
