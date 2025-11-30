<?php
// |KB Модель брокера: шифрование секрета, резерв и проверки порога

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Broker extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'api_key',
        'api_secret',
        'exchange_url',
        'reserve_balance',
        'min_reserve_threshold',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'reserve_balance' => 'decimal:8',
            'min_reserve_threshold' => 'decimal:8',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * Get encrypted API secret
     */
    public function getApiSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted API secret
     */
    public function setApiSecretAttribute($value)
    {
        $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Check if reserve balance is below threshold
     */
    public function isReserveLow(): bool
    {
        return $this->reserve_balance < $this->min_reserve_threshold;
    }

    /**
     * Add to reserve balance
     */
    public function addToReserve(float $amount): bool
    {
        $this->reserve_balance += $amount;
        return $this->save();
    }

    /**
     * Subtract from reserve balance
     */
    public function subtractFromReserve(float $amount): bool
    {
        if ($this->reserve_balance < $amount) {
            return false;
        }

        $this->reserve_balance -= $amount;
        return $this->save();
    }

    /**
     * Scope for active brokers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Связь с пользователем (User)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
