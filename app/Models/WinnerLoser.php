<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WinnerLoser extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'token_amount',
        'token_id',
        'status',
        'processed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'token_amount' => 'decimal:8',
            'metadata' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Get user that owns this record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get token for this record
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed(): bool
    {
        $this->status = 'processed';
        $this->processed_at = now();
        return $this->save();
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(): bool
    {
        $this->status = 'paid';
        return $this->save();
    }

    /**
     * Check if is winner
     */
    public function isWinner(): bool
    {
        return $this->type === 'winner';
    }

    /**
     * Check if is loser
     */
    public function isLoser(): bool
    {
        return $this->type === 'loser';
    }

    /**
     * Check if is processed
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Check if is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Scope for winners
     */
    public function scopeWinners($query)
    {
        return $query->where('type', 'winner');
    }

    /**
     * Scope for losers
     */
    public function scopeLosers($query)
    {
        return $query->where('type', 'loser');
    }

    /**
     * Scope for pending records
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processed records
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
