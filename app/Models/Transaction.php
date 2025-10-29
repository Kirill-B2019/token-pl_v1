<?php
// |KB Модель транзакции: генерация ID, статусы, скоупы и связи

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'token_id',
        'type',
        'status',
        'amount',
        'price',
        'total_amount',
        'fee',
        'payment_method',
        'payment_reference',
        'metadata',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'price' => 'decimal:8',
            'total_amount' => 'decimal:8',
            'fee' => 'decimal:8',
            'metadata' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Boot method to generate transaction ID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = static::generateTransactionId();
            }
        });
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId(): string
    {
        do {
            $transactionId = 'TXN_' . Str::upper(Str::random(12));
        } while (static::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    /**
     * Get user that owns this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get token for this transaction
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        $this->processed_at = now();
        return $this->save();
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(): bool
    {
        $this->status = 'failed';
        return $this->save();
    }

    /**
     * Mark transaction as cancelled
     */
    public function markAsCancelled(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope for buy transactions
     */
    public function scopeBuy($query)
    {
        return $query->where('type', 'buy');
    }

    /**
     * Scope for sell transactions
     */
    public function scopeSell($query)
    {
        return $query->where('type', 'sell');
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
