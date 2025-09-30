<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBalance extends Model
{
    protected $fillable = [
        'user_id',
        'token_id',
        'balance',
        'locked_balance',
        'total_purchased',
        'total_sold',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:8',
            'locked_balance' => 'decimal:8',
            'total_purchased' => 'decimal:8',
            'total_sold' => 'decimal:8',
        ];
    }

    /**
     * Get user that owns this balance
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get token for this balance
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    /**
     * Get available balance (total - locked)
     */
    public function getAvailableBalanceAttribute(): float
    {
        return $this->balance - $this->locked_balance;
    }

    /**
     * Add to balance
     */
    public function addBalance(float $amount): bool
    {
        $this->balance += $amount;
        $this->total_purchased += $amount;
        return $this->save();
    }

    /**
     * Subtract from balance
     */
    public function subtractBalance(float $amount): bool
    {
        if ($this->available_balance < $amount) {
            return false;
        }

        $this->balance -= $amount;
        $this->total_sold += $amount;
        return $this->save();
    }

    /**
     * Lock balance
     */
    public function lockBalance(float $amount): bool
    {
        if ($this->available_balance < $amount) {
            return false;
        }

        $this->locked_balance += $amount;
        return $this->save();
    }

    /**
     * Unlock balance
     */
    public function unlockBalance(float $amount): bool
    {
        if ($this->locked_balance < $amount) {
            return false;
        }

        $this->locked_balance -= $amount;
        return $this->save();
    }

    /**
     * Check if user has enough balance
     */
    public function hasEnoughBalance(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }
}
